<?php
require_once '../config/db_connect.php';
require_once '../auth/check_session.php';
require_once '../config/metric_type_helpers.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is authenticated
if (!isAuthenticated()) {
    echo json_encode([
        'success' => false, 
        'message' => 'Authentication required'
    ]);
    exit;
}

// Get the current user's agency ID and user ID
$agencyId = $_SESSION['agency_id'];
$userId = $_SESSION['user_id'];

// Default response
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

// Only process POST requests with JSON data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Get JSON data from the request body
$rawInput = file_get_contents('php://input');
$inputData = json_decode($rawInput, true);

// Check if JSON is valid
if (json_last_error() !== JSON_ERROR_NONE) {
    $response['message'] = 'Invalid JSON data: ' . json_last_error_msg();
    echo json_encode($response);
    exit;
}

try {
    // Get database connection
    $conn = getDbConnection();
    
    // Basic validation
    if (empty($inputData['metricId']) || empty($inputData['year']) || empty($inputData['monthlyData'])) {
        throw new Exception('Metric ID, year and monthly data are required');
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Get time_series metric type ID
    $timeSeriesTypeID = getMetricTypeIDFromKey($conn, 'time_series');
    
    if (!$timeSeriesTypeID) {
        throw new Exception("Could not find MetricTypeID for 'time_series'");
    }
    
    // Get metric details to include metric name
    $metricStmt = $conn->prepare("SELECT MetricName, Unit FROM CustomMetrics WHERE MetricID = ? AND AgencyID = ?");
    $metricStmt->execute([$inputData['metricId'], $agencyId]);
    $metric = $metricStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$metric) {
        throw new Exception('Metric not found or you do not have access to it');
    }
    
    // Check if this is a draft
    $isDraft = isset($inputData['isDraft']) && $inputData['isDraft'];
    
    // Enhanced data structure with metadata
    $reportData = [
        'metricId' => $inputData['metricId'],
        'metricName' => $metric['MetricName'],
        'unit' => $metric['Unit'],
        'year' => $inputData['year'],
        'monthlyData' => $inputData['monthlyData'],
        'annualNotes' => $inputData['annualNotes'] ?? '',
        'isDraft' => $isDraft,
        'lastUpdated' => date('Y-m-d H:i:s'),
        'submittedBy' => $_SESSION['username'],
        'userId' => $userId
    ];
    
    // Check if we need to update an existing report or create a new one
    $stmt = $conn->prepare("
        SELECT MetricID 
        FROM Metrics 
        WHERE AgencyID = ? 
        AND MetricTypeID = ? 
        AND Year = ? 
        AND JSON_EXTRACT(Data, '$.metricId') = ?
    ");
    
    $stmt->execute([$agencyId, $timeSeriesTypeID, $inputData['year'], $inputData['metricId']]);
    $existingReport = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingReport) {
        // Update existing record
        $updateStmt = $conn->prepare("UPDATE Metrics SET Data = ? WHERE MetricID = ?");
        $updateStmt->execute([json_encode($reportData), $existingReport['MetricID']]);
        $reportId = $existingReport['MetricID'];
    } else {
        // Insert a new record
        $insertStmt = $conn->prepare("
            INSERT INTO Metrics (AgencyID, MetricTypeID, Data, Year, Quarter) 
            VALUES (?, ?, ?, ?, 'All')
        ");
        $insertStmt->execute([
            $agencyId, 
            $timeSeriesTypeID, 
            json_encode($reportData),
            $inputData['year']
        ]);
        $reportId = $conn->lastInsertId();
    }
    
    // Log the action
    $actionType = $isDraft ? 'save_time_series_draft' : 'submit_time_series_data';
    $logStmt = $conn->prepare('
        INSERT INTO logs (user_id, action, entity_type, entity_id, details, ip_address)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    
    $logStmt->execute([
        $userId,
        $actionType,
        'time_series_data',
        $reportId,
        "Time series data for {$metric['MetricName']} - {$inputData['year']}",
        $_SERVER['REMOTE_ADDR']
    ]);
    
    // Commit transaction
    $conn->commit();
    
    $response = [
        'success' => true,
        'message' => $isDraft ? 'Draft saved successfully' : 'Data submitted successfully',
        'reportId' => $reportId
    ];
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error in save_time_series_data.php: ' . $e->getMessage());
}

// Return JSON response
echo json_encode($response);

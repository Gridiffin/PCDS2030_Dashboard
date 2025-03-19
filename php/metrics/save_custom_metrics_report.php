<?php
require_once '../config/db_connect.php';
require_once '../auth/check_session.php';
require_once '../config/metric_type_helpers.php';  // Add helper functions

// Set content type to JSON
header('Content-Type: application/json');

// Debug logging
error_log("save_custom_metrics_report.php called");

// Check if user is authenticated
if (!isAuthenticated()) {
    error_log("Authentication failed");
    echo json_encode([
        'success' => false, 
        'message' => 'Authentication required'
    ]);
    exit;
}

// Get the current user's agency ID
$agencyId = $_SESSION['agency_id'];
$userId = $_SESSION['user_id'];

// Default response
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Get JSON data from the request body
$rawInput = file_get_contents('php://input');
error_log("Raw input: " . $rawInput);  // Debug log

$inputData = json_decode($rawInput, true);

// Check if JSON is valid
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("JSON error: " . json_last_error_msg());
    $response['message'] = 'Invalid JSON data: ' . json_last_error_msg();
    echo json_encode($response);
    exit;
}

error_log("Input data parsed successfully");  // Debug log

try {
    // Get database connection
    $conn = getDbConnection();
    
    // Basic validation
    if (empty($inputData['year']) || empty($inputData['quarter']) || empty($inputData['reportDate']) || empty($inputData['metricId'])) {
        throw new Exception('Year, quarter, report date, and metric ID are required');
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Get metric details
    $metricStmt = $conn->prepare("SELECT * FROM CustomMetrics WHERE MetricID = ? AND AgencyID = ?");
    $metricStmt->execute([$inputData['metricId'], $agencyId]);
    $metric = $metricStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$metric) {
        throw new Exception('Metric not found or you do not have access to it');
    }
    
    // Check if this is a draft
    $isDraft = isset($inputData['isDraft']) && $inputData['isDraft'];
    
    // Prepare the metric data for storage
    $metricData = [
        'year' => $inputData['year'],
        'quarter' => $inputData['quarter'],
        'reportDate' => $inputData['reportDate'],
        'notes' => $inputData['notes'] ?? '',
        'metricsData' => $inputData['metricsData'] ?? [],
        'isDraft' => $isDraft,
        'lastUpdated' => date('Y-m-d H:i:s'),
        'submittedBy' => $_SESSION['username'],
        'userId' => $userId,
        'metricId' => $inputData['metricId'],
        'metricName' => $metric['MetricName']
    ];
    
    // Check if this is an update to an existing report
    $isUpdate = isset($inputData['reportId']) && !empty($inputData['reportId']);
    $reportId = $isUpdate ? $inputData['reportId'] : null;
    
    // Get MetricTypeID for 'single_custom_metric'
    $metricTypeID = getMetricTypeIDFromKey($conn, 'single_custom_metric');
    
    if (!$metricTypeID) {
        throw new Exception("Could not find MetricTypeID for 'single_custom_metric'");
    }

    if ($isUpdate) {
        // Verify the report exists and belongs to the user's agency
        $checkStmt = $conn->prepare("
            SELECT MetricID 
            FROM Metrics 
            WHERE MetricID = ? 
            AND AgencyID = ? 
            AND MetricType = 'single_custom_metric'
        ");
        $checkStmt->execute([$reportId, $agencyId]);
        
        if ($checkStmt->rowCount() === 0) {
            throw new Exception('Report not found or you do not have permission to update it');
        }
        
        // Update the existing report
        $stmt = $conn->prepare("
            UPDATE Metrics 
            SET Data = ?, Quarter = ?, Year = ? 
            WHERE MetricID = ?
        ");
        
        $stmt->execute([
            json_encode($metricData),
            $inputData['quarter'],
            $inputData['year'],
            $reportId
        ]);
    } else {
        // Insert a new report using the MetricTypeID instead of string
        $stmt = $conn->prepare('
            INSERT INTO Metrics (MetricTypeID, Data, Quarter, Year, AgencyID) 
            VALUES (?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $metricTypeID,
            json_encode($metricData),
            $inputData['quarter'],
            $inputData['year'],
            $agencyId
        ]);
        
        $reportId = $conn->lastInsertId();
    }
    
    // Log the action
    $actionType = $isDraft ? ($isUpdate ? 'update_single_metric_draft' : 'save_single_metric_draft') : 'submit_single_metric_report';
    $stmt = $conn->prepare('
        INSERT INTO logs (user_id, action, entity_type, entity_id, details, ip_address)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    
    $stmt->execute([
        $userId,
        $actionType,
        'single_metric_report',
        $reportId,
        "Single metric report for {$metric['MetricName']} - {$inputData['quarter']} {$inputData['year']}",
        $_SERVER['REMOTE_ADDR']
    ]);
    
    // Commit transaction
    $conn->commit();
    
    $response = [
        'success' => true,
        'message' => $isDraft ? ($isUpdate ? 'Draft updated successfully' : 'Draft saved successfully') : 'Report submitted successfully',
        'reportId' => $reportId,
        'metricName' => $metric['MetricName']
    ];
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Error: " . $e->getMessage());
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Return JSON response
echo json_encode($response);

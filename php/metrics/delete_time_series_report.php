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

// Check if report ID is provided
if (!isset($inputData['id']) || empty($inputData['id'])) {
    $response['message'] = 'Report ID is required';
    echo json_encode($response);
    exit;
}

$reportId = $inputData['id'];

try {
    // Get database connection
    $conn = getDbConnection();
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Get time_series metric type ID
    $timeSeriesTypeID = getMetricTypeIDFromKey($conn, 'time_series');
    
    if (!$timeSeriesTypeID) {
        throw new Exception("Could not find MetricTypeID for 'time_series'");
    }
    
    // Check if the report belongs to this agency
    $stmt = $conn->prepare("
        SELECT Data 
        FROM Metrics 
        WHERE MetricID = ? AND AgencyID = ? AND MetricTypeID = ?
    ");
    
    $stmt->execute([$reportId, $agencyId, $timeSeriesTypeID]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$report) {
        throw new Exception('Report not found or you do not have permission to delete it');
    }
    
    // Get report data to log what's being deleted
    $reportData = json_decode($report['Data'], true);
    $isDraft = $reportData['isDraft'] ?? false;
    $metricName = $reportData['metricName'] ?? 'Unknown Metric';
    $year = $reportData['year'] ?? 'Unknown Year';
    
    // Delete the report
    $deleteStmt = $conn->prepare("DELETE FROM Metrics WHERE MetricID = ?");
    $deleteStmt->execute([$reportId]);
    
    // Log the deletion
    $logStmt = $conn->prepare('
        INSERT INTO logs (user_id, action, entity_type, entity_id, details, ip_address)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    
    $logStmt->execute([
        $userId,
        $isDraft ? 'delete_time_series_draft' : 'delete_time_series_report',
        'time_series_report',
        $reportId,
        "Deleted {$metricName} - {$year}" . ($isDraft ? " (Draft)" : ""),
        $_SERVER['REMOTE_ADDR']
    ]);
    
    // Commit transaction
    $conn->commit();
    
    $response = [
        'success' => true,
        'message' => ($isDraft ? 'Draft' : 'Report') . ' deleted successfully',
        'wasDraft' => $isDraft
    ];
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error in delete_time_series_report.php: ' . $e->getMessage());
}

// Return JSON response
echo json_encode($response);

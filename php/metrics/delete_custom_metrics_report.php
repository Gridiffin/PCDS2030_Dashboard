<?php
require_once '../config/db_connect.php';
require_once '../auth/check_session.php';

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
    
    // Verify this is a custom metrics report and belongs to the user's agency
    $stmt = $conn->prepare("
        SELECT MetricID, Data, MetricType 
        FROM Metrics 
        WHERE MetricID = ? AND AgencyID = ? AND MetricType = 'custom_metrics_report'
    ");
    
    $stmt->execute([$reportId, $agencyId]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$report) {
        throw new Exception('Report not found or you do not have permission to delete it');
    }
    
    // Extract report data to log details of what was deleted
    $reportData = json_decode($report['Data'], true);
    $isDraft = $reportData['isDraft'] ?? false;
    $reportYear = $reportData['year'] ?? 'Unknown';
    $reportQuarter = $reportData['quarter'] ?? 'Unknown';
    
    // Delete the report
    $stmt = $conn->prepare("DELETE FROM Metrics WHERE MetricID = ?");
    $stmt->execute([$reportId]);
    
    // Log the deletion
    $actionType = $isDraft ? 'delete_metrics_draft' : 'delete_metrics_report';
    $stmt = $conn->prepare('
        INSERT INTO logs (user_id, action, entity_type, entity_id, details, ip_address)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    
    $stmt->execute([
        $userId,
        $actionType,
        'metrics_report',
        $reportId,
        "Deleted custom metrics " . ($isDraft ? "draft" : "report") . " for {$reportQuarter} {$reportYear}",
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
    error_log('Error deleting custom metrics report: ' . $e->getMessage());
}

// Return JSON response
echo json_encode($response);

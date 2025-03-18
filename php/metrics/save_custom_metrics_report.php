<?php
require_once '../config/db_connect.php';
require_once '../auth/check_session.php';

// Set content type to JSON
header('Content-Type: application/json');

// Debug logging - write to error log for debugging
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
    if (empty($inputData['year']) || empty($inputData['quarter']) || empty($inputData['reportDate'])) {
        throw new Exception('Year, quarter, and report date are required');
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Check if this is a draft
    $isDraft = isset($inputData['isDraft']) && $inputData['isDraft'];
    error_log("Is draft: " . ($isDraft ? "yes" : "no"));  // Debug log
    
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
        'userId' => $userId
    ];
    
    // Check if this is an update to an existing report
    $isUpdate = isset($inputData['reportId']) && !empty($inputData['reportId']);
    $reportId = $isUpdate ? $inputData['reportId'] : null;
    
    if ($isUpdate) {
        error_log("Updating existing report: " . $reportId);  // Debug log
        
        // Verify the report exists and belongs to the user's agency
        $checkStmt = $conn->prepare("
            SELECT MetricID 
            FROM Metrics 
            WHERE MetricID = ? 
            AND AgencyID = ? 
            AND MetricType = 'custom_metrics_report'
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
        
        error_log("Report updated successfully");  // Debug log
    } else {
        error_log("Creating new report");  // Debug log
        
        // Insert into the Metrics table with a special metric type for custom metrics reports
        $stmt = $conn->prepare('
            INSERT INTO Metrics (MetricType, Data, Quarter, Year, AgencyID) 
            VALUES (?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            'custom_metrics_report', // Special metric type to distinguish from regular metrics
            json_encode($metricData),
            $inputData['quarter'],
            $inputData['year'],
            $agencyId
        ]);
        
        $reportId = $conn->lastInsertId();
        error_log("New report created with ID: " . $reportId);  // Debug log
    }
    
    // Log the action
    $actionType = $isDraft ? ($isUpdate ? 'update_metrics_draft' : 'save_metrics_draft') : 'submit_metrics_report';
    $stmt = $conn->prepare('
        INSERT INTO logs (user_id, action, entity_type, entity_id, details, ip_address)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    
    $stmt->execute([
        $userId,
        $actionType,
        'metrics_report',
        $reportId,
        "Custom metrics report for {$inputData['quarter']} {$inputData['year']}",
        $_SERVER['REMOTE_ADDR']
    ]);
    
    // Commit transaction
    $conn->commit();
    
    $response = [
        'success' => true,
        'message' => $isDraft ? ($isUpdate ? 'Draft updated successfully' : 'Draft saved successfully') : 'Report submitted successfully',
        'reportId' => $reportId
    ];
    
    error_log("Response: " . json_encode($response));  // Debug log
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Error: " . $e->getMessage());  // Debug log
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Return JSON response
echo json_encode($response);

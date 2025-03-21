<?php
// Turn off error display - errors will be logged but not shown to client
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start output buffering to prevent any unwanted output
ob_start();

// Start session to check authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

// Include necessary files
require_once '../config/db_connect.php';
require_once '../auth/check_session.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is authenticated
if (!isAuthenticated()) {
    // Clear any buffered output
    ob_end_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Authentication required'
    ]);
    exit;
}

// Default response
$response = [
    'success' => false,
    'message' => 'An error occurred',
    'programId' => null
];

try {
    // Get JSON data from request
    $jsonData = file_get_contents('php://input');
    $inputData = json_decode($jsonData, true);
    
    if (!$inputData) {
        throw new Exception('Invalid JSON data');
    }
    
    // Get database connection
    $conn = getDbConnection();
    
    // Get the user's agency ID from session
    $agencyId = $_SESSION['agency_id'] ?? null;
    
    if (!$agencyId) {
        throw new Exception('User agency not found');
    }
    
    // Process program ID - if it starts with 'new_', we need to generate a proper ID
    $programId = $inputData['programId'] ?? null;
    
    // Simplified data structure for metrics table JSON
    $metricData = [
        'programName' => $inputData['programName'] ?? '',
        'targetText' => $inputData['targetText'] ?? '',
        'statusText' => $inputData['statusText'] ?? '',
        'statusDate' => $inputData['statusDate'] ?? date('Y-m-d'),
        'statusColor' => $inputData['statusColor'] ?? 'not-started',
        'isDraft' => $inputData['isDraft'] ?? false,
        'lastUpdated' => date('Y-m-d H:i:s'),
        'submittedBy' => $_SESSION['username'] ?? 'anonymous',
        'userId' => $_SESSION['user_id'] ?? 0
    ];
    
    // Convert to JSON string
    $jsonMetricData = json_encode($metricData);
    
    // Check the actual column name in the database
    // Check if MetricTypeID exists instead of MetricType
    $checkColumnsQuery = "SHOW COLUMNS FROM metrics";
    $columnsStmt = $conn->query($checkColumnsQuery);
    $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Log the columns for debugging
    error_log("Metrics table columns: " . implode(", ", $columns));
    
    // Determine if we should use MetricTypeID or another column
    $metricTypeColumn = in_array('MetricTypeID', $columns) ? 'MetricTypeID' : 
                        (in_array('MetricType', $columns) ? 'MetricType' : null);
    
    if (!$metricTypeColumn) {
        throw new Exception('Could not determine metric type column in database');
    }
    
    // Check if this is an update or a new entry
    if (preg_match('/^new_/', $programId)) {
        // This is a new entry - Use dynamic column name
        $stmt = $conn->prepare("
            INSERT INTO metrics 
                ($metricTypeColumn, Data, Quarter, Year, AgencyID) 
            VALUES 
                (?, ?, ?, ?, ?)
        ");
        
        // Use a default value for MetricTypeID - adjust based on your schema
        $metricTypeValue = $metricTypeColumn === 'MetricTypeID' ? 3 : 'governance';
        
        $stmt->execute([
            $metricTypeValue,
            $jsonMetricData,
            $inputData['quarter'],
            $inputData['year'],
            $agencyId
        ]);
        
        $newId = $conn->lastInsertId();
        $response['programId'] = $newId;
        $actionType = $inputData['isDraft'] ? 'draft_metric' : 'submit_metric';
    } else {
        // This is an update to an existing entry
        $stmt = $conn->prepare("
            UPDATE metrics 
            SET 
                Data = ?, 
                Quarter = ?, 
                Year = ? 
            WHERE 
                MetricID = ? AND AgencyID = ?
        ");
        
        $stmt->execute([
            $jsonMetricData,
            $inputData['quarter'],
            $inputData['year'],
            $programId,
            $agencyId
        ]);
        
        $response['programId'] = $programId;
        $actionType = $inputData['isDraft'] ? 'update_draft_metric' : 'update_metric';
    }
    
    // Log the action
    $details = "{$actionType} - {$inputData['programName']} ({$inputData['quarter']} {$inputData['year']})";
    $logStmt = $conn->prepare("
        INSERT INTO logs 
            (user_id, action, entity_type, entity_id, details, ip_address) 
        VALUES 
            (?, ?, ?, ?, ?, ?)
    ");
    
    $logStmt->execute([
        $_SESSION['user_id'] ?? null,
        $actionType,
        'metric',
        $response['programId'],
        $details,
        $_SERVER['REMOTE_ADDR']
    ]);
    
    $response['success'] = true;
    $response['message'] = $inputData['isDraft'] ? 'Draft saved successfully' : 'Target status submitted successfully';
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error in save_target_status.php: ' . $e->getMessage());
}

// Clear any buffered output before sending the JSON response
ob_end_clean();
echo json_encode($response);
exit;
?>

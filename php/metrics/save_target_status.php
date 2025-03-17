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

// Include database connection and authentication
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
    'data' => null
];

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

try {
    // For FormData submission, we need to handle differently
    if (isset($_POST['data'])) {
        // Get the JSON data from the FormData
        $inputData = json_decode($_POST['data'], true);
    } else {
        // Handle direct JSON POST
        $inputData = json_decode(file_get_contents('php://input'), true);
    }
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }
    
    // No need to verify required fields for drafts
    if (!isset($inputData['isDraft']) || !$inputData['isDraft']) {
        // Add validation as needed
    }
    
    // Get database connection
    $conn = getDbConnection();
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Get user's AgencyID from session
    $agencyId = $_SESSION['agency_id'];
    
    // Process program data
    $programId = null;
    if (isset($inputData['programId']) && !empty($inputData['programId']) && $inputData['programId'] !== 'new') {
        // Use existing program
        $programId = $inputData['programId'];
    } else if (isset($inputData['programName']) && !empty($inputData['programName'])) {
        // Create new program entry
        $programId = uniqid('prog_');
    }
    
    // Prepare JSON data for metrics table
    $metricData = [
        'programId' => $programId,
        'programName' => $inputData['programName'] ?? '',
        'programDescription' => $inputData['programDescription'] ?? '',
        'target' => [
            'indicator' => $inputData['indicator'] ?? '',
            'value' => $inputData['targetValue'] ?? '',
            'unit' => $inputData['targetUnit'] ?? '',
            'deadline' => $inputData['targetDeadline'] ?? null,
            'description' => $inputData['targetDescription'] ?? ''
        ],
        'status' => [
            'currentValue' => $inputData['currentValue'] ?? '',
            'date' => $inputData['statusDate'] ?? date('Y-m-d'),
            'completionPercentage' => $inputData['completionPercentage'] ?? 0,
            'notes' => $inputData['statusNotes'] ?? '',
            'challenges' => $inputData['challenges'] ?? '',
            'color' => $inputData['statusColor'] ?? null
        ],
        'lastUpdated' => date('Y-m-d H:i:s'),
        'status' => $inputData['status'] ?? 'draft',
        'submittedBy' => $_SESSION['username'],
        'userId' => $_SESSION['user_id']
    ];
    
    // Insert metric data into database
    $stmt = $conn->prepare('
        INSERT INTO Metrics (MetricType, Data, Quarter, Year, AgencyID) 
        VALUES (?, ?, ?, ?, ?)
    ');
    
    $stmt->execute([
        $inputData['metricType'],
        json_encode($metricData),
        $inputData['quarter'],
        $inputData['year'],
        $agencyId
    ]);
    
    $metricId = $conn->lastInsertId();
    
    // Handle file uploads if present
    if (isset($_FILES['supportingFiles']) && !empty($_FILES['supportingFiles']['name'][0])) {
        $uploadDir = '../../uploads/metrics/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileCount = count($_FILES['supportingFiles']['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            $fileName = $_FILES['supportingFiles']['name'][$i];
            $fileTmpName = $_FILES['supportingFiles']['tmp_name'][$i];
            
            // Generate unique filename
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
            $uniqueName = uniqid('file_') . '.' . $fileExt;
            $targetFilePath = $uploadDir . $uniqueName;
            
            // Upload file
            if (move_uploaded_file($fileTmpName, $targetFilePath)) {
                // Success
            }
        }
    }
    
    // Log the action
    $actionType = $inputData['status'] === 'draft' ? 'draft_metric' : 'submit_metric';
    $stmt = $conn->prepare('
        INSERT INTO logs (user_id, action, entity_type, entity_id, details, ip_address)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    
    $stmt->execute([
        $_SESSION['user_id'],
        $actionType,
        'metric',
        $metricId,
        "Metric data for {$inputData['metricType']} - {$inputData['indicator']} (Q{$inputData['quarter']} {$inputData['year']})",
        $_SERVER['REMOTE_ADDR']
    ]);
    
    // Commit transaction
    $conn->commit();
    
    $response = [
        'success' => true,
        'message' => $inputData['status'] === 'draft' ? 'Draft saved successfully' : 'Data submitted successfully',
        'metricId' => $metricId
    ];
    
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

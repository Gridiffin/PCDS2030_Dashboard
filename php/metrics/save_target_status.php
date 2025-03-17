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
    // Get the JSON data directly from POST body
    $inputData = json_decode(file_get_contents('php://input'), true);
    
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
    
    // Check if this is an update to an existing draft
    $isDraftUpdate = isset($inputData['isDraft']) && $inputData['isDraft'] && isset($inputData['draftId']);
    $metricId = null;
    
    // Store whether this is a submission of a draft (converting from draft to submission)
    $isSubmittingDraft = $inputData['status'] !== 'draft' && isset($inputData['draftId']);
    $programName = $inputData['programName'];
    
    if ($isDraftUpdate) {
        // Update existing draft
        $stmt = $conn->prepare('
            UPDATE Metrics 
            SET Data = ?, Quarter = ?, Year = ? 
            WHERE MetricID = ? AND AgencyID = ?
        ');
        
        $stmt->execute([
            json_encode($metricData),
            $inputData['quarter'],
            $inputData['year'],
            $inputData['draftId'],
            $agencyId
        ]);
        
        // Set the metric ID for the response
        $metricId = $inputData['draftId'];
        
        // Check if update was successful
        if ($stmt->rowCount() === 0) {
            throw new Exception('Failed to update draft - draft not found or you don\'t have permission to update it');
        }
    } else {
        // Insert new record
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
    }
    
    // If this was a draft being submitted (status changed from draft to submitted),
    // delete any other drafts with the same program name
    if ($isSubmittingDraft || ($inputData['status'] !== 'draft' && isset($programName))) {
        // Find and delete other drafts with the same program name
        $deleteDraftsStmt = $conn->prepare("
            DELETE FROM Metrics 
            WHERE AgencyID = ? 
            AND JSON_EXTRACT(Data, '$.programName') = ? 
            AND JSON_EXTRACT(Data, '$.status') = 'draft'
            AND MetricID != ?
        ");
        $deleteDraftsStmt->execute([
            $agencyId,
            $programName,
            $metricId
        ]);
        
        $deletedDraftsCount = $deleteDraftsStmt->rowCount();
        if ($deletedDraftsCount > 0) {
            $response['deletedDraftsCount'] = $deletedDraftsCount;
            $response['message'] .= " ($deletedDraftsCount related draft(s) removed)";
        }
    }
    
    // Log the action
    $actionType = $inputData['status'] === 'draft' ? 
        ($isDraftUpdate ? 'update_draft' : 'draft_metric') : 
        'submit_metric';
        
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
        'message' => $inputData['status'] === 'draft' ? 
            ($isDraftUpdate ? 'Draft updated successfully' : 'Draft saved successfully') : 
            'Data submitted successfully',
        'metricId' => $metricId,
        'programName' => $programName
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

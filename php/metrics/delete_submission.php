<?php
// Turn off error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start output buffering
ob_start();

// Start session to check authentication
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Include database connection and authentication
require_once '../config/db_connect.php';
require_once '../auth/check_session.php';

// Set content type to JSON
header('Content-Type: application/json');

// Default response
$response = [
    'success' => false,
    'message' => 'An error occurred'
];

try {
    // Check if user is authenticated
    if (!isAuthenticated()) {
        throw new Exception('Authentication required');
    }
    
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Get the submission ID from POST data
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['id']) || empty($input['id'])) {
        throw new Exception('Submission ID is required');
    }
    
    $submissionId = $input['id'];
    $userAgencyId = $_SESSION['agency_id'];
    
    // Get database connection
    $conn = getDbConnection();
    
    // Begin transaction
    $conn->beginTransaction();
    
    // First check if the submission belongs to the user's agency
    $stmt = $conn->prepare("
        SELECT AgencyID 
        FROM Metrics 
        WHERE MetricID = ?
    ");
    
    $stmt->execute([$submissionId]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$submission) {
        throw new Exception('Submission not found');
    }
    
    // Check if user has permission to delete this submission
    if ($submission['AgencyID'] != $userAgencyId) {
        throw new Exception('You do not have permission to delete this submission');
    }
    
    // Remove reference to supporting_files table
    
    // Now delete the submission
    $stmt = $conn->prepare("
        DELETE FROM Metrics 
        WHERE MetricID = ?
    ");
    
    $stmt->execute([$submissionId]);
    
    // Log the deletion
    $stmt = $conn->prepare('
        INSERT INTO logs (user_id, action, entity_type, entity_id, details, ip_address)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    
    $stmt->execute([
        $_SESSION['user_id'],
        'delete_metric',
        'metric',
        $submissionId,
        "Deleted submission ID: {$submissionId}",
        $_SERVER['REMOTE_ADDR']
    ]);
    
    // Commit changes
    $conn->commit();
    
    $response = [
        'success' => true,
        'message' => 'Submission deleted successfully'
    ];
    
} catch (Exception $e) {
    // Roll back transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error in delete_submission.php: ' . $e->getMessage());
}

// Clear any buffered output before sending JSON
ob_end_clean();
echo json_encode($response);
exit;
?>

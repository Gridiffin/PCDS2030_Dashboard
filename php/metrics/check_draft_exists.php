<?php
// Turn off error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start output buffering to prevent any unwanted output
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
    'exists' => false,
    'success' => false
];

try {
    // Check if user is authenticated
    if (!isAuthenticated()) {
        throw new Exception('Authentication required');
    }
    
    // Check if ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('Draft ID is required');
    }
    
    $draftId = $_GET['id'];
    
    // Get database connection
    $conn = getDbConnection();
    
    // Check if the draft exists and belongs to the user's agency
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM Metrics 
        WHERE MetricID = ? 
        AND AgencyID = ?
        AND JSON_EXTRACT(Data, '$.status') = 'draft'
    ");
    
    $stmt->execute([$draftId, $_SESSION['agency_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $response = [
        'exists' => ($result && $result['count'] > 0),
        'success' => true
    ];
    
} catch (Exception $e) {
    error_log('Error in check_draft_exists.php: ' . $e->getMessage());
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Clear any buffered output before sending JSON
ob_end_clean();
echo json_encode($response);
exit;
?>

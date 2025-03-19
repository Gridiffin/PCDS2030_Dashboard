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

// Default response
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

// Check if report ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $response['message'] = 'Report ID is required';
    echo json_encode($response);
    exit;
}

$reportId = $_GET['id'];

try {
    // Get database connection
    $conn = getDbConnection();
    
    // Get the report data
    $stmt = $conn->prepare("
        SELECT MetricID, Data, Year
        FROM Metrics 
        WHERE MetricID = ? AND AgencyID = ?
    ");
    
    $stmt->execute([$reportId, $agencyId]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$report) {
        throw new Exception('Report not found or you do not have permission to access it');
    }
    
    // Decode the JSON data
    $data = json_decode($report['Data'], true);
    
    // Return the report data
    $response = [
        'success' => true,
        'data' => $data
    ];
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error in get_time_series_report.php: ' . $e->getMessage());
}

// Return JSON response
echo json_encode($response);

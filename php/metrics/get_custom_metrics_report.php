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
    
    // First, just get the basic report data without timestamp
    $stmt = $conn->prepare("
        SELECT MetricID, Data, Quarter, Year
        FROM Metrics 
        WHERE MetricID = ? AND AgencyID = ? AND MetricType = 'custom_metrics_report'
    ");
    
    $stmt->execute([$reportId, $agencyId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$row) {
        throw new Exception('Report not found or you do not have permission to access it');
    }
    
    $data = json_decode($row['Data'], true);
    
    // Format the report details - use the timestamp from the JSON data itself
    $report = [
        'id' => $row['MetricID'],
        'year' => $row['Year'],
        'quarter' => $row['Quarter'],
        'reportDate' => $data['reportDate'] ?? null,
        'notes' => $data['notes'] ?? '',
        'metricsData' => $data['metricsData'] ?? [],
        'isDraft' => $data['isDraft'] ?? false,
        'lastUpdated' => $data['lastUpdated'] ?? date('Y-m-d H:i:s'),
        'submittedBy' => $data['submittedBy'] ?? ''
    ];
    
    $response = [
        'success' => true,
        'data' => $report
    ];
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error fetching custom metrics report: ' . $e->getMessage());
}

// Return JSON response
echo json_encode($response);

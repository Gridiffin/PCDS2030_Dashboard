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

// Check if report ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Report ID is required'
    ]);
    exit;
}

$reportId = $_GET['id'];

// Default response
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    // Get database connection
    $conn = getDbConnection();
    
    // Fetch the report
    $stmt = $conn->prepare("
        SELECT MetricID, Data, Quarter, Year 
        FROM Metrics 
        WHERE MetricID = ? AND AgencyID = ? AND MetricType = 'single_custom_metric'
    ");
    $stmt->execute([$reportId, $agencyId]);
    
    $report = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$report) {
        throw new Exception('Report not found or you do not have permission to access it');
    }
    
    // Parse the JSON data
    $data = json_decode($report['Data'], true);
    
    // Format the response
    $response = [
        'success' => true,
        'data' => [
            'id' => $report['MetricID'],
            'year' => $report['Year'],
            'quarter' => $report['Quarter'],
            'reportDate' => $data['reportDate'] ?? null,
            'notes' => $data['notes'] ?? '',
            'metricId' => $data['metricId'] ?? null,
            'metricName' => $data['metricName'] ?? 'Unknown Metric',
            'metricsData' => $data['metricsData'] ?? [],
            'isDraft' => $data['isDraft'] ?? false,
            'lastUpdated' => $data['lastUpdated'] ?? date('Y-m-d H:i:s')
        ]
    ];
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error fetching metric report: ' . $e->getMessage());
}

// Return JSON response
echo json_encode($response);

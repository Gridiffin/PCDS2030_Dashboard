<?php
require_once '../config/db_connect.php';
require_once '../auth/check_session.php';
require_once '../config/metric_type_helpers.php';

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

try {
    // Check required parameters
    if (!isset($_GET['metricId']) || !isset($_GET['year'])) {
        throw new Exception('Metric ID and year are required');
    }
    
    $metricId = $_GET['metricId'];
    $year = $_GET['year'];
    
    // Get database connection
    $conn = getDbConnection();
    
    // Get time_series metric type ID
    $timeSeriesTypeID = getMetricTypeIDFromKey($conn, 'time_series');
    
    if (!$timeSeriesTypeID) {
        throw new Exception("Could not find MetricTypeID for 'time_series'");
    }
    
    // Check if time series data exists for this metric and year
    $stmt = $conn->prepare("
        SELECT MetricID, Data 
        FROM Metrics 
        WHERE AgencyID = ? 
        AND MetricTypeID = ? 
        AND Year = ? 
        AND JSON_EXTRACT(Data, '$.metricId') = ?
    ");
    
    $stmt->execute([$agencyId, $timeSeriesTypeID, $year, $metricId]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($report) {
        // Data exists, return it
        $data = json_decode($report['Data'], true);
        
        $response = [
            'success' => true,
            'data' => $data
        ];
    } else {
        // No data exists yet, return success with null data
        $response = [
            'success' => true,
            'data' => null
        ];
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error in get_time_series_data.php: ' . $e->getMessage());
}

// Return JSON response
echo json_encode($response);

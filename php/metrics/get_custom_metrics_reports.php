<?php
require_once '../config/db_connect.php';
require_once '../auth/check_session.php';
require_once '../config/metric_type_helpers.php'; // Add the helpers

// Set content type to JSON
header('Content-Type: application/json');

// Debug logging
error_log("get_custom_metrics_reports.php called");

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
error_log("Agency ID: " . $agencyId);

// Check if specific metric ID is provided
$metricId = isset($_GET['metricId']) ? $_GET['metricId'] : null;

// Default response
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    // Get database connection
    $conn = getDbConnection();
    
    // Get the MetricTypeID for 'single_custom_metric'
    $singleMetricTypeID = getMetricTypeIDFromKey($conn, 'single_custom_metric');
    
    if (!$singleMetricTypeID) {
        throw new Exception("Could not find MetricTypeID for 'single_custom_metric'");
    }
    
    // Build query based on whether we have a metric ID filter
    if ($metricId) {
        // Get reports for specific metric only
        $stmt = $conn->prepare("
            SELECT MetricID as id, Data, Quarter, Year
            FROM Metrics 
            WHERE AgencyID = ? 
            AND MetricTypeID = ?
            AND JSON_EXTRACT(Data, '$.metricId') = ?
            ORDER BY Year DESC, Quarter DESC
        ");
        $stmt->execute([$agencyId, $singleMetricTypeID, $metricId]);
    } else {
        // Get all single metric reports
        $stmt = $conn->prepare("
            SELECT MetricID as id, Data, Quarter, Year
            FROM Metrics 
            WHERE AgencyID = ? 
            AND MetricTypeID = ?
            ORDER BY Year DESC, Quarter DESC
        ");
        $stmt->execute([$agencyId, $singleMetricTypeID]);
    }
    
    $reports = [];
    $count = 0;
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data = json_decode($row['Data'], true);
        $count++;
        
        // Get metric name from the data
        $metricName = $data['metricName'] ?? 'Unknown Metric';
        
        // Format report for display
        $report = [
            'id' => $row['id'],
            'year' => $row['Year'],
            'quarter' => $row['Quarter'],
            'metricId' => $data['metricId'] ?? null,
            'metricName' => $metricName,
            'reportDate' => $data['reportDate'] ?? null,
            'isDraft' => $data['isDraft'] ?? false,
            'lastUpdated' => $data['lastUpdated'] ?? date('Y-m-d H:i:s')
        ];
        
        $reports[] = $report;
    }
    
    error_log("Found {$count} reports");
    
    $response = [
        'success' => true,
        'data' => $reports
    ];
    
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Return JSON response
echo json_encode($response);

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
    // Get database connection
    $conn = getDbConnection();
    
    // Get time_series metric type ID
    $timeSeriesTypeID = getMetricTypeIDFromKey($conn, 'time_series');
    
    if (!$timeSeriesTypeID) {
        throw new Exception("Could not find MetricTypeID for 'time_series'");
    }
    
    // Check if specific metric ID is provided
    $metricId = isset($_GET['metricId']) ? $_GET['metricId'] : null;
    $year = isset($_GET['year']) ? $_GET['year'] : null;
    
    // Build the base query
    $sql = "
        SELECT MetricID as id, Data, Year
        FROM Metrics 
        WHERE AgencyID = ? 
        AND MetricTypeID = ?
    ";
    
    $params = [$agencyId, $timeSeriesTypeID];
    
    // Add filters if specified
    if ($metricId) {
        $sql .= " AND JSON_EXTRACT(Data, '$.metricId') = ?";
        $params[] = $metricId;
    }
    
    if ($year) {
        $sql .= " AND Year = ?";
        $params[] = $year;
    }
    
    // Add sorting
    $sql .= " ORDER BY Year DESC, MetricID DESC";
    
    // Execute the query
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    $reports = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data = json_decode($row['Data'], true);
        
        // Format report for display
        $report = [
            'id' => $row['id'],
            'year' => $row['Year'],
            'metricId' => $data['metricId'] ?? null,
            'metricName' => $data['metricName'] ?? 'Unknown Metric',
            'unit' => $data['unit'] ?? '',
            'isDraft' => $data['isDraft'] ?? false,
            'lastUpdated' => $data['lastUpdated'] ?? date('Y-m-d H:i:s'),
            'totalEntries' => count(array_filter($data['monthlyData'] ?? [], function($entry) {
                return isset($entry['value']) && $entry['value'] !== '';
            }))
        ];
        
        $reports[] = $report;
    }
    
    $response = [
        'success' => true,
        'data' => $reports
    ];
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error in get_time_series_reports.php: ' . $e->getMessage());
}

// Return JSON response
echo json_encode($response);

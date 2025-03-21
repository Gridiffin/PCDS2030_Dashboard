<?php
// Include necessary files
require_once '../config/db_connect.php';
require_once '../auth/check_session.php';

// Make sure we're outputting clean JSON without any PHP errors/notices
ob_start();

// Set content type to JSON
header('Content-Type: application/json');

// Default response
$response = [
    'success' => false,
    'message' => 'An error occurred',
    'data' => []
];

try {
    // Check if user is authenticated
    if (!isAuthenticated()) {
        throw new Exception('Authentication required');
    }
    
    // Get database connection
    $conn = getDbConnection();
    
    // Query to fetch all metric types
    $stmt = $conn->query("
        SELECT 
            MetricTypeID as id,
            TypeKey as `key`,
            TypeName as name,
            Description as description
        FROM MetricTypes
        ORDER BY SortOrder, TypeName
    ");
    
    $metricTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no metric types found, add some default ones
    if (empty($metricTypes)) {
        $metricTypes = [
            ['id' => 'governance', 'name' => 'Governance'],
            ['id' => 'economic', 'name' => 'Economic Development'],
            ['id' => 'forestry', 'name' => 'Forestry'],
            ['id' => 'conservation', 'name' => 'Conservation']
        ];
    }
    
    $response['success'] = true;
    $response['data'] = $metricTypes;
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error in get_all_metric_types.php: ' . $e->getMessage());
}

// Clear any output buffering to ensure clean JSON
ob_end_clean();

// Return response
echo json_encode($response);
exit;
?>

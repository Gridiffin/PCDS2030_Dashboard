<?php
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

try {
    // Get database connection
    $conn = getDbConnection();
    
    // Get filtering parameters
    $sectorId = isset($_GET['sectorId']) ? intval($_GET['sectorId']) : null;
    
    // Build query based on filters
    $sql = "SELECT 
                mt.MetricTypeID as id,
                mt.TypeKey as `key`,
                mt.TypeName as name,
                mt.Description as description,
                mt.ChartType as chartType,
                s.SectorName as sectorName,
                s.SectorID as sectorId
            FROM MetricTypes mt
            LEFT JOIN Sectors s ON mt.SectorID = s.SectorID
            WHERE 1=1";
    
    $params = [];
    
    // Add sector filter if specified
    if ($sectorId) {
        $sql .= " AND (mt.SectorID = ? OR mt.SectorID IS NULL)";
        $params[] = $sectorId;
    }
    
    $sql .= " ORDER BY mt.SortOrder, mt.TypeName";
    
    // Execute query
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $metricTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'data' => $metricTypes
    ];
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error in get_metric_types.php: ' . $e->getMessage());
}

echo json_encode($response);
?>

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

try {
    // Get database connection
    $conn = getDbConnection();
    
    // Get filtering parameters
    $typeId = isset($_GET['typeId']) ? intval($_GET['typeId']) : null;
    
    // Get user's sector ID from agency (used for filtering)
    $stmt = $conn->prepare("SELECT SectorID FROM Agencies WHERE AgencyID = ?");
    $stmt->execute([$agencyId]);
    $userSectorId = $stmt->fetchColumn();
    
    // Build query based on filters
    $sql = "SELECT * FROM CustomMetrics WHERE AgencyID = ?";
    $params = [$agencyId];
    
    // Add type filter if specified
    if ($typeId) {
        $sql .= " AND SectorID = ?";
        $params[] = $typeId;
    }
    
    $sql .= " ORDER BY SortOrder, MetricName";
    
    // Execute query
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $metrics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'sectorId' => $userSectorId,
        'data' => $metrics
    ];
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error in get_sector_metrics.php: ' . $e->getMessage());
}

echo json_encode($response);

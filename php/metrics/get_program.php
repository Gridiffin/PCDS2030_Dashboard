<?php
// Include necessary files
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
    // Check if program ID is provided
    if (!isset($_GET['id'])) {
        throw new Exception('Program ID is required');
    }
    
    $programId = $_GET['id'];
    
    // Get database connection
    $conn = getDbConnection();
    
    // Get the user's agency ID from session
    $agencyId = $_SESSION['agency_id'] ?? null;
    
    if (!$agencyId) {
        throw new Exception('User agency not found');
    }
    
    // Check the actual column name in the database
    $checkColumnsQuery = "SHOW COLUMNS FROM metrics";
    $columnsStmt = $conn->query($checkColumnsQuery);
    $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Log the columns for debugging
    error_log("Metrics table columns: " . implode(", ", $columns));
    
    // Determine if we should use MetricTypeID or another column
    $metricTypeColumn = in_array('MetricTypeID', $columns) ? 'MetricTypeID' : 
                       (in_array('MetricType', $columns) ? 'MetricType' : null);
    
    // Query to fetch program data - using columns we know exist
    $stmt = $conn->prepare("
        SELECT MetricID, Data, Quarter, Year 
        FROM metrics 
        WHERE MetricID = ? AND AgencyID = ?
    ");
    
    $stmt->execute([$programId, $agencyId]);
    $metric = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$metric) {
        throw new Exception('Program not found or you do not have permission to access it');
    }
    
    // Decode the JSON data
    $programData = json_decode($metric['Data'], true);
    
    if (!$programData) {
        throw new Exception('Invalid program data format');
    }
    
    // Add quarter and year to program data
    $programData['quarter'] = $metric['Quarter'];
    $programData['year'] = $metric['Year'];
    
    // If the metric type column exists, add that too
    if ($metricTypeColumn && isset($metric[$metricTypeColumn])) {
        $programData['metricType'] = $metric[$metricTypeColumn];
    }
    
    $response['success'] = true;
    $response['message'] = 'Program data retrieved successfully';
    $response['data'] = $programData;
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error in get_program.php: ' . $e->getMessage());
}

// Return response
echo json_encode($response);
?>

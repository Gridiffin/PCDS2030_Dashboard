<?php
// Turn off error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start output buffering to prevent any unwanted output
ob_start();

// Start session to check authentication
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Include database connection and authentication
require_once '../config/db_connect.php';
require_once '../auth/check_session.php';

// Set content type to JSON
header('Content-Type: application/json');

// Default response
$response = [
    'success' => false,
    'message' => 'An error occurred',
    'data' => null
];

try {
    // Check if user is authenticated
    if (!isAuthenticated()) {
        throw new Exception('Authentication required');
    }
    
    // Get database connection
    $conn = getDbConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Get user's agency ID from session
    $userAgencyId = $_SESSION['agency_id'];
    
    // Get metrics data for drafts only - drafts will have status="draft" in JSON data
    $sql = "
        SELECT 
            m.MetricID, 
            m.MetricType, 
            m.Data, 
            m.Quarter, 
            m.Year, 
            m.AgencyID,
            a.AgencyName
        FROM 
            Metrics m
        JOIN 
            agencies a ON m.AgencyID = a.AgencyID
        WHERE 
            m.AgencyID = ? AND
            JSON_EXTRACT(m.Data, '$.status') = 'draft'
        ORDER BY 
            m.Year DESC, 
            m.Quarter DESC,
            m.MetricID DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userAgencyId]);
    $metrics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process metrics data
    $drafts = [];
    foreach ($metrics as $metric) {
        $data = json_decode($metric['Data'], true);
        if (!$data) {
            continue; // Skip this row if data can't be decoded
        }
        
        // Format the draft data
        $draft = [
            'id' => $metric['MetricID'],
            'programName' => $data['programName'] ?? 'Unnamed Program',
            'year' => $metric['Year'],
            'quarter' => $metric['Quarter'],
            'metricType' => $metric['MetricType'],
            'metricTypeName' => ucfirst($metric['MetricType']),
            'targetDescription' => $data['targetDescription'] ?? '',
            'targetSummary' => $data['targetSummary'] ?? '',
            'lastUpdated' => $data['lastUpdated'] ?? $metric['Year'] . '-01-01',
            'status' => 'draft'
        ];
        
        $drafts[] = $draft;
    }
    
    $response = [
        'success' => true,
        'data' => $drafts
    ];
    
} catch (Exception $e) {
    error_log('Error in get_drafts.php: ' . $e->getMessage());
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Clear any buffered output before sending JSON
ob_end_clean();
echo json_encode($response);
exit;
?>

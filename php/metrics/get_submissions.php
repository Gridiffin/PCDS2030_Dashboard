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
    
    // Build query based on filters
    $params = [];
    $whereConditions = [];
    
    // Get user's agency ID from session
    $userAgencyId = $_SESSION['agency_id'];
    
    // Apply filters
    if (isset($_GET['year']) && !empty($_GET['year'])) {
        $whereConditions[] = 'Year = ?';
        $params[] = $_GET['year'];
    }
    
    if (isset($_GET['quarter']) && !empty($_GET['quarter'])) {
        $whereConditions[] = 'Quarter = ?';
        $params[] = $_GET['quarter'];
    }
    
    if (isset($_GET['metricType']) && !empty($_GET['metricType'])) {
        $whereConditions[] = 'MetricType = ?';
        $params[] = $_GET['metricType'];
    }
    
    if (isset($_GET['agencyId']) && !empty($_GET['agencyId'])) {
        $whereConditions[] = 'AgencyID = ?';
        $params[] = $_GET['agencyId'];
    }
    
    // Build the WHERE clause
    $whereClause = '';
    if (!empty($whereConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    }
    
    // Get metrics data
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
        $whereClause
        ORDER BY 
            m.Year DESC, 
            m.Quarter DESC,
            m.MetricID DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $metrics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process metrics data
    $submissions = [];
    foreach ($metrics as $metric) {
        $data = json_decode($metric['Data'], true);
        
        // Determine if the current user can edit this metric (only if it belongs to their agency)
        $isEditable = ($metric['AgencyID'] == $userAgencyId);
        
        // Extract metric type name (in a real application, this would come from a lookup table)
        $metricTypeName = ucfirst($metric['MetricType']);
        
        // Format the submission data
        $submission = [
            'id' => $metric['MetricID'],
            'programName' => $data['programName'] ?? 'Unnamed Program',
            'year' => $metric['Year'],
            'quarter' => $metric['Quarter'],
            'metricType' => $metric['MetricType'],
            'metricTypeName' => $metricTypeName,
            'agencyId' => $metric['AgencyID'],
            'agencyName' => $metric['AgencyName'],
            'targetValue' => isset($data['target']) ? 
                             ($data['target']['value'] . ' ' . $data['target']['unit']) : 
                             'Not specified',
            'currentValue' => isset($data['status']) ? 
                              ($data['status']['currentValue'] . ' ' . $data['target']['unit']) : 
                              'Not specified',
            'lastUpdated' => $data['lastUpdated'] ?? $metric['Year'] . '-01-01',
            'status' => $data['status'] ?? 'in-progress',
            'isEditable' => $isEditable
        ];
        
        $submissions[] = $submission;
    }
    
    $response = [
        'success' => true,
        'data' => $submissions
    ];
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error in get_submissions.php: ' . $e->getMessage());
}

echo json_encode($response);
?>

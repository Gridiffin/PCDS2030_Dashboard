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
        $whereConditions[] = 'm.MetricTypeID = ?';
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
            m.MetricTypeID, 
            mt.TypeName AS MetricTypeName, 
            m.Data, 
            m.Quarter, 
            m.Year, 
            m.AgencyID,
            a.AgencyName
        FROM 
            Metrics m
        LEFT JOIN 
            MetricTypes mt ON m.MetricTypeID = mt.MetricTypeID
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
        if (!$data) {
            continue; // Skip this row if data can't be decoded
        }
        
        // Determine if the current user can edit this metric (only if it belongs to their agency)
        $isEditable = ($metric['AgencyID'] == $userAgencyId);
        
        // Extract metric type name (in a real application, this would come from a lookup table)
        $metricTypeName = ucfirst($metric['MetricTypeName']);
        
        // Process target value and current value safely
        $targetValue = '';
        $currentValue = '';
        
        if (isset($data['target'])) {
            if (isset($data['target']['value']) && isset($data['target']['unit'])) {
                $targetValue = $data['target']['value'] . ' ' . $data['target']['unit'];
            }
        }
        
        if (isset($data['status'])) {
            if (isset($data['status']['currentValue'])) {
                $currentValue = $data['status']['currentValue'];
                if (isset($data['target']['unit'])) {
                    $currentValue .= ' ' . $data['target']['unit'];
                }
            }
        }
        
        // Format the submission data
        $submission = [
            'id' => $metric['MetricID'],
            'programName' => $data['programName'] ?? 'Unnamed Program',
            'year' => $metric['Year'],
            'quarter' => $metric['Quarter'],
            'metricType' => $metric['MetricTypeID'],
            'metricTypeName' => $metricTypeName,
            'agencyId' => $metric['AgencyID'],
            'agencyName' => $metric['AgencyName'],
            'targetValue' => $targetValue,
            'targetSummary' => isset($data['targetSummary']) ? $data['targetSummary'] : $targetValue,
            'currentValue' => $currentValue,
            'statusSummary' => $data['statusSummary'] ?? '',
            'lastUpdated' => $data['lastUpdated'] ?? $metric['Year'] . '-01-01',
            'status' => $data['status'] ?? 'in-progress',
            'statusCategory' => $data['statusCategory'] ?? $data['status'] ?? 'in-progress',
            'statusColor' => $data['statusColor'] ?? null,
            'isEditable' => $isEditable,
            'isQualitative' => $data['isQualitative'] ?? false
        ];
        
        $submissions[] = $submission;
    }
    
    $response = [
        'success' => true,
        'data' => $submissions
    ];
    
} catch (Exception $e) {
    error_log('Error in get_submissions.php: ' . $e->getMessage());
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Clear any buffered output before sending JSON
ob_end_clean();
echo json_encode($response);
exit;
?>

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
    // Check if ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        $response['message'] = 'Submission ID is required';
        echo json_encode($response);
        exit;
    }
    
    $metricId = $_GET['id'];
    
    // Get database connection
    $conn = getDbConnection();
    
    // Get user's agency ID from session
    $userAgencyId = $_SESSION['agency_id'];
    
    // Get metric data
    $stmt = $conn->prepare("
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
            m.MetricID = ?
    ");
    
    $stmt->execute([$metricId]);
    $metric = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$metric) {
        $response['message'] = 'Submission not found';
        echo json_encode($response);
        exit;
    }
    
    // Decode JSON data
    $data = json_decode($metric['Data'], true);
    
    // Determine if the current user can edit this metric
    $isEditable = ($metric['AgencyID'] == $userAgencyId);
    
    // Format the submission details
    $details = [
        'id' => $metric['MetricID'],
        'programName' => $data['programName'] ?? 'Unnamed Program',
        'description' => $data['programDescription'] ?? '',
        'year' => $metric['Year'],
        'quarter' => $metric['Quarter'],
        'metricType' => $metric['MetricType'],
        'metricTypeName' => ucfirst($metric['MetricType']),
        'agencyId' => $metric['AgencyID'],
        'agencyName' => $metric['AgencyName'],
        'indicator' => $data['target']['indicator'] ?? '',
        'targetValue' => $data['target']['value'] ?? '',
        'targetUnit' => $data['target']['unit'] ?? '',
        'targetDeadline' => $data['target']['deadline'] ?? '',
        'currentValue' => $data['status']['currentValue'] ?? '',
        'completionPercentage' => $data['status']['completionPercentage'] ?? 0,
        'statusDate' => $data['status']['date'] ?? '',
        'statusNotes' => $data['status']['notes'] ?? '',
        'challenges' => $data['status']['challenges'] ?? '',
        'lastUpdated' => $data['lastUpdated'] ?? '',
        'status' => $data['status'] ?? 'in-progress',
        'submittedBy' => $data['submittedBy'] ?? '',
        'isEditable' => $isEditable
    ];
    
    // In a real implementation, we would get file information from a files table
    $details['supportingFiles'] = [];
    
    $response = [
        'success' => true,
        'data' => $details
    ];
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error in get_submission_details.php: ' . $e->getMessage());
}

echo json_encode($response);
?>

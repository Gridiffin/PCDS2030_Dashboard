<?php
// Include necessary files
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

    // Check if submission ID is provided
    if (!isset($_GET['id'])) {
        throw new Exception('Submission ID is required');
    }
    
    $submissionId = $_GET['id'];
    
    // Get database connection
    $conn = getDbConnection();
    
    // Query to fetch submission data
    $stmt = $conn->prepare("
        SELECT 
            m.MetricID as id, 
            m.Data, 
            m.Quarter, 
            m.Year,
            mt.TypeName as metricTypeName,
            a.AgencyName as agencyName
        FROM metrics m
        LEFT JOIN MetricTypes mt ON m.MetricTypeID = mt.MetricTypeID
        LEFT JOIN agencies a ON m.AgencyID = a.AgencyID
        WHERE m.MetricID = ?
    ");
    
    $stmt->execute([$submissionId]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$submission) {
        throw new Exception('Submission not found');
    }
    
    // Parse the JSON data
    $submissionData = json_decode($submission['Data'], true) ?: [];
    
    // Combine database fields with JSON data
    $responseData = array_merge($submissionData, [
        'id' => $submission['id'],
        'quarter' => $submission['Quarter'],
        'year' => $submission['Year'],
        'metricTypeName' => $submission['metricTypeName'],
        'agencyName' => $submission['agencyName'],
        // These fields map from the JSON structure to what the frontend expects
        'programName' => $submissionData['programName'] ?? 'Untitled Program',
        'description' => $submissionData['description'] ?? '',
        'indicator' => $submissionData['targetText'] ?? '',
        'targetDescription' => $submissionData['targetDescription'] ?? '',
        'statusDate' => $submissionData['statusDate'] ?? date('Y-m-d'),
        'statusNotes' => $submissionData['statusText'] ?? ''
    ]);
    
    $response['success'] = true;
    $response['message'] = 'Submission details retrieved successfully';
    $response['data'] = $responseData;
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error in get_submission_details.php: ' . $e->getMessage());
}

// Clear any output buffering to ensure clean JSON
if (ob_get_length()) ob_clean();

// Return response
echo json_encode($response);
exit;
?>

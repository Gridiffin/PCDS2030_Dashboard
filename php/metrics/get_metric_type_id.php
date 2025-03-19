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

// Default response
$response = [
    'success' => false,
    'message' => '',
    'typeId' => null,
    'typeName' => null
];

// Check if type key is provided
if (!isset($_GET['key']) || empty($_GET['key'])) {
    $response['message'] = 'Type key parameter is required';
    echo json_encode($response);
    exit;
}

$typeKey = $_GET['key'];

try {
    // Get database connection
    $conn = getDbConnection();
    
    // Get the metric type ID
    $stmt = $conn->prepare("SELECT MetricTypeID, TypeName FROM MetricTypes WHERE TypeKey = ?");
    $stmt->execute([$typeKey]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        throw new Exception("Metric type key '$typeKey' not found");
    }
    
    $response = [
        'success' => true,
        'typeId' => $result['MetricTypeID'],
        'typeName' => $result['TypeName']
    ];
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error in get_metric_type_id.php: ' . $e->getMessage());
}

// Return JSON response
echo json_encode($response);

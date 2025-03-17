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
    // In a real implementation, this would query a metric_types table
    // and join with agency_metric_types to get allowed types per agency
    
    // For now, we'll return a static list of metric types
    // In a real implementation, this would be filtered based on the agency
    $metricTypes = [
        ['id' => 'forestry', 'name' => 'Forestry'],
        ['id' => 'conservation', 'name' => 'Conservation'],
        ['id' => 'land', 'name' => 'Land Use'],
        ['id' => 'water', 'name' => 'Water Resources'],
        ['id' => 'energy', 'name' => 'Energy'],
        ['id' => 'social', 'name' => 'Social Development'],
        ['id' => 'economic', 'name' => 'Economic Development'],
        ['id' => 'governance', 'name' => 'Governance']
    ];
    
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

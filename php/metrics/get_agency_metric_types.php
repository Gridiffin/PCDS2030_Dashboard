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
    // Get user's agency ID from session
    $agencyId = $_SESSION['agency_id'];
    
    // In a real implementation, you would query a database table
    // that defines which metric types each agency can use
    
    // For demo purposes, we'll return pre-defined lists based on agency ID
    switch ($agencyId) {
        case 1: // Main Agency
            $allowedTypes = [
                ['id' => 'governance', 'name' => 'Governance'],
                ['id' => 'economic', 'name' => 'Economic Development']
            ];
            break;
        case 2: // Forestry Department
            $allowedTypes = [
                ['id' => 'forestry', 'name' => 'Forestry'],
                ['id' => 'conservation', 'name' => 'Conservation']
            ];
            break;
        case 3: // Water Resources Department
            $allowedTypes = [
                ['id' => 'water', 'name' => 'Water Resources']
            ];
            break;
        case 4: // Energy Department
            $allowedTypes = [
                ['id' => 'energy', 'name' => 'Energy']
            ];
            break;
        case 5: // Environmental Protection Agency
            $allowedTypes = [
                ['id' => 'conservation', 'name' => 'Conservation'],
                ['id' => 'land', 'name' => 'Land Use'],
                ['id' => 'water', 'name' => 'Water Resources']
            ];
            break;
        default:
            // Default to a limited set
            $allowedTypes = [
                ['id' => 'governance', 'name' => 'Governance']
            ];
            break;
    }
    
    $response = [
        'success' => true,
        'data' => $allowedTypes
    ];
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error in get_agency_metric_types.php: ' . $e->getMessage());
}

echo json_encode($response);
?>

<?php
// Turn off error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start output buffering
ob_start();

// Include database connection and check_session
// (We include check_session.php first, which handles the session start)
require_once 'check_session.php';
require_once '../config/db_connect.php';

// Set content type to JSON
header('Content-Type: application/json');

// Default response
$response = [
    'success' => false,
    'message' => 'Not authenticated',
    'user' => null
];

try {
    // Check if user is logged in
    if (isAuthenticated()) {
        // Get database connection
        $conn = getDbConnection();
        
        if (!$conn) {
            throw new Exception("Database connection failed");
        }
        
        // Get user data from session and database
        $userId = $_SESSION['user_id'];
        
        // Query user details from database
        $stmt = $conn->prepare('
            SELECT 
                u.UserID, 
                u.Username,
                u.AgencyID,
                a.AgencyName
            FROM 
                users u
            JOIN 
                agencies a ON u.AgencyID = a.AgencyID
            WHERE 
                u.UserID = ?
        ');
        
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // In a real implementation, this would query a user_metrics_access table
            // For now, use the same mock data structure as the mock_data.js file
            
            // Get allowed metric types for this agency
            $agencyId = $user['AgencyID'];
            
            // Map agency IDs to allowed metric types (similar to mock_data.js)
            $agencyMetricTypes = [
                1 => [['id' => 'governance', 'name' => 'Governance'], ['id' => 'economic', 'name' => 'Economic Development']],
                2 => [['id' => 'forestry', 'name' => 'Forestry'], ['id' => 'conservation', 'name' => 'Conservation']],
                3 => [['id' => 'water', 'name' => 'Water Resources']],
                4 => [['id' => 'energy', 'name' => 'Energy']],
                5 => [['id' => 'conservation', 'name' => 'Conservation'], ['id' => 'land', 'name' => 'Land Use'], ['id' => 'water', 'name' => 'Water Resources']]
            ];
            
            // Get allowed types for the user's agency or provide a default
            $allowedMetricTypes = isset($agencyMetricTypes[$agencyId]) ? $agencyMetricTypes[$agencyId] : [['id' => 'governance', 'name' => 'Governance']];
            
            // Format user data for response
            $userData = [
                'id' => $user['UserID'],
                'username' => $user['Username'],
                'agencyId' => $user['AgencyID'],
                'agencyName' => $user['AgencyName'],
                'allowedMetricTypes' => $allowedMetricTypes
            ];
            
            $response = [
                'success' => true,
                'message' => 'User authenticated',
                'user' => $userData
            ];
        } else {
            throw new Exception("User not found in database");
        }
    } else {
        // DEVELOPMENT MODE - Return a demo user for testing if not authenticated
        
        // Create a demo user
        $demoUser = [
            'id' => 101,
            'username' => 'demo_user',
            'email' => 'demo@example.com',
            'agencyId' => 2,
            'agencyName' => 'Forestry Department',
            'allowedMetricTypes' => [
                ['id' => 'forestry', 'name' => 'Forestry'],
                ['id' => 'conservation', 'name' => 'Conservation']
            ]
        ];
        
        // Also set the demo user in session for consistency
        $_SESSION['authenticated'] = true;
        $_SESSION['user_id'] = $demoUser['id'];
        $_SESSION['username'] = $demoUser['username'];
        $_SESSION['agency_id'] = $demoUser['agencyId'];
        
        $response = [
            'success' => true,
            'message' => 'DEMO MODE: Using demo user',
            'user' => $demoUser
        ];
        
        error_log("DEMO MODE: User not authenticated, returning demo user");
    }
} catch (Exception $e) {
    error_log("Error in get_current_user.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    $response = [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'user' => null
    ];
}

// Clear any buffered output before sending JSON
ob_end_clean();
echo json_encode($response);
exit;
?>

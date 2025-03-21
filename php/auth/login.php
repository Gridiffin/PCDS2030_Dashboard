<?php
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log received data (for debugging only, remove in production)
$logFile = '../../login_attempts.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Login attempt received\n", FILE_APPEND);

// Include database connection and functions
require_once '../config/db_connect.php';
require_once 'check_session.php';

// Function to sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Get the raw input and check if it's JSON
$rawInput = file_get_contents('php://input');
$isJson = !empty($rawInput) && substr($rawInput, 0, 1) === '{';

$username = '';
$password = '';

// Log the request type
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Request type: " . ($_SERVER['REQUEST_METHOD']) . 
                 ", Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set') . 
                 ", is JSON: " . ($isJson ? 'yes' : 'no') . "\n", FILE_APPEND);

// Process the input based on format
if ($isJson) {
    // Handle JSON input
    $data = json_decode($rawInput, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $username = isset($data['username']) ? sanitize($data['username']) : '';
        $password = isset($data['password']) ? $data['password'] : '';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - JSON data processed\n", FILE_APPEND);
    } else {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Invalid JSON: " . json_last_error_msg() . "\n", FILE_APPEND);
    }
} else {
    // Handle form data
    $username = isset($_POST['username']) ? sanitize($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Form data processed\n", FILE_APPEND);
}

// Log the attempted username
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Authenticating: $username\n", FILE_APPEND);

// Try to validate against the database
$userData = checkCredentials($username, $password);

if ($userData) {
    // Set session variables
    $_SESSION['authenticated'] = true;
    $_SESSION['user_id'] = $userData['user_id'];
    $_SESSION['username'] = $userData['username'];
    $_SESSION['role_id'] = $userData['role_id'];
    $_SESSION['agency_id'] = $userData['agency_id'];
    
    // Log success
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Login successful for: $username (Role: {$userData['role_id']})\n", FILE_APPEND);
    
    // Return success response with redirect URL
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => ($userData['role_id'] == 1) ? 'admin_dashboard.php' : 'user_dashboard.php',
        'session' => session_id()
    ]);
} else {
    // Fallback to the hardcoded users if database authentication fails
    if ($username === 'user' && $password === 'user123') {
        // Set session variables
        $_SESSION['authenticated'] = true;
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = $username;
        $_SESSION['role_id'] = 2; // Regular user role
        $_SESSION['agency_id'] = 1;
        $_SESSION['agency_name'] = 'Main Agency';
        
        // Log success
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Login successful for: $username (hardcoded)\n", FILE_APPEND);
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => 'user_dashboard.php',
            'session' => session_id()
        ]);
    } else if ($username === 'admin' && $password === 'admin123') {
        // Set session variables
        $_SESSION['authenticated'] = true;
        $_SESSION['user_id'] = 2;
        $_SESSION['username'] = $username;
        $_SESSION['role_id'] = 1; // Admin role
        $_SESSION['agency_id'] = 1;
        
        // Log success
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Admin login successful for: $username (hardcoded)\n", FILE_APPEND);
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => 'admin_dashboard.php',
            'session' => session_id()
        ]);
    } else {
        // Log failure
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Login failed for: $username\n", FILE_APPEND);
        
        // Return error response
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password',
            'provided' => ['user' => $username, 'pass_length' => strlen($password)]
        ]);
    }
}
?>

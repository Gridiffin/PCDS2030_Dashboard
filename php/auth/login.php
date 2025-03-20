<?php
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log received data (for debugging only, remove in production)
$logFile = '../../login_attempts.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Login attempt - POST data: " . 
                 json_encode($_POST) . "\n", FILE_APPEND);

// Function to sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method',
        'method' => $_SERVER['REQUEST_METHOD']
    ]);
    exit;
}

// Get username and password from POST data
$username = isset($_POST['username']) ? sanitize($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Log authentication attempt (for debugging only, remove in production)
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Authenticating: $username\n", FILE_APPEND);

// Simple authentication for demonstration
// In a real application, you would validate against a database
if ($username === 'user' && $password === 'user123') {
    // Set session variables
    $_SESSION['authenticated'] = true;
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = $username;
    $_SESSION['role_id'] = 2; // Regular user role
    $_SESSION['agency_id'] = 1;
    $_SESSION['agency_name'] = 'Main Agency';
    
    // Log success
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Login successful for: $username\n", FILE_APPEND);
    
    // Return success response with redirect URL - fix the path
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => 'user_dashboard.php', // Use relative path
        'session' => session_id()
    ]);
} 
// Admin login
else if ($username === 'admin' && $password === 'admin123') {
    // Set session variables
    $_SESSION['authenticated'] = true;
    $_SESSION['user_id'] = 2;
    $_SESSION['username'] = $username;
    $_SESSION['role_id'] = 1; // Admin role
    $_SESSION['agency_id'] = 1;
    $_SESSION['agency_name'] = 'Main Agency';
    
    // Log success
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Admin login successful for: $username\n", FILE_APPEND);
    
    // Return success response with redirect URL - fix the path
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => 'admin_dashboard.php', // Use relative path
        'session' => session_id()
    ]);
} 
else {
    // Log failure
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Login failed for: $username\n", FILE_APPEND);
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Invalid username or password',
        'provided' => ['user' => $username, 'pass_length' => strlen($password)]
    ]);
}
?>

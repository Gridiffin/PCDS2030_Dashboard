<?php
// Start session
session_start();

// Include database connection
require_once '../config/db_connect.php';

// Set default response
$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

// Process login request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);

    // Basic validation
    if (empty($username) || empty($password)) {
        $response['message'] = 'Please fill in all fields';
        echo json_encode($response);
        exit;
    }

    try {
        // Get database connection
        $conn = getDbConnection();
        
        if (!$conn) {
            throw new Exception('Database connection failed');
        }

        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare('SELECT UserID, username, password, RoleID, AgencyID FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Check if user exists and verify password
        if ($user && password_verify($password, $user['password'])) {
            // Authentication successful
            // Store user data in session
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_id'] = $user['RoleID'];
            $_SESSION['agency_id'] = $user['AgencyID'];
            $_SESSION['authenticated'] = true;
            $_SESSION['last_activity'] = time();
            
            // Log the successful login
            $stmt = $conn->prepare('INSERT INTO logs (user_id, action, ip_address) VALUES (?, ?, ?)');
            $stmt->execute([
                $user['UserID'],
                'login',
                $_SERVER['REMOTE_ADDR']
            ]);
            
            // Set response based on role
            $response['success'] = true;
            $response['message'] = 'Login successful!';
            
            if ($user['RoleID'] == 1) {
                // Admin role
                $response['redirect'] = 'admin_dashboard.html';
            } else {
                // Regular user
                $response['redirect'] = 'user_dashboard.html';
            }
        } else {
            // Authentication failed
            $response['message'] = 'Invalid username or password';
        }
    } catch (Exception $e) {
        // Log the error (don't expose details in production)
        error_log('Login error: ' . $e->getMessage());
        $response['message'] = 'An error occurred during login. Please try again.';
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>

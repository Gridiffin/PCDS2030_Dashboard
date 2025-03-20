<?php
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Log logout attempt (for debugging only, remove in production)
$logFile = '../../login_attempts.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Logout attempt for user: " . 
                 ($_SESSION['username'] ?? 'unknown') . "\n", FILE_APPEND);

// Destroy the session
$_SESSION = array();

// If a session cookie is used, destroy it
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Log success
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Logout successful\n", FILE_APPEND);

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Logout successful',
    'redirect' => 'login.php'
]);
?>

<?php
/**
 * Logout script
 * 
 * Destroys the session and redirects to login page.
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = array();

// If using session cookies, delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?>

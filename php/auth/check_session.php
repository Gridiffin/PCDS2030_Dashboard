<?php
// Start the session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Check if user is logged in
function isAuthenticated() {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

// Check if user is admin
function isAdmin() {
    return isAuthenticated() && isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;
}

// Check for session timeout (30 minutes)
function checkSessionTimeout() {
    $timeout = 1800; // 30 minutes in seconds
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        // Session has expired
        session_unset();
        session_destroy();
        return false;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    return true;
}

// Function to redirect if not authenticated
function requireLogin() {
    if (!isAuthenticated() || !checkSessionTimeout()) {
        header('Location: index.html');
        exit;
    }
}

// Function to redirect if not admin
function requireAdmin() {
    if (!isAdmin() || !checkSessionTimeout()) {
        header('Location: index.html');
        exit;
    }
}
?>

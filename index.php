<?php
// Start session
session_start();

// Check if user is logged in
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    // Check user role and redirect accordingly
    if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1) {
        // Admin redirect
        header('Location: admin_dashboard.php');
    } else {
        // User redirect
        header('Location: user_dashboard.php');
    }
    exit;
} else {
    // Not logged in, redirect to login page
    header('Location: login.php');
    exit;
}
?>

<?php
// Start session
session_start();

// If user is already logged in, redirect to the appropriate dashboard
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1) {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: user_dashboard.php');
    }
    exit;
}

// Variables for templates
$pageVars = [
    'pageTitle' => 'Login - PCDS2030 Dashboard',
    'hideHeader' => true,
    'additionalCss' => [
        'css/home.css'
    ],
    'scripts' => [
        'js/core/notifications.js',  // Will be loaded as module
        'js/core/api-client.js',     // Will be loaded as module
        'js/login.js'                // Will be loaded as module as per footer.php update
    ],
    'notification' => true
];

// Include header with custom styling
extract($pageVars);
require_once 'templates/header.php';

// Include the login content
require_once 'content/login_content.php';

// Include footer
require_once 'templates/footer.php';
?>

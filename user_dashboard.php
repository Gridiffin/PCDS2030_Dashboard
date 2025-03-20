<?php
require_once 'includes/template_manager.php';

// Debug mode - only show in development
$DEBUG = true;
$debug_info = '';

if (!is_logged_in() && $DEBUG) {
    $debug_info = "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px; color: #721c24; border: 1px solid #f5c6cb;'>";
    $debug_info .= "<h3>Debug Information - Session Error</h3>";
    $debug_info .= "<p>Login required but session is not authenticated</p>";
    $debug_info .= "<p>Session ID: " . session_id() . "</p>";
    $debug_info .= "<p>Session data: <pre>" . print_r($_SESSION, true) . "</pre></p>";
    $debug_info .= "<p>Please <a href='login.php' style='color: #721c24; font-weight: bold;'>login</a> to continue.</p>";
    $debug_info .= "</div>";
    
    // Output debug info
    echo "<!DOCTYPE html><html><head><title>Session Debug</title></head><body>";
    echo $debug_info;
    echo "</body></html>";
    exit;
}

// Require user to be logged in
require_login();

// Variables for templates
$pageVars = [
    'pageTitle' => 'User Dashboard',
    'userType' => 'user',
    'showLogout' => true,
    'includeResponsive' => true, 
    'includeMobileJs' => true,
    'additionalCss' => [
        'css/user_dashboard.css' // Explicitly include the user_dashboard.css file
    ],
    'scripts' => [
        'js/user_dashboard.js'
    ]
];

// Render the page
render_page('content/user_dashboard_content.php', $pageVars);
?>

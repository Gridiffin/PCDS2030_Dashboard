<?php
require_once 'includes/template_manager.php';

// Require admin privileges
require_admin();

// Variables for templates
$pageVars = [
    'pageTitle' => 'Admin Dashboard',
    'userType' => 'admin',
    'showLogout' => true,
    'includeResponsive' => true,
    'includeMobileJs' => true,
    'additionalCss' => [
        'css/admin_dashboard.css' 
    ],
    'scripts' => [
        'js/admin_dashboard.js'
    ]
];

// Render the page
render_page('content/admin_dashboard_content.php', $pageVars);
?>

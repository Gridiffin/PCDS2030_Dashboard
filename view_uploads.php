<?php
require_once 'includes/template_manager.php';

// Require user to be logged in
require_login();

// Variables for templates
$pageVars = [
    'pageTitle' => 'View Submissions',
    'userType' => 'user',
    'showAgencyBadge' => true,
    'showLogout' => false,
    'includeForms' => true,
    'includeTables' => true,
    'includeResponsive' => true,
    'includeMobileJs' => true,
    'notification' => true,
    'additionalNavButtons' => [
        [
            'href' => 'user_dashboard.php',
            'text' => 'Dashboard',
            'icon' => 'home'
        ]
    ],
    'scripts' => [
        'js/view_uploads.js'
    ],
    'styles' => [
        '/pcds2030_dashboard/css/view_uploads.css' // Use absolute path
    ]
];

// Render the page
render_page('content/view_uploads_content.php', $pageVars);
?>

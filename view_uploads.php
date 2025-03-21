<?php
require_once 'includes/template_manager.php';

// Require user to be logged in
require_login();

// Variables for templates
$pageVars = [
    'pageTitle' => 'View Submissions | PCDS 2030 Dashboard',
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
        'css/base.css',
        'css/tables.css',
        'css/view_uploads.css',
        'css/styles.css'  // This is correctly included
    ]
];

// Include Font Awesome for icons
$includeFontAwesome = true;

// Render the page
render_page('content/view_uploads_content.php', $pageVars);
?>

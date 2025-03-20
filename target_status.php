<?php
require_once 'includes/template_manager.php';

// Require user to be logged in
require_login();

// Variables for templates
$pageVars = [
    'pageTitle' => 'Target & Status',
    'userType' => 'user',
    'showAgencyBadge' => true,
    'showLogout' => false,
    'includeForms' => true,
    'includeResponsive' => true,
    'includeMobileJs' => true,
    'notification' => true,
    'additionalNavButtons' => [
        [
            'href' => 'user_dashboard.php',
            'text' => 'Dashboard',
            'icon' => 'home'
        ],
        [
            'href' => 'view_uploads.php',
            'text' => 'View Submissions',
            'icon' => 'list'
        ]
    ],
    'scripts' => [
        'js/target_status.js'
    ]
];

// Render the page
render_page('content/target_status_content.php', $pageVars);
?>

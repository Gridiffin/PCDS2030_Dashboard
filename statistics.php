<?php
require_once 'includes/template_manager.php';

// Require user to be logged in
require_login();

// Variables for templates
$pageVars = [
    'pageTitle' => 'Statistics',
    'userType' => 'user',
    'showLogout' => true,
    'includeForms' => true,
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
        'js/statistics.js'
    ]
];

// Render the page
render_page('content/statistics_content.php', $pageVars);
?>

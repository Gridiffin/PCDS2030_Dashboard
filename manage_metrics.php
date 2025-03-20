<?php
require_once 'includes/template_manager.php';

// Require user to be logged in
require_login();

// Variables for templates
$pageVars = [
    'pageTitle' => 'Manage Metrics',
    'userType' => 'user',
    'showAgencyBadge' => true,
    'showLogout' => false,
    'includeForms' => true,
    'includeTables' => true,
    'includeResponsive' => true,
    'includeMobileJs' => true,
    'notification' => true,
    'additionalCss' => [
        'css/manage_metrics.css',
        'css/metric_selector.css'
    ],
    'additionalNavButtons' => [
        [
            'href' => 'user_dashboard.php',
            'text' => 'Dashboard',
            'icon' => 'home'
        ]
    ],
    'scripts' => [
        'js/manage_metrics.js'
    ]
];

// Render the page
render_page('content/manage_metrics_content.php', $pageVars);
?>

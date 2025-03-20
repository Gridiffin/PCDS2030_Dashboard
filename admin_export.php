<?php
require_once 'includes/template_manager.php';

// Require admin privileges
require_admin();

// Variables for templates
$pageVars = [
    'pageTitle' => 'Admin - Export Reports',
    'userType' => 'admin',
    'showLogout' => true,
    'includeForms' => true,
    'includeTables' => true,
    'includeResponsive' => true,
    'includeMobileJs' => true,
    'notification' => true,
    'additionalNavButtons' => [
        [
            'href' => 'admin_dashboard.php',
            'text' => 'Dashboard',
            'icon' => 'home'
        ]
    ],
    'scripts' => [
        'js/admin_export.js'
    ]
];

// Render the page
render_page('content/admin_export_content.php', $pageVars);
?>

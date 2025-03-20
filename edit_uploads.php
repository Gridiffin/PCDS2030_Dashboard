<?php
require_once 'includes/template_manager.php';

// Require user to be logged in
require_login();

// Variables for templates
$pageVars = [
    'pageTitle' => 'Edit Uploads',
    'userType' => 'user',
    'showLogout' => true,
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
        'js/edit_uploads.js'
    ]
];

// Render the page
render_page('content/edit_uploads_content.php', $pageVars);
?>

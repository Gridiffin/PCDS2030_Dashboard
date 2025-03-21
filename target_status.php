<?php
require_once 'includes/template_manager.php';

// Require user to be logged in
require_login();

// Get program ID if editing an existing program
$programId = isset($_GET['program']) ? $_GET['program'] : '';

// Variables for templates
$pageVars = [
    'pageTitle' => $programId ? 'Edit Target Status' : 'New Target Status',
    'userType' => 'user',
    'showAgencyBadge' => true,
    'showLogout' => true,
    'includeForms' => true,
    'includeResponsive' => true, 
    'includeMobileJs' => true,
    'notification' => true,
    'additionalCss' => [
        'css/target_status.css'
    ],
    'additionalNavButtons' => [
        [
            'href' => 'view_uploads.php',
            'text' => 'Back to Submissions',
            'icon' => 'chevron-left'
        ]
    ],
    'scripts' => [
        'js/target_status.js'
    ],
    'programId' => $programId
];

// Render the page
render_page('content/target_status_content.php', $pageVars);
?>

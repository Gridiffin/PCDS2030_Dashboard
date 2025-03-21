<?php
// Start session with more secure parameters
if (session_status() == PHP_SESSION_NONE) {
    // Set session cookie parameters for better security (optional for development)
    /*
    session_set_cookie_params([
        'lifetime' => 0,              // 0 = until browser is closed
        'path' => '/',                // Cookie path
        'domain' => '',               // Cookie domain
        'secure' => false,            // Cookie sent only over HTTPS
        'httponly' => true,           // Cookie not accessible via JavaScript
        'samesite' => 'Strict'        // Cookie sent only for same-site requests
    ]);
    */
    session_start();
}

/**
 * Template Manager
 * Provides functions for rendering pages with templates
 */

/**
 * Helper function to render a page using the template system
 * 
 * @param string $contentFile Path to content PHP file
 * @param array $pageVars Variables to pass to the templates
 */
function render_page($contentFile, $pageVars = []) {
    // Extract variables for template use
    extract($pageVars);

    // Debug included styles
    if (!empty($styles)) {
        error_log('Included styles: ' . implode(', ', $styles));
    }
    
    // Include header
    require_once 'templates/header.php';
    
    // Include content
    require_once $contentFile;
    
    // Include footer
    require_once 'templates/footer.php';
}

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

/**
 * Check if current user is an admin
 * 
 * @return bool True if admin, false otherwise
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;
}

/**
 * Require user to be logged in
 * Redirects to login page if not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        // Set a session message to inform the user why they were redirected
        $_SESSION['login_required_message'] = "Please login to access this page.";
        header('Location: login.php');
        exit;
    }
}

/**
 * Require user to be an admin
 * Redirects to login page if not logged in or not an admin
 */
function require_admin() {
    require_login(); // First ensure user is logged in
    
    if (!is_admin()) {
        // Set a session message to inform the user why they were redirected
        $_SESSION['login_required_message'] = "Admin privileges required to access this page.";
        header('Location: user_dashboard.php');
        exit;
    }
}

/**
 * Get current user data
 * 
 * @return array User data or empty array if not logged in
 */
function get_authenticated_user() {
    if (!is_logged_in()) {
        return [];
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'role_id' => $_SESSION['role_id'] ?? null,
        'agency_id' => $_SESSION['agency_id'] ?? null,
        'agency_name' => $_SESSION['agency_name'] ?? null
    ];
}
?>

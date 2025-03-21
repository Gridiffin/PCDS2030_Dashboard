# PCDS2030 Dashboard

This is the dashboard for the PCDS2030 project, providing data management and visualization tools for sustainability tracking.

## System Requirements

- PHP 8.0+
- MySQL 5.7+
- Web server (Apache, Nginx, etc.)
- Modern web browser

## Project Structure

- `/content/` - PHP content fragments for template system
- `/templates/` - Header, footer, and other reusable template parts
- `/includes/` - PHP include files and utility functions
- `/js/` - JavaScript files
- `/css/` - CSS stylesheets
- `/assets/` - Images, fonts, and other static files
- `/php/` - Backend PHP API files
- `/utils/` - Utility scripts
- `/database/` - Database schema and setup scripts

## Template System

This project uses a custom PHP template system that separates content from presentation:

1. Each page consists of a main PHP file (e.g. `user_dashboard.php`) that:
   - Loads the template manager
   - Sets variables for templates
   - Renders the page using a content file

2. Content files (in `/content/`) contain the main HTML for each page, without header or footer

3. The template manager handles:
   - Including headers and footers
   - Managing authentication
   - Setting up common elements

### Example

```php
<?php
// Main PHP file (e.g. some_page.php)
require_once 'includes/template_manager.php';

$pageVars = [
    'pageTitle' => 'Page Title',
    'userType' => 'user',
    'showLogout' => true,
    // Other variables as needed
];

render_page('content/some_page_content.php', $pageVars);
?>
```

## Authentication

Authentication is managed through PHP sessions. The `template_manager.php` provides:

- `require_login()` - Ensures user is authenticated
- `require_admin()` - Ensures user is authenticated and has admin role
- `is_admin()` - Checks if current user is an admin

## Utility Scripts

The `/utils/` directory contains helpful scripts:

- `update_js_references.php` - Updates .html references to .php in JavaScript files
- `verify_php_links.php` - Verifies that PHP files use .php extensions in links

## Adding New Pages

To add a new page:

1. Create a content file in `/content/` (e.g. `new_feature_content.php`)
2. Create a main PHP file (e.g. `new_feature.php`) that uses the template system
3. Add any necessary JavaScript and CSS

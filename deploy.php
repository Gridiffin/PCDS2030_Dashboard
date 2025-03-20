<?php
/**
 * Simple deployment script for PCDS2030 Dashboard
 * 
 * WARNING: This is a basic deployment script and should only be used in development environments.
 * For production, use a proper CI/CD pipeline or deployment tool.
 */

// Only allow execution from command line
if (php_sapi_name() !== 'cli') {
    die("This script can only be executed from the command line.");
}

// Configuration
$config = [
    'source_dir' => __DIR__,
    'remote_host' => 'your-remote-server.com',
    'remote_user' => 'your-username',
    'remote_dir' => '/path/to/remote/directory/',
    'exclude_patterns' => [
        '.git*',
        'deploy.php',
        'debug*',
        'bin/*',
        '*.log',
        'temp/*',
        'uploads/*',
        'config.local.php'
    ],
    'pre_commands' => [
        'cd ' . __DIR__ . ' && php utils/update_js_references.php',
        'cd ' . __DIR__ . ' && php utils/verify_php_links.php'
    ],
    'post_commands' => [
        'rm -rf {{remote_dir}}temp/*',
        'chmod 755 {{remote_dir}}',
        'chmod 644 {{remote_dir}}*.php'
    ]
];

// Colors for terminal output
define('COLOR_GREEN', "\033[0;32m");
define('COLOR_RED', "\033[0;31m");
define('COLOR_YELLOW', "\033[0;33m");
define('COLOR_BLUE', "\033[0;34m");
define('COLOR_RESET', "\033[0m");

// Print colored message
function colorMsg($msg, $color = COLOR_RESET) {
    echo $color . $msg . COLOR_RESET . PHP_EOL;
}

// Print section header
function printSection($title) {
    echo PHP_EOL;
    colorMsg("=== $title ===", COLOR_BLUE);
}

// Execute command and return output
function execCommand($command, $remote = false) {
    if ($remote) {
        $command = 'ssh ' . $GLOBALS['config']['remote_user'] . '@' . $GLOBALS['config']['remote_host'] . ' "' . $command . '"';
    }
    
    colorMsg("Executing: $command", COLOR_YELLOW);
    
    exec($command, $output, $return_var);
    
    if ($return_var !== 0) {
        colorMsg("ERROR: Command failed with code $return_var", COLOR_RED);
        echo implode(PHP_EOL, $output) . PHP_EOL;
        return false;
    }
    
    return $output;
}

// Process deployment
function deploy() {
    global $config;
    
    printSection("Starting Deployment");
    
    // Run pre-deployment commands
    printSection("Running Pre-Deployment Tasks");
    foreach ($config['pre_commands'] as $command) {
        if (execCommand($command) === false) {
            colorMsg("Deployment aborted due to pre-deployment command failure", COLOR_RED);
            exit(1);
        }
    }
    
    // Build exclude pattern for rsync
    $exclude_patterns = '';
    foreach ($config['exclude_patterns'] as $pattern) {
        $exclude_patterns .= " --exclude='$pattern'";
    }
    
    // Create rsync command
    $rsync_command = sprintf(
        'rsync -avz --delete %s %s %s@%s:%s',
        $exclude_patterns,
        $config['source_dir'] . '/',
        $config['remote_user'],
        $config['remote_host'],
        $config['remote_dir']
    );
    
    // Perform rsync
    printSection("Transferring Files");
    if (execCommand($rsync_command) === false) {
        colorMsg("Deployment aborted due to file transfer failure", COLOR_RED);
        exit(1);
    }
    
    // Run post-deployment commands
    printSection("Running Post-Deployment Tasks");
    foreach ($config['post_commands'] as $command) {
        // Replace template variables
        $command = str_replace('{{remote_dir}}', $config['remote_dir'], $command);
        if (execCommand($command, true) === false) {
            colorMsg("Post-deployment command failed, but deployment was completed", COLOR_YELLOW);
        }
    }
    
    printSection("Deployment Completed Successfully");
}

// Start deployment
deploy();

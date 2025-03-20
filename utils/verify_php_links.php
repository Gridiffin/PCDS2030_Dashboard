<?php
/**
 * Utility script to verify that PHP files use .php extensions in links
 */

// Directory to scan
$dir = '..';

// Directories to skip
$skipDirs = ['utils', 'vendor', 'node_modules', 'assets'];

// File patterns to look for
$pattern = '/href\s*=\s*[\'"]([^"\']*?)\.html[\'"]/i';

// Count of issues found
$issueCount = 0;

// Function to process a single file
function process_file($file, $pattern, &$issueCount) {
    // Read file content
    $content = file_get_contents($file);
    if ($content === false) {
        echo "ERROR: Could not read $file\n";
        return;
    }
    
    // Find matches
    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
    
    if (count($matches) > 0) {
        echo "Issues found in $file:\n";
        foreach ($matches as $match) {
            echo "  Found link to: {$match[1]}.html (should be .php)\n";
            $issueCount++;
        }
    }
}

// Function to scan directory recursively
function scan_directory($dir, $skipDirs, $pattern, &$issueCount) {
    $files = scandir($dir);
    
    foreach ($files as $file) {
        // Skip . and ..
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        // Skip specified directories
        if (in_array($file, $skipDirs) && is_dir($dir . '/' . $file)) {
            continue;
        }
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            // Recurse into subdirectory
            scan_directory($path, $skipDirs, $pattern, $issueCount);
        } else if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            // Process PHP file
            process_file($path, $pattern, $issueCount);
        }
    }
}

// Process all PHP files
scan_directory($dir, $skipDirs, $pattern, $issueCount);

if ($issueCount > 0) {
    echo "\nFound $issueCount issue(s) with .html extensions in PHP files\n";
} else {
    echo "\nNo issues found! All PHP files use .php extensions in links.\n";
}
?>

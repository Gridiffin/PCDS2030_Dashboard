<?php
/**
 * Utility script to update all .html references to .php in JavaScript files
 */

// Directory to scan
$dir = '../js';

// File patterns to look for
$patterns = [
    '/window\.location\.href\s*=\s*[\'"]([^"\']*?)\.html[\'"]/i',
    '/href\s*=\s*[\'"]([^"\']*?)\.html[\'"]/i', 
    '/fetch\s*\(\s*[\'"]([^"\']*?)\.html[\'"]/i'
];

// Replacement patterns
$replacements = [
    'window.location.href = \'$1.php\'',
    'href=\'$1.php\'',
    'fetch(\'$1.php\''
];

// Count of changes made
$changeCount = 0;

// Function to process a single file
function process_file($file, $patterns, $replacements, &$changeCount) {
    echo "Processing $file...\n";
    
    // Read file content
    $content = file_get_contents($file);
    if ($content === false) {
        echo "ERROR: Could not read $file\n";
        return;
    }
    
    // Make replacements
    $newContent = preg_replace($patterns, $replacements, $content, -1, $count);
    $changeCount += $count;
    
    if ($count > 0) {
        // Write changes back
        if (file_put_contents($file, $newContent) !== false) {
            echo "  Made $count replacement(s) in $file\n";
        } else {
            echo "ERROR: Could not write changes to $file\n";
        }
    } else {
        echo "  No changes needed in $file\n";
    }
}

// Function to scan directory recursively
function scan_directory($dir, $patterns, $replacements, &$changeCount) {
    $files = scandir($dir);
    
    foreach ($files as $file) {
        // Skip . and ..
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            // Recurse into subdirectory
            scan_directory($path, $patterns, $replacements, $changeCount);
        } else if (pathinfo($path, PATHINFO_EXTENSION) === 'js') {
            // Process JavaScript file
            process_file($path, $patterns, $replacements, $changeCount);
        }
    }
}

// Process all JavaScript files
scan_directory($dir, $patterns, $replacements, $changeCount);

echo "\nCompleted with $changeCount total replacement(s)\n";
?>

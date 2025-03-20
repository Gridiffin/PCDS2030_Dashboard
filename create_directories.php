<?php
// Create directories for fonts and images if they don't exist
$directories = [
    'assets/fonts',
    'assets/images'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "Created directory: $dir<br>";
        } else {
            echo "Failed to create directory: $dir<br>";
        }
    } else {
        echo "Directory already exists: $dir<br>";
    }
}

echo "Directory setup completed!";
?>

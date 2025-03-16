<?php
/**
 * Database setup script for PCDS2030 Dashboard
 * 
 * Note: You can also set up your database manually through phpMyAdmin:
 * 1. Go to http://localhost/phpmyadmin/
 * 2. Create a new database called "pcds2030_dashboard"
 * 3. Select the database and go to the SQL tab
 * 4. Copy the SQL commands from sql_schema.sql or paste them directly
 * 5. Click "Go" to execute the SQL commands
 */

// Use the centralized database configuration
require_once '../php/config/db_connect.php';

// Check if setup should be skipped (if database was set up manually)
if (isset($_GET['skip']) && $_GET['skip'] == 'true') {
    echo "<p>Database setup skipped. If you've already set up your database through phpMyAdmin, you can now <a href='../index.php'>return to the dashboard</a>.</p>";
    exit;
}

try {
    // Connect to MySQL without selecting a database
    $host = $db_config['host'];
    $username = $db_config['username'];
    $password = $db_config['password'];
    $charset = $db_config['charset'];
    
    $dsn = "mysql:host={$host};charset={$charset}";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Create database if not exists
    $db_name = $db_config['database'];
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '$db_name' created or already exists.<br>";
    
    // Select the database
    $pdo->exec("USE `$db_name`");
    
    // Read and execute SQL from sql_schema.sql
    $sql = file_get_contents(__DIR__ . '/sql_schema.sql');
    $pdo->exec($sql);
    echo "Database tables created successfully.<br>";
    
    echo "<p>Database setup completed! You can now <a href='../index.php'>return to the dashboard</a>.</p>";
    
} catch (PDOException $e) {
    echo "<div style='color: red; margin-bottom: 15px;'>Database Error: " . $e->getMessage() . "</div>";
    echo "<p>You can also set up your database manually:</p>
          <ol>
          <li>Go to <a href='http://localhost/phpmyadmin/' target='_blank'>phpMyAdmin</a></li>
          <li>Create a new database named 'pcds2030_dashboard'</li>
          <li>Select the database and go to the SQL tab</li>
          <li>Open the file at " . __DIR__ . "/sql_schema.sql and copy its contents</li>
          <li>Paste the SQL commands into phpMyAdmin and click 'Go'</li>
          </ol>
          <p>After manual setup, <a href='../index.php'>return to the dashboard</a>.</p>";
}
?>

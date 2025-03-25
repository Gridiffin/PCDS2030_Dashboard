<?php
/**
 * Database connection
 * 
 * Establishes connection to the MySQL database.
 */

require_once dirname(__DIR__) . '/config/config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");
?>

<?php
session_start();

// Include database connection for logging
require_once '../config/db_connect.php';

// Log the logout action if user was authenticated
if (isset($_SESSION['user_id'])) {
    try {
        $conn = getDbConnection();
        if ($conn) {
            $stmt = $conn->prepare('INSERT INTO logs (user_id, action, ip_address) VALUES (?, ?, ?)');
            $stmt->execute([
                $_SESSION['user_id'],
                'logout',
                $_SERVER['REMOTE_ADDR']
            ]);
        }
    } catch (Exception $e) {
        error_log('Logout error: ' . $e->getMessage());
    }
}

// Destroy the session
session_unset();
session_destroy();

// Redirect to login page
header('Location: ../../index.html');
exit;
?>

<?php
// Start the session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Check if user is logged in
function isAuthenticated() {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

// Check if user is admin
function isAdmin() {
    return isAuthenticated() && isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;
}

// Check for session timeout (30 minutes)
function checkSessionTimeout() {
    $timeout = 1800; // 30 minutes in seconds
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        // Session has expired
        session_unset();
        session_destroy();
        return false;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    return true;
}

// Function to redirect if not authenticated
function requireLogin() {
    if (!isAuthenticated() || !checkSessionTimeout()) {
        header('Location: index.html');
        exit;
    }
}

// Function to redirect if not admin
function requireAdmin() {
    if (!isAdmin() || !checkSessionTimeout()) {
        header('Location: index.html');
        exit;
    }
}

/**
 * Check if user credentials are valid
 * @param string $username Username
 * @param string $password Plain text password
 * @return array|bool User data array or false if invalid
 */
function checkCredentials($username, $password) {
    // Log the check attempt
    $logFile = '../../login_attempts.log';
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Checking credentials for: $username\n", FILE_APPEND);
    
    try {
        $conn = getDbConnection();
        if (!$conn) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Database connection failed\n", FILE_APPEND);
            return false;
        }
        
        // Find user by username
        $stmt = $conn->prepare('SELECT UserID, username, password, RoleID, AgencyID FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - No user found with username: $username\n", FILE_APPEND);
            return false;
        }
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - User found, verifying password (hash length: " . 
                         strlen($user['password']) . ")\n", FILE_APPEND);
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Password verified successfully\n", FILE_APPEND);
            
            // Log this login in the logs table
            try {
                $logStmt = $conn->prepare("INSERT INTO logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
                $logStmt->execute([
                    $user['UserID'],
                    'login',
                    'User logged in successfully',
                    $_SERVER['REMOTE_ADDR']
                ]);
            } catch (Exception $e) {
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Failed to log login: " . $e->getMessage() . "\n", FILE_APPEND);
            }
            
            // Return user data for session creation
            return [
                'user_id' => $user['UserID'],
                'username' => $user['username'],
                'role_id' => $user['RoleID'],
                'agency_id' => $user['AgencyID']
            ];
        }
        
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Password verification failed\n", FILE_APPEND);
        return false;
    } catch (PDOException $e) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Database error: " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}
?>

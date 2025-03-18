<?php
// Start session to check admin authentication
// if (session_status() === PHP_SESSION_NONE) {
//     session_start(); 
// }

// For debugging - helpful during development
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

// Include database connection and authentication
require_once '../config/db_connect.php';
require_once '../auth/check_session.php';

// Log request details for debugging
$requestData = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
error_log('Request to manage_users.php: ' . json_encode($requestData));

// Always set content type to JSON before any output
header('Content-Type: application/json');

// Allow for development testing without authentication
$skipAuth = true; // Temporarily set to true for testing

// Ensure the user is an admin
if (!$skipAuth && !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Set default response
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

// Get database connection
$conn = getDbConnection();
if (!$conn) {
    $response['message'] = 'Database connection failed';
    echo json_encode($response);
    exit;
}

// Handle different operations
$operation = $_POST['operation'] ?? ($_GET['operation'] ?? '');
if (!$operation) {
    error_log("No operation received in manage_users.php"); // Added debug log
}

error_log("manage_users.php running, operation: $operation");

switch ($operation) {
    case 'add':
        // Add a new user
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get and validate form data - updated to match users table columns
            $username = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
            $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
            $roleId = filter_input(INPUT_POST, 'roleId', FILTER_VALIDATE_INT);
            $agencyId = filter_input(INPUT_POST, 'agencyId', FILTER_VALIDATE_INT);

            // Debug log the received values
            error_log("Adding user: username=$username, roleId=$roleId, agencyId=$agencyId");
            
            // Basic validation
            if (empty($username) || empty($password) || !$roleId || !$agencyId) {
                $response['message'] = 'Required fields are missing';
                error_log("Validation failed: username=$username, roleId=$roleId, agencyId=$agencyId");
                break;
            }

            // Check if agency exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM agencies WHERE AgencyID = ?");
            $stmt->execute([$agencyId]);
            if ($stmt->fetchColumn() === 0) {
                $response['message'] = 'Selected agency does not exist.';
                error_log("Invalid agency ID: $agencyId");
                break;
            }

            try {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Check if username already exists
                $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetchColumn() > 0) {
                    $response['message'] = 'Username already exists';
                    break;
                }

                // Start transaction
                $conn->beginTransaction();

                // Insert into users table
                $stmt = $conn->prepare("INSERT INTO users (username, password, RoleID, AgencyID) VALUES (?, ?, ?, ?)");
                $success = $stmt->execute([$username, $hashedPassword, $roleId, $agencyId]);
                
                if (!$success) {
                    throw new PDOException("Failed to insert user: " . implode(' ', $stmt->errorInfo()));
                }
                
                $userId = $conn->lastInsertId();
                error_log("User added with ID: $userId");

                // Log the action using a simpler logging approach
                $action = 'create';
                $details = "Created user: $username";
                $userIp = $_SERVER['REMOTE_ADDR'];
                
                // Insert into logs table, handling the case where session user_id might not exist
                $logUserId = $_SESSION['user_id'] ?? null;
                if ($logUserId) {
                    $stmt = $conn->prepare('INSERT INTO logs (user_id, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)');
                    $stmt->execute([
                        $logUserId,
                        $action,
                        'user',
                        $userId,
                        $details,
                        $userIp
                    ]);
                } else {
                    $stmt = $conn->prepare('INSERT INTO logs (action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([
                        $action,
                        'user',
                        $userId,
                        $details,
                        $userIp
                    ]);
                }

                // Commit transaction
                $conn->commit();
                
                $response['success'] = true;
                $response['message'] = 'User added successfully';
                $response['userId'] = $userId;
            } catch (PDOException $e) {
                // Roll back transaction on error
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }
                $response['message'] = 'Error adding user: ' . $e->getMessage();
                error_log('Error adding user: ' . $e->getMessage());
            }
        }
        break;
        
    case 'delete':
        // Delete a user
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = filter_input(INPUT_POST, 'userId', FILTER_VALIDATE_INT);
            
            if (!$userId) {
                $response['message'] = 'Invalid user ID';
                break;
            }
            
            try {
                // Get username for logging
                $stmt = $conn->prepare("SELECT username FROM users WHERE UserID = ?");
                $stmt->execute([$userId]);
                $username = $stmt->fetchColumn();
                
                if (!$username) {
                    $response['message'] = 'User not found';
                    break;
                }
                
                // Start transaction
                $conn->beginTransaction();
                
                // Delete user
                $stmt = $conn->prepare("DELETE FROM users WHERE UserID = ?");
                $stmt->execute([$userId]);
                
                // Log the action
                $stmt = $conn->prepare('INSERT INTO logs (user_id, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([
                    $_SESSION['user_id'],
                    'delete',
                    'user',
                    $userId,
                    "Deleted user: $username",
                    $_SERVER['REMOTE_ADDR']
                ]);
                
                // Commit transaction
                $conn->commit();
                
                $response['success'] = true;
                $response['message'] = 'User deleted successfully';
            } catch (PDOException $e) {
                // Roll back transaction on error
                $conn->rollBack();
                $response['message'] = 'Error deleting user: ' . $e->getMessage();
                error_log('Error deleting user: ' . $e->getMessage());
            }
        }
        break;
        
    case 'get':
        // Get all users
        try {
            // Debug the agencies table structure
            $checkSectors = $conn->query("SELECT COUNT(*) as count, 
                                        SUM(CASE WHEN Sector IS NULL THEN 1 ELSE 0 END) as null_sectors 
                                        FROM agencies");
            $sectorStats = $checkSectors->fetch(PDO::FETCH_ASSOC);
            error_log("Agencies table stats: Total=" . $sectorStats['count'] . ", Null sectors=" . $sectorStats['null_sectors']);
            
            // If there are agencies but all sectors are null, let's update at least one
            if ($sectorStats['count'] > 0 && $sectorStats['null_sectors'] == $sectorStats['count']) {
                error_log("All sectors are NULL, updating Main Agency with Government sector");
                $updateStmt = $conn->prepare("UPDATE agencies SET Sector = 'Government' WHERE AgencyID = 1");
                $updateStmt->execute();
            }
            
            $query = "SELECT u.UserID, u.username, u.RoleID, u.AgencyID,
                      r.RoleName, a.AgencyName, 
                      COALESCE(a.Sector, 'Not Assigned') as SectorName,
                      MAX(l.timestamp) as last_login
                      FROM users u
                      LEFT JOIN roles r ON u.RoleID = r.RoleID
                      LEFT JOIN agencies a ON u.AgencyID = a.AgencyID
                      LEFT JOIN logs l ON u.UserID = l.user_id AND l.action = 'login'
                      GROUP BY u.UserID, u.username, u.RoleID, u.AgencyID, r.RoleName, a.AgencyName, a.Sector
                      ORDER BY u.UserID";
            
            $stmt = $conn->query($query);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug the first user data
            if (!empty($users)) {
                error_log("First user data: " . json_encode($users[0]));
            }
            
            // Process users for display
            foreach ($users as &$user) {
                // Check if user has logged in within the last 30 days
                $lastLogin = strtotime($user['last_login'] ?? '');
                $thirtyDaysAgo = strtotime('-30 days');
                
                if (!$lastLogin) {
                    $user['status'] = 'inactive';
                } else if ($lastLogin > $thirtyDaysAgo) {
                    $user['status'] = 'active';
                } else {
                    $user['status'] = 'inactive';
                }
                
                // Format last login date
                if ($lastLogin) {
                    $user['last_login'] = date('Y-m-d H:i', $lastLogin);
                } else {
                    $user['last_login'] = 'Never';
                }
                
                // Set display name
                if (!empty($user['first_name']) && !empty($user['last_name'])) {
                    $user['display_name'] = $user['first_name'] . ' ' . $user['last_name'];
                } else {
                    $user['display_name'] = $user['username'];
                }
            }
            
            $response['success'] = true;
            $response['data'] = $users;
        } catch (PDOException $e) {
            $response['message'] = 'Error fetching users: ' . $e->getMessage();
            error_log('Error fetching users: ' . $e->getMessage());
        }
        break;
        
    case 'getRoles':
            try {
            $stmt = $conn->query("SELECT RoleID, RoleName FROM roles ORDER BY RoleID");
            $roles = $stmt->fetchAll();
            $response['success'] = true;
            $response['data'] = $roles;
        } catch (PDOException $e) {
            $response['message'] = 'Error fetching roles: ' . $e->getMessage();
        }
        echo json_encode($response);
        exit;

    case 'getAgencies':
        try {
            $stmt = $conn->query("SELECT AgencyID, AgencyName FROM agencies ORDER BY AgencyID");
            $agencies = $stmt->fetchAll();
            $response['success'] = true;
            $response['data'] = $agencies;
        } catch (PDOException $e) {
            $response['message'] = 'Error fetching agencies: ' . $e->getMessage();
        }
        echo json_encode($response);
        exit;
        
    case 'getSectors':
        try {
            error_log("getSectors operation started");
            
            // First check if there are any non-null sectors
            $checkStmt = $conn->query("SELECT COUNT(*) FROM agencies WHERE Sector IS NOT NULL");
            $sectorCount = $checkStmt->fetchColumn();
            
            if ($sectorCount == 0) {
                // No sectors found, let's add a default one
                error_log("No sectors found, adding a default one");
                $updateStmt = $conn->prepare("UPDATE agencies SET Sector = 'Government' WHERE AgencyID = 1");
                $updateStmt->execute();
            }
            
            // Now get the sectors
            $stmt = $conn->query("SELECT Sector as id, Sector as name FROM agencies 
                                  WHERE Sector IS NOT NULL 
                                  GROUP BY Sector 
                                  ORDER BY Sector");
            $sectors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("getSectors found " . count($sectors) . " sectors");
            
            $response['success'] = true;
            $response['data'] = $sectors;
        } catch (PDOException $e) {
            $response['message'] = 'Error fetching sectors: ' . $e->getMessage();
            error_log('Error fetching sectors: ' . $e->getMessage());
        }
        echo json_encode($response);
        exit;

    default:
        $response['message'] = 'Invalid operation';
}

// Set CORS headers if needed for development
header('Access-Control-Allow-Origin: *'); // In production, specify the domain instead of *
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Return JSON response
echo json_encode($response);
exit; // Ensure no additional output after JSON
?>

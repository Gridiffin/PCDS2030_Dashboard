<?php
// Turn on error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'php/config/db_connect.php';

echo "<h1>PCDS2030 JSON Column Fix Tool</h1>";

// Test database connection
echo "<h2>Database Connection Test</h2>";
try {
    $conn = getDbConnection();
    echo "<p style='color:green'>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// Check MySQL version
echo "<h2>Database Version Check</h2>";
try {
    $stmt = $conn->query("SELECT VERSION() as version");
    $version = $stmt->fetch(PDO::FETCH_ASSOC)['version'];
    echo "<p>MySQL Version: {$version}</p>";
    
    // Check if version supports JSON (MySQL 5.7.8+ or MariaDB 10.2.7+)
    $isMySQL = !strpos($version, 'MariaDB');
    $versionNum = preg_replace('/[^\d\.]/', '', $version);
    $parts = explode('.', $versionNum);
    
    $majorVersion = intval($parts[0]);
    $minorVersion = isset($parts[1]) ? intval($parts[1]) : 0;
    $patchVersion = isset($parts[2]) ? intval($parts[2]) : 0;
    
    $supportsJSON = false;
    if ($isMySQL && ($majorVersion > 5 || ($majorVersion == 5 && $minorVersion >= 7 && $patchVersion >= 8))) {
        $supportsJSON = true;
        echo "<p style='color:green'>✓ MySQL version supports native JSON data type</p>";
    } else if (!$isMySQL && ($majorVersion > 10 || ($majorVersion == 10 && $minorVersion >= 2 && $patchVersion >= 7))) {
        $supportsJSON = true;
        echo "<p style='color:green'>✓ MariaDB version supports native JSON data type</p>";
    } else {
        echo "<p style='color:orange'>⚠ Your database version might not support native JSON data type</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error checking database version: " . htmlspecialchars($e->getMessage()) . "</p>";
    $supportsJSON = false;
}

// Check current column definition
echo "<h2>Column Definition Check</h2>";
try {
    $stmt = $conn->query("SHOW COLUMNS FROM Metrics WHERE Field = 'Data'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$column) {
        echo "<p style='color:red'>✗ Data column not found in Metrics table</p>";
        exit;
    }
    
    echo "<p>Current Data column type: {$column['Type']}</p>";
    
    if (strtoupper($column['Type']) === 'JSON') {
        echo "<p style='color:green'>✓ Data column is already of type JSON</p>";
        $needsFix = false;
    } else {
        echo "<p style='color:orange'>⚠ Data column is not defined as JSON type</p>";
        $needsFix = true;
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error checking column definition: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// Fix column if needed and supported
echo "<h2>Column Fix</h2>";

if (!$needsFix) {
    echo "<p>No fix needed - column is already proper type.</p>";
    exit;
}

if (!$supportsJSON) {
    echo "<p style='color:orange'>⚠ Cannot modify column - your database version doesn't support JSON type</p>";
    echo "<p>You can still use the Data column with JSON functions as long as it contains valid JSON strings.</p>";
    echo "<p>For optimal performance, consider upgrading to MySQL 5.7.8+ or MariaDB 10.2.7+</p>";
    exit;
}

echo "<form method='post'>";
echo "<p style='color:red'>⚠ BACKUP YOUR DATABASE BEFORE PROCEEDING ⚠</p>";
echo "<p>This will alter the Data column to use the JSON data type. All current data will be kept.</p>";
echo "<button type='submit' name='fix_column'>Fix Column Type</button>";
echo "</form>";

if (isset($_POST['fix_column'])) {
    try {
        // Backup current data just in case
        $stmt = $conn->query("SELECT MetricID, Data FROM Metrics");
        $backupData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>Backed up " . count($backupData) . " records in memory</p>";
        
        // Alter the column
        $conn->exec("ALTER TABLE Metrics MODIFY COLUMN Data JSON");
        echo "<p style='color:green'>✓ Successfully altered Data column to JSON type</p>";
        
        // Verify change
        $stmt = $conn->query("SHOW COLUMNS FROM Metrics WHERE Field = 'Data'");
        $column = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>New column type: {$column['Type']}</p>";
        
        // Check if all data is still accessible
        $stmt = $conn->query("SELECT COUNT(*) FROM Metrics");
        $count = $stmt->fetchColumn();
        echo "<p>Record count after alteration: $count</p>";
        
        if ($count == count($backupData)) {
            echo "<p style='color:green'>✓ All records preserved</p>";
        } else {
            echo "<p style='color:red'>✗ Record count mismatch! Some data may have been lost.</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Error altering column: " . htmlspecialchars($e->getMessage()) . "</p>";
        
        if (strpos($e->getMessage(), 'Invalid JSON') !== false) {
            echo "<h3>Invalid JSON Data Found</h3>";
            echo "<p>The column could not be converted because some records contain invalid JSON:</p>";
            
            // Try to identify problematic records
            try {
                $stmt = $conn->query("SELECT MetricID, Data FROM Metrics");
                $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<ul>";
                foreach ($records as $record) {
                    json_decode($record['Data']);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        echo "<li>Record ID {$record['MetricID']}: " . json_last_error_msg() . "</li>";
                        echo "<pre>" . htmlspecialchars(substr($record['Data'], 0, 100)) . "...</pre>";
                    }
                }
                echo "</ul>";
                
            } catch (Exception $innerEx) {
                echo "<p>Could not analyze problematic records: " . htmlspecialchars($innerEx->getMessage()) . "</p>";
            }
        }
    }
}
?>

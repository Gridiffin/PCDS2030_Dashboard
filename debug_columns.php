<?php
// Turn on error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once 'php/config/db_connect.php';

echo "<h1>Database Table Structure Debugging Tool</h1>";

try {
    // Get database connection
    $conn = getDbConnection();
    echo "<p style='color:green'>✓ Database connection successful</p>";
    
    // Get list of tables
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Available Tables</h2>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li><a href='#$table'>$table</a></li>";
    }
    echo "</ul>";
    
    // Display each table structure
    foreach ($tables as $table) {
        echo "<h2 id='$table'>$table</h2>";
        
        // Get columns
        $stmt = $conn->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Columns</h3>";
        echo "<table border='1' cellpadding='5' style='border-collapse:collapse'>";
        echo "<thead><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr></thead>";
        echo "<tbody>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>" . (isset($column['Default']) ? $column['Default'] : "NULL") . "</td>";
            echo "<td>{$column['Extra']}</td>";
            echo "</tr>";
        }
        
        echo "</tbody></table>";
        
        // Get sample data (first 5 rows)
        try {
            $stmt = $conn->query("SELECT * FROM $table LIMIT 5");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($rows) > 0) {
                echo "<h3>Sample Data (5 rows)</h3>";
                echo "<table border='1' cellpadding='5' style='border-collapse:collapse'>";
                
                // Table header
                echo "<thead><tr>";
                foreach (array_keys($rows[0]) as $header) {
                    echo "<th>$header</th>";
                }
                echo "</tr></thead>";
                
                // Table data
                echo "<tbody>";
                foreach ($rows as $row) {
                    echo "<tr>";
                    foreach ($row as $key => $value) {
                        // Format JSON data for readability
                        if ($key === 'Data' && json_decode($value) !== null) {
                            // It's valid JSON, display it formatted
                            echo "<td><pre style='max-height:200px;overflow:auto'>" . 
                                htmlspecialchars(json_encode(json_decode($value), JSON_PRETTY_PRINT)) . 
                                "</pre></td>";
                        } else {
                            // Display regular value (truncate if too long)
                            $display = is_string($value) && strlen($value) > 100 ? 
                                htmlspecialchars(substr($value, 0, 100) . '...') : 
                                htmlspecialchars($value ?? 'NULL');
                            echo "<td>$display</td>";
                        }
                    }
                    echo "</tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p>No data in table</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color:red'>Error fetching sample data: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

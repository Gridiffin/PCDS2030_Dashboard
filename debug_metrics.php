<?php
// Turn on error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'php/config/db_connect.php';

echo "<h1>PCDS2030 Metrics Debugging Tool</h1>";

// Test database connection
echo "<h2>Database Connection Test</h2>";
try {
    $conn = getDbConnection();
    echo "<p style='color:green'>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// Check if Metrics table exists
echo "<h2>Metrics Table Check</h2>";
try {
    $stmt = $conn->query("SHOW TABLES LIKE 'Metrics'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green'>✓ Metrics table exists</p>";
        
        // Check table structure
        $stmt = $conn->query("DESCRIBE Metrics");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p>Table columns: " . implode(", ", $columns) . "</p>";
        
        // Check for Data column of type JSON
        $stmt = $conn->query("SHOW COLUMNS FROM Metrics WHERE Field = 'Data'");
        $dataColumn = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dataColumn && strpos(strtoupper($dataColumn['Type']), 'JSON') !== false) {
            echo "<p style='color:green'>✓ Data column has JSON type</p>";
        } else {
            echo "<p style='color:orange'>⚠ Data column may not be JSON type</p>";
        }
        
        // Count existing records
        $stmt = $conn->query("SELECT COUNT(*) FROM Metrics");
        $count = $stmt->fetchColumn();
        
        echo "<p>Current record count: $count</p>";
        
    } else {
        echo "<p style='color:red'>✗ Metrics table does not exist!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Error checking Metrics table: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test submission function
echo "<h2>Test Submission</h2>";
echo "<form method='post'>";
echo "<button type='submit' name='test_submission'>Insert Test Record</button>";
echo "</form>";

if (isset($_POST['test_submission'])) {
    try {
        $testData = [
            'programId' => 'test_prog_' . time(),
            'programName' => 'Test Program',
            'target' => [
                'indicator' => 'Test Indicator',
                'description' => 'Test Description'
            ],
            'status' => [
                'notes' => 'Test Status',
                'date' => date('Y-m-d')
            ],
            'lastUpdated' => date('Y-m-d H:i:s'),
            'status' => 'draft',
            'submittedBy' => 'Debug Tool',
            'userId' => 0
        ];
        
        $stmt = $conn->prepare("INSERT INTO Metrics (MetricType, Data, Quarter, Year, AgencyID) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            'test_metric', 
            json_encode($testData), 
            'Q1', 
            date('Y'), 
            1 // Default agency
        ]);
        
        $testId = $conn->lastInsertId();
        
        echo "<p style='color:green'>✓ Test record inserted successfully with ID: $testId</p>";
        
        // Verify the record was inserted
        $stmt = $conn->prepare("SELECT * FROM Metrics WHERE MetricID = ?");
        $stmt->execute([$testId]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($record) {
            echo "<p>Record verified in database.</p>";
            echo "<pre>" . htmlspecialchars(print_r($record, true)) . "</pre>";
        } else {
            echo "<p style='color:red'>✗ Could not verify inserted record</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Test submission failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>

<?php
require_once '../config/db_connect.php';
require_once '../auth/check_session.php';

// Set content type to JSON
header('Content-Type: application/json');

// Debug logging
error_log("get_custom_metrics_reports.php called");

// Check if user is authenticated
if (!isAuthenticated()) {
    error_log("Authentication failed");
    echo json_encode([
        'success' => false, 
        'message' => 'Authentication required'
    ]);
    exit;
}

// Get the current user's agency ID
$agencyId = $_SESSION['agency_id'];
error_log("Agency ID: " . $agencyId);

// Default response
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    // Get database connection
    $conn = getDbConnection();
    
    // Check for the timestamp column - get all available columns first
    $columns = [];
    try {
        $stmt = $conn->query("DESCRIBE Metrics");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $row['Field'];
        }
        error_log("Available columns in Metrics table: " . implode(", ", $columns));
    } catch (Exception $e) {
        error_log("Failed to get table structure: " . $e->getMessage());
    }
    
    // Determine which timestamp column to use - try common names
    $timestampColumn = null;
    $possibleColumns = ['DateCreated', 'LastUpdated', 'CreateDate', 'Timestamp', 'Created', 'Modified'];
    
    foreach ($possibleColumns as $column) {
        if (in_array($column, $columns)) {
            $timestampColumn = $column;
            error_log("Using timestamp column: " . $timestampColumn);
            break;
        }
    }
    
    // If we couldn't find a timestamp column, just don't include it in the results
    if ($timestampColumn) {
        $stmt = $conn->prepare("
            SELECT MetricID, Data, Quarter, Year, UNIX_TIMESTAMP($timestampColumn) as timestamp 
            FROM Metrics 
            WHERE AgencyID = ? AND MetricType = 'custom_metrics_report'
            ORDER BY Year DESC, Quarter DESC, $timestampColumn DESC
        ");
    } else {
        // Fall back to not using a timestamp in the query
        error_log("No timestamp column found, proceeding without timestamp ordering");
        $stmt = $conn->prepare("
            SELECT MetricID, Data, Quarter, Year
            FROM Metrics 
            WHERE AgencyID = ? AND MetricType = 'custom_metrics_report'
            ORDER BY Year DESC, Quarter DESC
        ");
    }
    
    $stmt->execute([$agencyId]);
    $reports = [];
    $count = 0;
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data = json_decode($row['Data'], true);
        $count++;
        
        // Format report for display - use lastUpdated from JSON if no timestamp column
        $report = [
            'id' => $row['MetricID'],
            'year' => $row['Year'],
            'quarter' => $row['Quarter'],
            'reportDate' => $data['reportDate'] ?? null,
            'isDraft' => $data['isDraft'] ?? false,
            'lastUpdated' => isset($row['timestamp']) ? 
                date('Y-m-d H:i:s', $row['timestamp']) : 
                ($data['lastUpdated'] ?? date('Y-m-d H:i:s'))
        ];
        
        $reports[] = $report;
    }
    
    error_log("Found {$count} reports");
    
    $response = [
        'success' => true,
        'data' => $reports
    ];
    
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Return JSON response
echo json_encode($response);

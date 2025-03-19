<?php
require_once '../php/config/db_connect.php';

try {
    // Get database connection
    $conn = getDbConnection();
    
    // Get the metric IDs
    $timberStmt = $conn->prepare("SELECT MetricID FROM CustomMetrics WHERE MetricKey = 'timber_export_value' AND AgencyID = 1");
    $timberStmt->execute();
    $timberMetricId = $timberStmt->fetchColumn();
    
    $reforestStmt = $conn->prepare("SELECT MetricID FROM CustomMetrics WHERE MetricKey = 'reforested_area' AND AgencyID = 1");
    $reforestStmt->execute();
    $reforestMetricId = $reforestStmt->fetchColumn();
    
    $visitorStmt = $conn->prepare("SELECT MetricID FROM CustomMetrics WHERE MetricKey = 'visitor_count' AND AgencyID = 1");
    $visitorStmt->execute();
    $visitorMetricId = $visitorStmt->fetchColumn();
    
    // Update the JSON data in the Metrics table
    $timberStmt = $conn->prepare("
        UPDATE Metrics 
        SET Data = REPLACE(Data, '@timberMetricId', ?) 
        WHERE MetricTypeID = (SELECT MetricTypeID FROM MetricTypes WHERE TypeKey = 'time_series')
        AND JSON_EXTRACT(Data, '$.metricName') = 'Timber Export Value'
    ");
    $timberStmt->execute([$timberMetricId]);
    
    $reforestStmt = $conn->prepare("
        UPDATE Metrics 
        SET Data = REPLACE(Data, '@reforestMetricId', ?) 
        WHERE MetricTypeID = (SELECT MetricTypeID FROM MetricTypes WHERE TypeKey = 'time_series')
        AND JSON_EXTRACT(Data, '$.metricName') = 'Reforested Area'
    ");
    $reforestStmt->execute([$reforestMetricId]);
    
    $visitorStmt = $conn->prepare("
        UPDATE Metrics 
        SET Data = REPLACE(Data, '@visitorMetricId', ?) 
        WHERE MetricTypeID = (SELECT MetricTypeID FROM MetricTypes WHERE TypeKey = 'time_series')
        AND JSON_EXTRACT(Data, '$.metricName') = 'Visitors to Protected Areas'
    ");
    $visitorStmt->execute([$visitorMetricId]);
    
    echo "Time series mock data updated successfully!";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<?php
/**
 * Helper functions for transitioning from MetricType to MetricTypeID
 */

/**
 * Get a MetricTypeID from a TypeKey
 */
function getMetricTypeIDFromKey($conn, $typeKey) {
    $stmt = $conn->prepare("SELECT MetricTypeID FROM MetricTypes WHERE TypeKey = ?");
    $stmt->execute([$typeKey]);
    return $stmt->fetchColumn();
}

/**
 * Get a TypeKey from a MetricTypeID
 */
function getMetricTypeKeyFromID($conn, $metricTypeID) {
    $stmt = $conn->prepare("SELECT TypeKey FROM MetricTypes WHERE MetricTypeID = ?");
    $stmt->execute([$metricTypeID]);
    return $stmt->fetchColumn();
}

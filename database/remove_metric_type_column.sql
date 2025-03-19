-- Only run this after all code has been migrated to use MetricTypeID!

-- Remove the triggers first
DROP TRIGGER IF EXISTS BeforeInsertMetrics;
DROP TRIGGER IF EXISTS BeforeUpdateMetrics;

-- Remove the redundant MetricType column
ALTER TABLE Metrics DROP COLUMN MetricType;

-- Remove the index if it exists
DROP INDEX IF EXISTS idx_metric_type_string ON Metrics;

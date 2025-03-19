-- Populate the MetricTypes table with existing types found in the system

-- First, empty the table to avoid duplicates on re-run
TRUNCATE TABLE MetricTypes;

-- Insert standard metric types with proper descriptions and display names
INSERT INTO MetricTypes (TypeKey, TypeName, Description, SortOrder, ChartType) VALUES
('governance', 'Governance Reports', 'Program targets and status for governance initiatives', 10, 'bar'),
('Government', 'Government Programs', 'Government program targets and status updates', 15, 'bar'),
('single_custom_metric', 'Single Metric Reports', 'Individual custom metric reports for specific indicators', 20, 'line'),
('custom_metrics_report', 'Multi-Metric Reports', 'Grouped reports containing multiple custom metrics', 30, 'mixed'),
('test_metric', 'Test Reports', 'Test data for system validation and debugging', 100, NULL);

-- Insert sector-specific metric types
INSERT INTO MetricTypes (TypeKey, TypeName, SectorID, Description, SortOrder, ChartType) VALUES
('forestry_report', 'Forestry Reports', 1, 'Reports specific to the forestry sector', 40, 'bar'),
('agriculture_report', 'Agriculture Reports', 2, 'Reports specific to the agriculture sector', 41, 'bar'),
('land_report', 'Land Management Reports', 3, 'Reports specific to land management', 42, 'bar'),
('water_report', 'Water Resources Reports', 4, 'Reports specific to water resources', 43, 'line'),
('biodiversity_report', 'Biodiversity Reports', 5, 'Reports specific to biodiversity conservation', 44, 'line');

-- First, make sure we have the 'time_series' metric type in the MetricTypes table
INSERT INTO MetricTypes (TypeKey, TypeName, Description, SortOrder, ChartType)
SELECT 'time_series', 'Time Series Data', 'Monthly or quarterly time-based data series', 25, 'line'
WHERE NOT EXISTS (SELECT 1 FROM MetricTypes WHERE TypeKey = 'time_series');

-- Create sample metrics for time series data if they don't exist already
INSERT INTO CustomMetrics (AgencyID, MetricName, MetricKey, DataType, Unit, Description, SortOrder)
SELECT 1, 'Timber Export Value', 'timber_export_value', 'currency', 'RM', 'Monthly timber export values in Malaysian Ringgit', 1
WHERE NOT EXISTS (SELECT 1 FROM CustomMetrics WHERE MetricKey = 'timber_export_value' AND AgencyID = 1);

INSERT INTO CustomMetrics (AgencyID, MetricName, MetricKey, DataType, Unit, Description, SortOrder)
SELECT 1, 'Reforested Area', 'reforested_area', 'number', 'hectares', 'Monthly reforestation efforts in hectares', 2
WHERE NOT EXISTS (SELECT 1 FROM CustomMetrics WHERE MetricKey = 'reforested_area' AND AgencyID = 1);

INSERT INTO CustomMetrics (AgencyID, MetricName, MetricKey, DataType, Unit, Description, SortOrder)
SELECT 1, 'Visitors to Protected Areas', 'visitor_count', 'number', 'visitors', 'Monthly count of visitors to protected forest areas', 3
WHERE NOT EXISTS (SELECT 1 FROM CustomMetrics WHERE MetricKey = 'visitor_count' AND AgencyID = 1);

-- Get the time_series metric type ID
SET @timeSeriesTypeId = (SELECT MetricTypeID FROM MetricTypes WHERE TypeKey = 'time_series');

-- Get IDs of our sample metrics
SET @timberMetricId = (SELECT MetricID FROM CustomMetrics WHERE MetricKey = 'timber_export_value' AND AgencyID = 1);
SET @reforestMetricId = (SELECT MetricID FROM CustomMetrics WHERE MetricKey = 'reforested_area' AND AgencyID = 1);
SET @visitorMetricId = (SELECT MetricID FROM CustomMetrics WHERE MetricKey = 'visitor_count' AND AgencyID = 1);

-- Insert sample time series data for Timber Export Value for 2024
INSERT INTO Metrics (AgencyID, MetricTypeID, Data, Year, Quarter)
VALUES (1, @timeSeriesTypeId,
'{
  "metricId": "@timberMetricId",
  "metricName": "Timber Export Value",
  "unit": "RM",
  "year": "2024",
  "monthlyData": {
    "jan": {"value": 1245000, "notes": "Increased exports to China"},
    "feb": {"value": 1320000, "notes": "New trade agreement effective"},
    "mar": {"value": 1450000, "notes": "End of quarter push"},
    "apr": {"value": 1380000, "notes": "Slight decrease from March peak"},
    "may": {"value": 1425000, "notes": "Recovery from April"},
    "jun": {"value": 1510000, "notes": "Mid-year peak performance"},
    "jul": {"value": 1475000, "notes": ""},
    "aug": {"value": 1490000, "notes": "Small growth from July"},
    "sep": {"value": 1560000, "notes": "Record month for Q3"},
    "oct": {"value": 1495000, "notes": ""},
    "nov": {"value": 1530000, "notes": "Pre-holiday increase"},
    "dec": {"value": 1620000, "notes": "End of year peak"}
  },
  "annualNotes": "Overall strong performance with consistent growth throughout the year. The implementation of new sustainable forestry practices has not negatively impacted export volumes.",
  "isDraft": false,
  "lastUpdated": "2024-12-31 15:30:00",
  "submittedBy": "admin",
  "userId": 1
}', '2024', 'All');

-- Insert sample time series data for Reforested Area for 2024
INSERT INTO Metrics (AgencyID, MetricTypeID, Data, Year, Quarter)
VALUES (1, @timeSeriesTypeId,
'{
  "metricId": "@reforestMetricId",
  "metricName": "Reforested Area",
  "unit": "hectares",
  "year": "2024",
  "monthlyData": {
    "jan": {"value": 120, "notes": "Focus on northern region"},
    "feb": {"value": 85, "notes": "Heavy rainfall limited work days"},
    "mar": {"value": 145, "notes": "Expanded to eastern region"},
    "apr": {"value": 210, "notes": "Spring planting campaign"},
    "may": {"value": 230, "notes": "Peak planting season"},
    "jun": {"value": 180, "notes": ""},
    "jul": {"value": 90, "notes": "Dry season limited activities"},
    "aug": {"value": 75, "notes": "Continued dry conditions"},
    "sep": {"value": 140, "notes": "Return to normal conditions"},
    "oct": {"value": 160, "notes": "Fall planting program started"},
    "nov": {"value": 190, "notes": ""},
    "dec": {"value": 110, "notes": "Holiday season reduced activity"}
  },
  "annualNotes": "Successfully reforested 1,735 hectares this year, exceeding the annual target of 1,500 hectares. The spring and fall planting campaigns were particularly successful.",
  "isDraft": false,
  "lastUpdated": "2024-12-28 10:15:00",
  "submittedBy": "admin",
  "userId": 1
}', '2024', 'All');

-- Insert sample time series data for Visitors for 2024 (as a draft)
INSERT INTO Metrics (AgencyID, MetricTypeID, Data, Year, Quarter)
VALUES (1, @timeSeriesTypeId,
'{
  "metricId": "@visitorMetricId",
  "metricName": "Visitors to Protected Areas",
  "unit": "visitors",
  "year": "2024",
  "monthlyData": {
    "jan": {"value": 5200, "notes": "Winter holiday visitors"},
    "feb": {"value": 4100, "notes": "Lower season begins"},
    "mar": {"value": 6300, "notes": "Spring break increase"},
    "apr": {"value": 7800, "notes": "Easter holiday weekend peak"},
    "may": {"value": 8500, "notes": "Beginning of summer season"},
    "jun": {"value": 12400, "notes": "School holiday peak"},
    "jul": {"value": 15600, "notes": "Peak summer season"},
    "aug": {"value": 14900, "notes": "Continued high summer traffic"},
    "sep": {"value": 9200, "notes": "Back to school reduction"},
    "oct": {"value": 7400, "notes": "Fall foliage viewers"}
  },
  "annualNotes": "Data for November and December not yet available. Overall visitor numbers are up 12% from previous year, indicating successful tourism promotion campaigns.",
  "isDraft": true,
  "lastUpdated": "2024-10-31 16:45:00",
  "submittedBy": "admin",
  "userId": 1
}', '2024', 'All');

-- Insert sample time series data for Timber Export Value for 2023 (for year-over-year comparison)
INSERT INTO Metrics (AgencyID, MetricTypeID, Data, Year, Quarter)
VALUES (1, @timeSeriesTypeId,
'{
  "metricId": "@timberMetricId",
  "metricName": "Timber Export Value",
  "unit": "RM",
  "year": "2023",
  "monthlyData": {
    "jan": {"value": 1050000, "notes": ""},
    "feb": {"value": 1120000, "notes": ""},
    "mar": {"value": 1180000, "notes": ""},
    "apr": {"value": 1220000, "notes": ""},
    "may": {"value": 1260000, "notes": ""},
    "jun": {"value": 1240000, "notes": ""},
    "jul": {"value": 1270000, "notes": ""},
    "aug": {"value": 1310000, "notes": ""},
    "sep": {"value": 1340000, "notes": ""},
    "oct": {"value": 1290000, "notes": ""},
    "nov": {"value": 1320000, "notes": ""},
    "dec": {"value": 1380000, "notes": ""}
  },
  "annualNotes": "Consistent growth throughout 2023 with total exports reaching 14.98M RM, a 5.8% increase over 2022.",
  "isDraft": false,
  "lastUpdated": "2023-12-31 12:00:00",
  "submittedBy": "admin",
  "userId": 1
}', '2023', 'All');

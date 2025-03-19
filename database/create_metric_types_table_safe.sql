-- Create MetricTypes table for organizing report formats
CREATE TABLE IF NOT EXISTS MetricTypes (
    MetricTypeID int(10) AUTO_INCREMENT PRIMARY KEY,
    TypeKey varchar(50) NOT NULL UNIQUE,  -- Maps to existing values in Metrics.MetricType
    TypeName varchar(100) NOT NULL,       -- Display name
    SectorID int(10) NULL,                -- Optional sector relationship
    Description text NULL,
    ChartType varchar(50) NULL,           -- For PPTX generation: 'bar', 'line', etc.
    SlideTemplate varchar(100) NULL,      -- Template to use for this metric type
    SortOrder int(3) DEFAULT 0,
    FOREIGN KEY (SectorID) REFERENCES Sectors(SectorID) ON DELETE SET NULL
);

-- Create index for better performance
CREATE INDEX idx_metric_type_sector ON MetricTypes(SectorID);

-- Add MetricTypeID column to Metrics table without constraints initially
ALTER TABLE Metrics ADD COLUMN IF NOT EXISTS MetricTypeID int(10) NULL;

-- Create an index on the existing MetricType string column for better join performance
ALTER TABLE Metrics ADD INDEX idx_metric_type_string (MetricType(20));

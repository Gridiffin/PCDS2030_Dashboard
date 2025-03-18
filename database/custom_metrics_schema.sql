CREATE TABLE CustomMetrics (
    MetricID int(10) AUTO_INCREMENT PRIMARY KEY,
    AgencyID int(10),
    SectorID varchar(255),
    MetricName varchar(255) NOT NULL,
    MetricKey varchar(100) NOT NULL,
    DataType varchar(50) NOT NULL, -- 'number', 'text', 'currency', 'date', etc.
    Unit varchar(50),
    IsRequired boolean DEFAULT false,
    Description text,
    SortOrder int(3) DEFAULT 0,
    DateCreated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (AgencyID) REFERENCES agencies(AgencyID) ON DELETE CASCADE
);

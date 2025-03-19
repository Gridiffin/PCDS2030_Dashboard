-- Step 1: Create the Sectors table if not exists
CREATE TABLE IF NOT EXISTS Sectors (
    SectorID int(10) AUTO_INCREMENT PRIMARY KEY,
    SectorName varchar(100) NOT NULL,
    Description text,
    SortOrder int(3) DEFAULT 0,
    DateCreated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Step 2: Add initial sector data if the table is empty
INSERT INTO Sectors (SectorName, Description, SortOrder)
SELECT * FROM (
    SELECT 'Forestry' AS name, 'Forest management and timber industry' AS description, 1 AS sort,
    UNION SELECT 'Agriculture', 'Agricultural activities and farming', 2
    UNION SELECT 'Land Management', 'Land use planning and management', 3
    UNION SELECT 'Water Resources', 'Water conservation and management', 4
    UNION SELECT 'Biodiversity', 'Protection of species and ecosystems', 5
) AS temp
WHERE NOT EXISTS (SELECT 1 FROM Sectors LIMIT 1);

-- Step 3: Handle the CustomMetrics table

-- Check if CustomMetrics has a Sector column (string) that needs conversion
SET @columnExists = 0;
SELECT COUNT(*) INTO @columnExists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'CustomMetrics' 
AND COLUMN_NAME = 'Sector' 
AND DATA_TYPE = 'varchar';

-- If Sector exists as varchar, we need to:
-- 1. Create a SectorID column
-- 2. Set appropriate values based on existing Sector strings
-- 3. Drop the old Sector column
SET @alterCustomMetrics = CONCAT(
    'ALTER TABLE CustomMetrics ',
    CASE WHEN @columnExists > 0 THEN
        'ADD COLUMN SectorID int(10) NULL, '
    ELSE '' END
);

-- Only execute if we need to make changes
SET @alterCustomMetrics = NULLIF(@alterCustomMetrics, 'ALTER TABLE CustomMetrics ');
IF @alterCustomMetrics IS NOT NULL THEN
    PREPARE stmt FROM @alterCustomMetrics;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END IF;

-- If we've added SectorID column to CustomMetrics, update values
SET @updateCmd = CONCAT(
    CASE WHEN @columnExists > 0 THEN
        'UPDATE CustomMetrics cm 
        INNER JOIN Sectors s ON cm.Sector LIKE CONCAT("%", s.SectorName, "%") 
        SET cm.SectorID = s.SectorID;'
    ELSE '' END
);

-- Execute the update if needed
IF @updateCmd <> '' THEN
    PREPARE stmt FROM @updateCmd;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END IF;

-- If we've migrated data, drop the old Sector column
SET @dropCmd = CONCAT(
    CASE WHEN @columnExists > 0 THEN
        'ALTER TABLE CustomMetrics DROP COLUMN Sector;'
    ELSE '' END
);

-- Execute the drop if needed
IF @dropCmd <> '' THEN
    PREPARE stmt FROM @dropCmd;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END IF;

-- Step 4: Handle the Agencies table

-- Add SectorID to Agencies table if it doesn't exist already
SET @agencySectorExists = 0;
SELECT COUNT(*) INTO @agencySectorExists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'Agencies' 
AND COLUMN_NAME = 'SectorID';

-- Add SectorID column if needed
SET @alterAgencies = CONCAT(
    'ALTER TABLE Agencies ',
    CASE WHEN @agencySectorExists = 0 THEN
        'ADD COLUMN SectorID int(10) NULL'
    ELSE '' END
);

-- Only execute if we need to make changes
SET @alterAgencies = NULLIF(@alterAgencies, 'ALTER TABLE Agencies ');
IF @alterAgencies IS NOT NULL THEN
    PREPARE stmt FROM @alterAgencies;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END IF;

-- Step 5: Add foreign key constraints 

-- Add foreign key to CustomMetrics if not exists
SET @fkCustomMetricsExists = 0;
SELECT COUNT(*) INTO @fkCustomMetricsExists 
FROM information_schema.TABLE_CONSTRAINTS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'CustomMetrics' 
AND CONSTRAINT_NAME = 'fk_sector_id';

SET @addFkCustomMetrics = CONCAT(
    'ALTER TABLE CustomMetrics ',
    CASE WHEN @fkCustomMetricsExists = 0 THEN
        'ADD CONSTRAINT fk_sector_id FOREIGN KEY (SectorID) REFERENCES Sectors(SectorID) ON DELETE SET NULL'
    ELSE '' END
);

-- Only execute if we need to make changes
SET @addFkCustomMetrics = NULLIF(@addFkCustomMetrics, 'ALTER TABLE CustomMetrics ');
IF @addFkCustomMetrics IS NOT NULL THEN
    PREPARE stmt FROM @addFkCustomMetrics;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END IF;

-- Add foreign key to Agencies if not exists
SET @fkAgenciesExists = 0;
SELECT COUNT(*) INTO @fkAgenciesExists 
FROM information_schema.TABLE_CONSTRAINTS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'Agencies' 
AND CONSTRAINT_NAME = 'fk_agency_sector';

SET @addFkAgencies = CONCAT(
    'ALTER TABLE Agencies ',
    CASE WHEN @fkAgenciesExists = 0 THEN
        'ADD CONSTRAINT fk_agency_sector FOREIGN KEY (SectorID) REFERENCES Sectors(SectorID) ON DELETE SET NULL'
    ELSE '' END
);

-- Only execute if we need to make changes
SET @addFkAgencies = NULLIF(@addFkAgencies, 'ALTER TABLE Agencies ');
IF @addFkAgencies IS NOT NULL THEN
    PREPARE stmt FROM @addFkAgencies;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END IF;

-- Step 6: Create helpful indexes for better performance
CREATE INDEX IF NOT EXISTS idx_agency_sector ON Agencies(SectorID);
CREATE INDEX IF NOT EXISTS idx_custom_metrics_sector ON CustomMetrics(SectorID);

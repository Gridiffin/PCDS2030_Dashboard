-- Script to safely migrate from MetricType string to MetricTypeID foreign key

-- Step 1: First make sure all existing rows have proper MetricTypeID values
-- Run the sync procedure again to ensure all records are properly linked
CALL SyncMetricTypeIDs();

-- Step 2: Add a reverse trigger that keeps MetricType updated based on MetricTypeID
-- This provides backward compatibility during the transition period
DELIMITER //
CREATE TRIGGER IF NOT EXISTS BeforeUpdateMetrics
BEFORE UPDATE ON Metrics
FOR EACH ROW
BEGIN
    -- If MetricTypeID was changed but MetricType wasn't, update MetricType
    IF NEW.MetricTypeID IS NOT NULL AND 
       (NEW.MetricTypeID != OLD.MetricTypeID OR OLD.MetricTypeID IS NULL) AND
       (NEW.MetricType = OLD.MetricType OR NEW.MetricType IS NULL)
    THEN
        SELECT TypeKey INTO @typeKey 
        FROM MetricTypes 
        WHERE MetricTypeID = NEW.MetricTypeID
        LIMIT 1;
        
        IF @typeKey IS NOT NULL THEN
            SET NEW.MetricType = @typeKey;
        END IF;
    END IF;
END //
DELIMITER ;

-- Step 3: Update both triggers to handle NULL cases properly
DROP TRIGGER IF EXISTS BeforeInsertMetrics;

DELIMITER //
CREATE TRIGGER BeforeInsertMetrics
BEFORE INSERT ON Metrics
FOR EACH ROW
BEGIN
    -- If only one of the two fields is provided, fill in the other
    IF NEW.MetricType IS NOT NULL AND NEW.MetricTypeID IS NULL THEN
        -- MetricType provided but not MetricTypeID
        SELECT MetricTypeID INTO @typeid 
        FROM MetricTypes 
        WHERE TypeKey = NEW.MetricType
        LIMIT 1;
        
        IF @typeid IS NOT NULL THEN
            SET NEW.MetricTypeID = @typeid;
        END IF;
    ELSEIF NEW.MetricType IS NULL AND NEW.MetricTypeID IS NOT NULL THEN
        -- MetricTypeID provided but not MetricType
        SELECT TypeKey INTO @typeKey 
        FROM MetricTypes 
        WHERE MetricTypeID = NEW.MetricTypeID
        LIMIT 1;
        
        IF @typeKey IS NOT NULL THEN
            SET NEW.MetricType = @typeKey;
        END IF;
    END IF;
END //
DELIMITER ;

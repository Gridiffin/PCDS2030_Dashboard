-- Set up procedure and triggers to keep MetricTypeID in sync with MetricType

-- Create procedure to update MetricTypeID based on MetricType string
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS SyncMetricTypeIDs()
BEGIN
    UPDATE Metrics m
    JOIN MetricTypes mt ON m.MetricType = mt.TypeKey
    SET m.MetricTypeID = mt.MetricTypeID
    WHERE m.MetricTypeID IS NULL;
    
    -- Handle unknown metric types by creating them
    INSERT INTO MetricTypes (TypeKey, TypeName, Description, SortOrder)
    SELECT DISTINCT m.MetricType, 
           CONCAT('Auto: ', m.MetricType), 
           'Automatically added from existing data', 
           200
    FROM Metrics m
    LEFT JOIN MetricTypes mt ON m.MetricType = mt.TypeKey
    WHERE mt.MetricTypeID IS NULL
    AND m.MetricType IS NOT NULL;
    
    -- Update any remaining unlinked metrics
    UPDATE Metrics m
    JOIN MetricTypes mt ON m.MetricType = mt.TypeKey
    SET m.MetricTypeID = mt.MetricTypeID
    WHERE m.MetricTypeID IS NULL;
END //
DELIMITER ;

-- Create trigger to handle new entries
DELIMITER //
CREATE TRIGGER IF NOT EXISTS BeforeInsertMetrics
BEFORE INSERT ON Metrics
FOR EACH ROW
BEGIN
    IF NEW.MetricType IS NOT NULL AND NEW.MetricTypeID IS NULL THEN
        SELECT MetricTypeID INTO @typeid 
        FROM MetricTypes 
        WHERE TypeKey = NEW.MetricType
        LIMIT 1;
        
        IF @typeid IS NOT NULL THEN
            SET NEW.MetricTypeID = @typeid;
        END IF;
    END IF;
END //
DELIMITER ;

-- Run the procedure to set initial values
CALL SyncMetricTypeIDs();

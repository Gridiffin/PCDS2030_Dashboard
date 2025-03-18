<?php
require_once '../config/db_connect.php';
require_once '../auth/check_session.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is authenticated
if (!isAuthenticated()) {
    echo json_encode([
        'success' => false, 
        'message' => 'Authentication required'
    ]);
    exit;
}

// Get the current user's agency ID
$agencyId = $_SESSION['agency_id'];

// Default response
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

// Get database connection
$conn = getDbConnection();

// Get operation type
$operation = $_POST['operation'] ?? ($_GET['operation'] ?? '');

switch ($operation) {
    case 'getMetrics':
        // Get all custom metrics for this agency
        try {
            $stmt = $conn->prepare("SELECT * FROM CustomMetrics WHERE AgencyID = ? ORDER BY SortOrder, MetricName");
            $stmt->execute([$agencyId]);
            $metrics = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response = [
                'success' => true,
                'data' => $metrics
            ];
        } catch (Exception $e) {
            $response['message'] = 'Error fetching metrics: ' . $e->getMessage();
        }
        break;
        
    case 'addMetric':
        // Add a new custom metric
        try {
            // Get metric data from POST
            $metricName = $_POST['metricName'] ?? '';
            $metricKey = createSlug($metricName); // Convert name to slug for storing as key
            $dataType = $_POST['dataType'] ?? '';
            $unit = $_POST['unit'] ?? '';
            $isRequired = isset($_POST['isRequired']) ? (bool)$_POST['isRequired'] : false;
            $description = $_POST['description'] ?? '';
            $sectorId = $_SESSION['sector_id'] ?? null;
            
            // Validate required fields
            if (empty($metricName) || empty($dataType)) {
                throw new Exception('Name and data type are required');
            }
            
            // Insert the new metric
            $stmt = $conn->prepare("INSERT INTO CustomMetrics 
                (AgencyID, SectorID, MetricName, MetricKey, DataType, Unit, IsRequired, Description) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                
            $stmt->execute([
                $agencyId,
                $sectorId,
                $metricName,
                $metricKey,
                $dataType,
                $unit,
                $isRequired ? 1 : 0,
                $description
            ]);
            
            $metricId = $conn->lastInsertId();
            
            $response = [
                'success' => true,
                'message' => 'Metric added successfully',
                'data' => [
                    'id' => $metricId
                ]
            ];
        } catch (Exception $e) {
            $response['message'] = 'Error adding metric: ' . $e->getMessage();
        }
        break;
        
    case 'updateMetric':
        // Update an existing custom metric
        try {
            // Get metric data from POST
            $metricId = $_POST['metricId'] ?? 0;
            $metricName = $_POST['metricName'] ?? '';
            $dataType = $_POST['dataType'] ?? '';
            $unit = $_POST['unit'] ?? '';
            $isRequired = isset($_POST['isRequired']) ? (bool)$_POST['isRequired'] : false;
            $description = $_POST['description'] ?? '';
            
            // Validate required fields
            if (!$metricId || empty($metricName) || empty($dataType)) {
                throw new Exception('ID, name and data type are required');
            }
            
            // Update the metric
            $stmt = $conn->prepare("UPDATE CustomMetrics 
                SET MetricName = ?, DataType = ?, Unit = ?, IsRequired = ?, Description = ? 
                WHERE MetricID = ? AND AgencyID = ?");
                
            $stmt->execute([
                $metricName,
                $dataType,
                $unit,
                $isRequired ? 1 : 0,
                $description,
                $metricId,
                $agencyId
            ]);
            
            if ($stmt->rowCount() === 0) {
                throw new Exception('Metric not found or you do not have permission to update it');
            }
            
            $response = [
                'success' => true,
                'message' => 'Metric updated successfully'
            ];
        } catch (Exception $e) {
            $response['message'] = 'Error updating metric: ' . $e->getMessage();
        }
        break;
        
    case 'deleteMetric':
        // Delete a custom metric
        try {
            $metricId = $_POST['metricId'] ?? 0;
            
            if (!$metricId) {
                throw new Exception('Metric ID is required');
            }
            
            // Delete the metric
            $stmt = $conn->prepare("DELETE FROM CustomMetrics WHERE MetricID = ? AND AgencyID = ?");
            $stmt->execute([$metricId, $agencyId]);
            
            if ($stmt->rowCount() === 0) {
                throw new Exception('Metric not found or you do not have permission to delete it');
            }
            
            $response = [
                'success' => true,
                'message' => 'Metric deleted successfully'
            ];
        } catch (Exception $e) {
            $response['message'] = 'Error deleting metric: ' . $e->getMessage();
        }
        break;
        
    default:
        $response['message'] = 'Invalid operation';
}

// Helper function to create a slug from a string
function createSlug($text) {
    // Remove special characters
    $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
    // Replace spaces with underscores
    $text = preg_replace('/\s+/u', '_', $text);
    // Convert to lowercase
    $text = mb_strtolower($text, 'UTF-8');
    // Limit length and remove trailing underscores
    $text = trim(substr($text, 0, 50), '_');
    return $text;
}

// Return JSON response
echo json_encode($response);

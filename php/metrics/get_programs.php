<?php
// Start session to check authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection and authentication
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

// Default response
$response = [
    'success' => false,
    'message' => 'An error occurred',
    'data' => null
];

try {
    // Get database connection
    $conn = getDbConnection();
    
    // Get agency ID (either from parameter or session)
    $agencyId = isset($_GET['agencyId']) ? $_GET['agencyId'] : $_SESSION['agency_id'];
    
    // In a real implementation, there would be a Programs table
    // For now, we'll extract program info from the Metrics table
    $sql = "
        SELECT DISTINCT 
            JSON_EXTRACT(Data, '$.programId') as programId,
            JSON_EXTRACT(Data, '$.programName') as programName
        FROM 
            Metrics
        WHERE 
            AgencyID = ? AND
            JSON_EXTRACT(Data, '$.status') != 'draft'
        ORDER BY 
            JSON_EXTRACT(Data, '$.programName')
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$agencyId]);
    
    $programs = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Clean up JSON_EXTRACT result (removes quotes)
        $programId = str_replace('"', '', $row['programId']);
        $programName = str_replace('"', '', $row['programName']);
        
        if (!empty($programName)) {
            $programs[] = [
                'id' => $programId,
                'name' => $programName
            ];
        }
    }
    
    // If no programs found, provide some example programs for testing
    if (empty($programs)) {
        switch ($agencyId) {
            case 1: // Main Agency
                $programs = [
                    ['id' => 'p1', 'name' => 'Economic Policy Development'],
                    ['id' => 'p2', 'name' => 'Governance Framework']
                ];
                break;
            case 2: // Forestry Department
                $programs = [
                    ['id' => 'p1', 'name' => 'Reforestation Initiative'],
                    ['id' => 'p2', 'name' => 'Wildlife Conservation'],
                    ['id' => 'p3', 'name' => 'Sustainable Forestry Practices']
                ];
                break;
            case 3: // Water Resources Department
                $programs = [
                    ['id' => 'p1', 'name' => 'Watershed Management'],
                    ['id' => 'p2', 'name' => 'Water Quality Monitoring']
                ];
                break;
            default:
                $programs = [
                    ['id' => 'p1', 'name' => 'Sample Program 1'],
                    ['id' => 'p2', 'name' => 'Sample Program 2']
                ];
                break;
        }
    }
    
    // Get drafts for this agency
    $draftsSql = "
        SELECT 
            m.MetricID as id, 
            JSON_EXTRACT(Data, '$.programName') as programName
        FROM 
            Metrics m
        WHERE 
            m.AgencyID = ? AND
            JSON_EXTRACT(m.Data, '$.status') = 'draft'
        ORDER BY 
            m.Year DESC, 
            m.Quarter DESC
    ";
    
    $draftsStmt = $conn->prepare($draftsSql);
    $draftsStmt->execute([$agencyId]);
    
    $drafts = [];
    while ($row = $draftsStmt->fetch(PDO::FETCH_ASSOC)) {
        // Clean up JSON_EXTRACT result (removes quotes)
        $programName = str_replace('"', '', $row['programName']);
        
        if (!empty($programName)) {
            $drafts[] = [
                'id' => $row['id'],
                'programName' => $programName
            ];
        }
    }
    
    $response = [
        'success' => true,
        'data' => $programs,
        'drafts' => $drafts
    ];
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error in get_programs.php: ' . $e->getMessage());
}

echo json_encode($response);
?>

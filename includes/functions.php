<?php
/**
 * Common functions used throughout the application
 */

/**
 * Sanitize user input
 * @param string $data Input to sanitize
 * @return string Sanitized input
 */
function sanitize_input($data) {
    // Sanitization logic would go here
    return htmlspecialchars(trim($data));
}

/**
 * Generate random string
 * @param int $length Length of string
 * @return string Random string
 */
function generate_random_string($length = 10) {
    // Random string generation logic would go here
    return ''; // Placeholder
}

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Output format
 * @return string Formatted date
 */
function format_date($date, $format = 'Y-m-d') {
    // Date formatting logic would go here
    return ''; // Placeholder
}

/**
 * Automatically manage reporting periods based on calendar quarters
 * 
 * This function:
 * 1. Ensures all quarters for current year exist in the database
 * 2. Sets correct open/closed status based on current date
 * 
 * @return bool True if successfully managed periods
 */
function auto_manage_reporting_periods() {
    global $conn;
    
    // Define the quarters with their start and end dates
    $current_year = date('Y');
    $quarters = [
        1 => [
            'start' => "$current_year-01-01",
            'end' => "$current_year-03-31"
        ],
        2 => [
            'start' => "$current_year-04-01",
            'end' => "$current_year-06-30"
        ],
        3 => [
            'start' => "$current_year-07-01",
            'end' => "$current_year-09-30"
        ],
        4 => [
            'start' => "$current_year-10-01",
            'end' => "$current_year-12-31"
        ]
    ];
    
    // Create next year's Q1 if we're in Q4
    $current_month = date('n');
    if ($current_month >= 10) {
        $next_year = $current_year + 1;
        $quarters[5] = [
            'quarter' => 1,
            'year' => $next_year,
            'start' => "$next_year-01-01",
            'end' => "$next_year-03-31"
        ];
    }
    
    // Get current date for comparison
    $today = date('Y-m-d');
    $current_quarter = ceil($current_month / 3);
    
    // Check and create missing periods
    foreach ($quarters as $quarter_num => $dates) {
        // Handle next year's Q1 special case
        if (isset($dates['quarter'])) {
            $q = $dates['quarter'];
            $y = $dates['year'];
        } else {
            $q = $quarter_num;
            $y = $current_year;
        }
        
        // Check if this period exists
        $query = "SELECT period_id, status FROM reporting_periods WHERE year = ? AND quarter = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $y, $q);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Period doesn't exist, create it
            $start = $dates['start'];
            $end = $dates['end'];
            
            // Determine if this should be open
            $status = ($q == $current_quarter && $y == $current_year) ? 'open' : 'closed';
            
            // Create the period
            $insert = "INSERT INTO reporting_periods (year, quarter, start_date, end_date, status) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert);
            $stmt->bind_param("iisss", $y, $q, $start, $end, $status);
            $stmt->execute();
        } else {
            // Period exists, update its status based on current date
            $period = $result->fetch_assoc();
            $period_id = $period['period_id'];
            
            // Current quarter should be open, others closed
            $should_be_open = ($q == $current_quarter && $y == $current_year);
            $current_status = $period['status'];
            
            if (($should_be_open && $current_status != 'open') || (!$should_be_open && $current_status != 'closed')) {
                $new_status = $should_be_open ? 'open' : 'closed';
                $update = "UPDATE reporting_periods SET status = ? WHERE period_id = ?";
                $stmt = $conn->prepare($update);
                $stmt->bind_param("si", $new_status, $period_id);
                $stmt->execute();
            }
        }
    }
    
    return true;
}

/**
 * Get current reporting period
 * @return array|null Current active reporting period or null if none
 */
function get_current_reporting_period() {
    global $conn;
    
    $query = "SELECT * FROM reporting_periods WHERE status = 'open' ORDER BY year DESC, quarter DESC LIMIT 1";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get all reporting periods
 * @return array List of all reporting periods
 */
function get_all_reporting_periods() {
    global $conn;
    
    $query = "SELECT * FROM reporting_periods ORDER BY year DESC, quarter DESC";
    $result = $conn->query($query);
    
    $periods = [];
    while ($row = $result->fetch_assoc()) {
        $periods[] = $row;
    }
    
    return $periods;
}

/**
 * Get a specific reporting period by ID
 * 
 * @param int $period_id The ID of the reporting period to retrieve
 * @return array|null The reporting period data or null if not found
 */
function get_reporting_period($period_id) {
    global $conn;
    
    $query = "SELECT * FROM reporting_periods WHERE period_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}
?>

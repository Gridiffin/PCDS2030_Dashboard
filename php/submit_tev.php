<?php
// filepath: c:\Users\gridi\OneDrive\Desktop\edu\intern\sfc\project\pcds2030_dashboard\submit_tev.php
// This file is unused - its corresponding form (submit_tev.html) isn't linked from main navigation
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pcds2030_dashboard";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $month = $_POST['month'];
    $year = $_POST['year'];
    $tev = $_POST['tev'];
    $agencyID = 1; // Assuming the agency ID for SFC is 1

    // Validate input
    if (!empty($month) && !empty($year) && !empty($tev)) {
        $stmt = $conn->prepare("INSERT INTO Metrics (MetricType, Data, Quarter, Year, AgencyID) VALUES (?, ?, ?, ?, ?)");
        $metricType = "Timber Export Value";
        $quarter = ceil(date('n', strtotime($month)) / 3); // Calculate quarter from month
        $stmt->bind_param("ssiii", $metricType, $tev, $quarter, $year, $agencyID);

        if ($stmt->execute()) {
            echo "Data submitted successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Please fill in all fields.";
    }
}

$conn->close();
?>
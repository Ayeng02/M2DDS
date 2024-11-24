<?php
// Database connection
include '../includes/db_connect.php'; // Make sure to adjust this path as needed

// Set the header to return JSON
header('Content-Type: application/json');

// Prepare SQL query to fetch employee logs along with employee names
$sql = "SELECT 
            e.emplog_id,
            e.emp_id,
            e.order_id,
            e.emplog_action,
            e.emplog_date,
            CONCAT(emp.emp_fname, ' ', emp.emp_lname) AS employee_name
        FROM emplog_tbl e
        JOIN emp_tbl emp ON e.emp_id = emp.emp_id";

$result = $conn->query($sql);

$logs = array();

if ($result->num_rows > 0) {
    // Fetch each log and store in array
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
}

// Return the logs as JSON
echo json_encode($logs);

// Close database connection
$conn->close();
?>

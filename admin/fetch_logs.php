<?php
// fetch_logs.php
include '../includes/db_connect.php';

$query = "
SELECT 
    sl.id,
    sl.user_id,
    sl.user_type,
    sl.systemlog_action,
    sl.systemlog_date,
    CASE 
        WHEN sl.user_type = 'Employee' THEN CONCAT(e.emp_fname, ' ', e.emp_lname)
        WHEN sl.user_type = 'Admin' THEN a.admin_name
    END AS user_name
FROM 
    systemlog_tbl sl
LEFT JOIN 
    emp_tbl e ON sl.user_id = e.emp_id AND sl.user_type = 'Employee'
LEFT JOIN 
    admin_tbl a ON sl.user_id = a.admin_id AND sl.user_type = 'Admin'
ORDER BY 
    sl.systemlog_date DESC
"; // Fetch logs with user names
$result = $conn->query($query);

$logs = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row; // Collect each log entry
    }
}

echo json_encode($logs); // Return the logs as JSON
?>

<?php
// Set timezone
date_default_timezone_set('Asia/Manila');

// Get current time and date
$time = date("h:i:s A");
$date = date("F j, Y");

// Return data as JSON
echo json_encode(array("time" => $time, "date" => $date));
?>

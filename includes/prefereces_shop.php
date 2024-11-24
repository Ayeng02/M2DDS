<?php

include './includes/db_connect.php';



// Fetch current shop background and video
$shop_bg_query = "SELECT olshopmgt_bg FROM olshopmgt_tbl LIMIT 1";  // Replace with actual table and column
$shop_vid_query = "SELECT olshopmgt_vid FROM olshopmgt_tbl LIMIT 1";  // Replace with actual table and column

$shop_bg_result = $conn->query($shop_bg_query);
$shop_vid_result = $conn->query($shop_vid_query);

$default_bg = './img/meat-bg.png'; // Default background image path
$default_vid = './img/sampleVid.mp4'; // Default video file path

// Set default image and video paths
$shop_bg = $default_bg; // Default background image
$shop_vid = $default_vid; // Default video file

// Check if the shop background image is found in the database
if ($shop_bg_result && $shop_bg_result->num_rows > 0) {
    $row = $shop_bg_result->fetch_assoc();
    $shop_bg = $row['olshopmgt_bg']; // Fetch the shop background from the database
}

// Check if the shop video is found in the database
if ($shop_vid_result && $shop_vid_result->num_rows > 0) {
    $row = $shop_vid_result->fetch_assoc();
    $shop_vid = $row['olshopmgt_vid']; // Fetch the shop video from the database
}

// Optionally, you could add an error message if the background or video files do not exist
if (!file_exists($shop_bg)) {
    $shop_bg = $default_bg; // Fallback to default background if file does not exist
}

if (!file_exists($shop_vid)) {
    $shop_vid = $default_vid; // Fallback to default video if file does not exist
}

?>
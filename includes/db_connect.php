<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "m2dds";

// Create connection with exception handling
try {
    $conn = new mysqli($servername, $username, $password, $database);

    // Check for connection errors
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    

} catch (Exception $e) {
    // Handle connection errors
    echo "Error: " . $e->getMessage();
}

?>

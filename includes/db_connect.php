<?php
$servername = "localhost";
$username = "u753706103_root_m2dds";
$password = "@Meattodoor123";
$database = "u753706103_m2dds";

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

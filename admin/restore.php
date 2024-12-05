<?php
// Database credentials
$host = 'localhost';
$user = 'u753706103_root_m2dds';
$password = '@Meattodoor123';
$dbname = 'u753706103_m2dds';

// Connect to the MySQL database
$connection = new mysqli($host, $user, $password, $dbname);

// Check the connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Check if a file is uploaded
if ($_FILES['backupFile']['error'] === UPLOAD_ERR_OK) {
    // Read the uploaded SQL file
    $fileContent = file_get_contents($_FILES['backupFile']['tmp_name']);

    // Split the SQL file into individual queries
    $queries = explode(";\n", $fileContent);

    // Loop through each query and execute it
    foreach ($queries as $query) {
        $query = trim($query);

        // Skip empty queries
        if (empty($query)) continue;

        // Handle INSERT INTO queries with ON DUPLICATE KEY UPDATE
        if (stripos($query, 'INSERT INTO') === 0) {
            // Convert the query to handle duplicates
            $query = preg_replace(
                "/INSERT INTO (`[^`]+`)/i",
                "INSERT INTO $1 ON DUPLICATE KEY UPDATE ",
                $query
            );
            $query .= " id = VALUES(id)"; // Example for updating primary key
        }

        // Execute the query
        if (!$connection->query($query)) {
            echo "Error: " . $connection->error . "\nQuery: " . $query;
        }
    }

    echo "Database restore complete!";
} else {
    echo "Error uploading file: " . $_FILES['backupFile']['error'];
}

// Close the connection
$connection->close();
?>

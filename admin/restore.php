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

if ($_FILES['backupFile']['error'] === UPLOAD_ERR_OK) {
    // Read the uploaded SQL file
    $fileContent = file_get_contents($_FILES['backupFile']['tmp_name']);
    
    // Split the SQL file into individual queries (assuming semi-colon separation)
    $queries = explode(";\n", $fileContent);

    // Loop through each query and execute it
    foreach ($queries as $query) {
        $query = trim($query);
        
        // Skip empty queries
        if (empty($query)) continue;

        // Check if the query is a CREATE TABLE statement
        if (stripos($query, 'CREATE TABLE') !== false) {
            // Extract the table name from the CREATE TABLE statement
            preg_match("/CREATE TABLE `([^`]+)`/i", $query, $matches);
            $table = $matches[1];

            // Check if the table already exists in the database
            $checkTableQuery = "SHOW TABLES LIKE '$table'";
            $checkResult = $connection->query($checkTableQuery);
            if ($checkResult->num_rows > 0) {
                // If the table exists, skip creating it
                continue; // Skip the current CREATE TABLE query
            } else {
                // If the table doesn't exist, execute the CREATE TABLE query
                $connection->query($query);
            }
        } elseif (stripos($query, 'INSERT INTO') !== false) {
            // Handle INSERT INTO queries
            // Use INSERT IGNORE to automatically skip duplicate entries based on primary key
            $insertIgnoreQuery = str_ireplace("INSERT INTO", "INSERT IGNORE INTO", $query);
            $connection->query($insertIgnoreQuery);
        } else {
            // Execute other queries (like table structure, indexes, etc.)
            $connection->query($query);
        }
    }

    echo "Database restore complete!";
} else {
    echo "Error: " . $_FILES['backupFile']['error'];
}

$connection->close();
?>

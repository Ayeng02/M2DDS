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

        // Check for CREATE TABLE statements
        if (stripos($query, 'CREATE TABLE') === 0) {
            // Add IF NOT EXISTS to CREATE TABLE statements
            if (!stripos($query, 'IF NOT EXISTS')) {
                $query = preg_replace(
                    "/CREATE TABLE (`?[^` ]+`?)/i",
                    "CREATE TABLE IF NOT EXISTS $1",
                    $query
                );
            }
        }

        try {
            // Execute the query
            if (!$connection->query($query)) {
                // Suppress errors for "already exists" cases
                $error = strtolower($connection->error);
                if (strpos($error, 'already exists') === false) {
                    echo "Query error: " . $connection->error . " for query: " . htmlspecialchars($query) . "<br>";
                }
            }
        } catch (mysqli_sql_exception $e) {
            // Suppress errors for "already exists" cases
            $error = strtolower($e->getMessage());
            if (strpos($error, 'already exists') === false) {
                echo "Error: " . htmlspecialchars($e->getMessage()) . " for query: " . htmlspecialchars($query) . "<br>";
            }
        }
    }

    echo "Database restore complete!";
} else {
    echo "Error uploading file: " . $_FILES['backupFile']['error'];
}

// Close the connection
$connection->close();
?>

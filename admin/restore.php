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

        // Modify INSERT INTO queries to IGNORE duplicates
        if (stripos($query, 'INSERT INTO') === 0) {
            $query = preg_replace(
                "/^INSERT INTO/i",
                "INSERT IGNORE INTO",
                $query
            );
        }

        try {
            // Execute the query
            $connection->query($query);
        } catch (mysqli_sql_exception $e) {
            // Log errors silently and continue
            // Optional: log the error to a file instead of displaying it
            error_log("Error executing query: " . $e->getMessage() . "\n", 3, "restore_errors.log");
        }
    }

    echo "Database restore complete!";
} else {
    echo "Error uploading file: " . $_FILES['backupFile']['error'];
}

// Close the connection
$connection->close();
?>

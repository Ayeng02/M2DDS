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

        // Check if this query is an INSERT statement
        if (stripos($query, 'INSERT INTO') !== false) {
            // Extract table name from the query
            preg_match("/INSERT INTO `([^`]+)`/i", $query, $matches);
            $table = $matches[1];

            // Get primary key column(s) of the table to check if data exists
            $primaryKeyQuery = "SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'";
            $primaryKeyResult = $connection->query($primaryKeyQuery);
            $primaryKey = [];
            while ($row = $primaryKeyResult->fetch_assoc()) {
                $primaryKey[] = $row['Column_name'];
            }

            // Get the data values in the query
            preg_match_all("/\((.*?)\)/", $query, $matches);
            $values = $matches[1];

            // Check if data already exists in the table
            foreach ($values as $value) {
                $columns = explode(",", $value);
                // Assuming the first column is the primary key (adjust accordingly)
                $primaryKeyValue = trim($columns[0], "'");

                // Check if the record already exists
                $checkQuery = "SELECT COUNT(*) AS count FROM `$table` WHERE `$primaryKey[0]` = '$primaryKeyValue'";
                $checkResult = $connection->query($checkQuery);
                $checkRow = $checkResult->fetch_assoc();

                if ($checkRow['count'] == 0) {
                    // If the record does not exist, execute the insert
                    $connection->query($query);
                }
            }
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

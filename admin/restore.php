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
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $connection->connect_error]));
}

// Check if a file is uploaded
if ($_FILES['backupFile']['error'] === UPLOAD_ERR_OK) {
    $fileContent = file_get_contents($_FILES['backupFile']['tmp_name']);
    $queries = explode(";\n", $fileContent);
    $success = true;

    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;

        if (stripos($query, 'CREATE TABLE') === 0) {
            if (!stripos($query, 'IF NOT EXISTS')) {
                $query = preg_replace(
                    "/CREATE TABLE (`?[^` ]+`?)/i",
                    "CREATE TABLE IF NOT EXISTS $1",
                    $query
                );
            }
        }

        try {
            if (!$connection->query($query)) {
                if (stripos($connection->error, 'already exists') === false) {
                    $success = false;
                }
            }
        } catch (mysqli_sql_exception $e) {
            if (stripos($e->getMessage(), 'already exists') === false) {
                $success = false;
            }
        }
    }

    echo json_encode(["status" => $success ? "success" : "error", "message" => $success ? "Database restored successfully." : "Some queries failed during restore."]);
} else {
    echo json_encode(["status" => "error", "message" => "Error uploading file: " . $_FILES['backupFile']['error']]);
}

$connection->close();
?>

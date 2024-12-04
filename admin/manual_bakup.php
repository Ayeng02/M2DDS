<?php
// Database credentials
$host = 'localhost';  // Usually localhost in Hostinger
$user = 'u753706103_root_m2dds';
$password = '@Meattodoor123';
$dbname = 'u753706103_m2dds';

// Connect to the MySQL database
$connection = new mysqli($host, $user, $password, $dbname);

// Check the connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Initialize the SQL backup content
$backupContent = "-- Database backup: $dbname\n";
$backupContent .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";

// Get all table names from the database
$tablesResult = $connection->query('SHOW TABLES');
$tables = [];
while ($row = $tablesResult->fetch_row()) {
    $tables[] = $row[0];
}

// Loop through each table and add its structure and data to the backup
foreach ($tables as $table) {
    // Add the table's structure (CREATE TABLE statement)
    $result = $connection->query("SHOW CREATE TABLE `$table`");
    $row = $result->fetch_row();
    $backupContent .= "\n-- Table: `$table`\n";
    $backupContent .= $row[1] . ";\n\n";
    
    // Add the table's data (INSERT INTO statements)
    $result = $connection->query("SELECT * FROM `$table`");
    while ($row = $result->fetch_assoc()) {
        $backupContent .= "INSERT INTO `$table` (";
        $backupContent .= implode(", ", array_keys($row)) . ") VALUES (";
        $backupContent .= "'" . implode("', '", array_values($row)) . "');\n";
    }
    $backupContent .= "\n";
}

// Close the database connection
$connection->close();

// Send the backup content as a downloadable file to the user's device
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="backup_' . date('Y-m-d_H-i-s') . '.sql"');
echo $backupContent;

exit;
?>

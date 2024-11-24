<?php
// Include database connection
include '../includes/db_connect.php';

// Check if `pos_code` is set in the URL
if (isset($_GET['pos_code'])) {
    $pos_code = $_GET['pos_code'];

    // Prepare the SQL delete statement
    $sql = "DELETE FROM pos_tbl WHERE pos_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $pos_code);

    if ($stmt->execute()) {
        // If the delete was successful, set a success message
        $_SESSION['delete_success'] = "Record successfully deleted.";
    } else {
        // If an error occurred, set an error message
        $_SESSION['error'] = "Error deleting record: " . $conn->error;
    }

    // Close the statement
    $stmt->close();
} else {
    // Redirect back if no `pos_code` provided
    $_SESSION['error'] = "No record specified to delete.";
}

// Close the database connection
$conn->close();

// Redirect back to the main page (or the appropriate page)
header("Location: viewPOS.php");
exit;
?>

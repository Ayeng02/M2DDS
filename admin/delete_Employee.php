<?php
// Include database connection
include '../includes/db_connect.php';

// Check if the id parameter is set
if (isset($_GET['id'])) {
    // Get the employee ID from the URL
    $emp_id = $_GET['id'];

    // Prepare the SQL statement to delete the employee
    $sql = "DELETE FROM emp_tbl WHERE emp_id = ?";

    // Prepare and execute the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind the parameter
        $stmt->bind_param('s', $emp_id);

        // Attempt to execute the statement
        if ($stmt->execute()) {
            // Successfully deleted, set success message
            $_SESSION['delete_success'] = 'Employee deleted successfully!';
        } else {
            // Error occurred, set error message
            $_SESSION['delete_error'] = 'Error deleting employee: ' . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        // Error preparing SQL statement
        $_SESSION['error'] = 'Error preparing SQL statement: ' . $conn->error;
    }
} else {
    $_SESSION['error'] = 'No employee ID specified.';
}

// Close the database connection
$conn->close();

// Redirect back to the employee list page (or wherever you want)
header("Location: addEmployee.php");
exit();
?>

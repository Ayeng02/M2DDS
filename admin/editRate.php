<?php
// Start session for alert messages
session_start();

// Database connection
include '../includes/db_connect.php';

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the values from the form
    $role = $_POST['Role'];       
    $dailyRate = $_POST['Daily-rate'];  

     // Remove the Peso symbol (₱) from the daily rate, if it exists
    $dailyRate = str_replace('₱', '', $dailyRate);


    // Validate the data (check if dailyRate is not empty)
    if (!empty($dailyRate)) {
        // Check if the role exists in the daily_rates table
        $checkQuery = "SELECT * FROM daily_rates WHERE role_name = ?";

        if ($stmt = $conn->prepare($checkQuery)) {
            $stmt->bind_param("s", $role);   // Bind role to the query
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // Role exists, proceed to update the daily rate for the given role
                $updateQuery = "UPDATE daily_rates SET daily_rate = ? WHERE role_name = ?";

                if ($updateStmt = $conn->prepare($updateQuery)) {
                    $updateStmt->bind_param("ds", $dailyRate, $role); // Bind the parameters

                    // Execute the update query
                    if ($updateStmt->execute()) {
                        // Successful update
                        $_SESSION['alert'] = [
                            'icon' => 'success',
                            'title' => 'Daily rate updated successfully for ' . htmlspecialchars($role) . '.'
                        ];
                    } else {
                        // Error in executing update
                        $_SESSION['alert'] = [
                            'icon' => 'error',
                            'title' => 'Failed to update daily rate. Please try again.'
                        ];
                    }
                    $updateStmt->close();  // Close the prepared statement
                } else {
                    // Error in preparing the update statement
                    $_SESSION['alert'] = [
                        'icon' => 'error',
                        'title' => 'Error preparing update statement: ' . $conn->error
                    ];
                }
            } else {
                // Role not found in the table
                $_SESSION['alert'] = [
                    'icon' => 'warning',
                    'title' => 'Role not found in daily rates table.'
                ];
            }
            $stmt->close();  // Close the check query statement
        } else {
            // Error in preparing the check query
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Error preparing check query: ' . $conn->error
            ];
        }
    } else {
        // If the daily rate is empty
        $_SESSION['alert'] = [
            'icon' => 'warning',
            'title' => 'Please fill in the daily rate field!'
        ];
    }

    // Redirect back to the payroll page or the page with the modal
    header("Location: ratesConfig.php"); // Replace with your actual page
    exit();
} else {
    // Invalid request method
    $_SESSION['error'] = "Invalid request method.";
    header("Location: ratesConfig.php");
    exit();
}
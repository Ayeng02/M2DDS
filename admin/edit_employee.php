<?php
// Start session for alert messages
session_start();

// Database connection
include '../includes/db_connect.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $emp_id = $_POST['emp_id'];
    $full_name = explode(' ', $_POST['full_name']); // Split full name into first and last name
    $emp_fname = isset($full_name[0]) ? $full_name[0] : ''; // First name
    $emp_lname = isset($full_name[1]) ? $full_name[1] : ''; // Last name
    $emp_email = $_POST['email'];
    $emp_num = $_POST['contact'];
    $emp_address = $_POST['address'];
    $emp_role = $_POST['role'];

    // Initialize an empty variable for the image path
    $image_path = null;

    // Check for duplicate full name
$sql = "SELECT emp_id FROM emp_tbl WHERE emp_fname = ? AND emp_lname = ? AND emp_id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sss', $emp_fname, $emp_lname, $emp_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Set alert data in session for duplicate full name
    $_SESSION['alert'] = [
        'icon' => 'error',
        'title' => "Employee with the same full name already exists. Please use a different name."
    ];
    // Redirect to the addEmployee.php page
    header("Location: addEmployee.php?alert=1");
    exit; 
}

  // Check for duplicate product name
  $sql = "SELECT emp_id FROM emp_tbl WHERE emp_email = ? AND emp_id != ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('ss', $emp_id, $emp_email);
  $stmt->execute();
  $stmt->store_result();
  
  if ($stmt->num_rows > 0) {
  
      // Set alert data in session
      $_SESSION['alert'] = [
          'icon' => 'error',
          'title' => "Employee email already exists. Please choose a different name."
      ];
      // Redirect to the add products page
      header("Location: addEmployee.php?alert=1");
      exit; // Ensure no further code is executed
  }
    
    // Check if a new image was uploaded
    if (isset($_FILES['emp_img']) && $_FILES['emp_img']['error'] == UPLOAD_ERR_OK) {
        // Handle the file upload
        $upload_dir = '../employee_images/'; // Path to upload directory
        $relative_dir_path = 'employee_images/';
        $image_file = $_FILES['emp_img'];
        $image_name = basename($image_file['name']);
        $image_path = $upload_dir . $image_name;
        $relative_path_to_store =   $relative_dir_path . $image_name;

        // Move the uploaded file to the target directory
        if (!move_uploaded_file($image_file['tmp_name'], $image_path)) {
            $_SESSION['alert'] = [
             'icon' => 'error',
             'title' => "Employee email already exists. Please choose a different name."];
            header("Location: addEmployee.php");
            exit();
        }
    } else {
        // If no new image was uploaded, fetch the current image path from the database
        $sql = "SELECT emp_img FROM emp_tbl WHERE emp_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('s', $emp_id);
            $stmt->execute();
            $stmt->bind_result($existing_image);
            $stmt->fetch();
            $stmt->close();

            // Use the existing image if no new image is uploaded
            $relative_path_to_store= $existing_image;
        }
    }

    // Update the employee in the database
    $sql = "UPDATE emp_tbl SET 
                emp_fname = ?, 
                emp_lname = ?, 
                emp_email = ?, 
                emp_num = ?, 
                emp_address = ?, 
                emp_role = ?, 
                emp_img = ? 
            WHERE emp_id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('ssssssss', $emp_fname, $emp_lname, $emp_email, $emp_num, $emp_address, $emp_role, $relative_path_to_store, $emp_id);

        if ($stmt->execute()) {

            // Insert into system log upon successful employee update
            $user_id = $_SESSION['admin_id'];
            $action = "Edit employee: " . $emp_fname . " " . $emp_lname . " (" . $emp_id . ")";
            $user_type = 'Admin';

            // Prepare and execute system log insertion
            if ($log_stmt = $conn->prepare("INSERT INTO systemlog_tbl (user_id, user_type, systemlog_action, systemlog_date) VALUES (?, ?, ?, NOW())")) {
                $log_stmt->bind_param("sss", $user_id, $user_type, $action);
                $log_stmt->execute();
                $log_stmt->close();
            } else {
                $_SESSION['error'] = "Failed to log system action: " . $conn->error;
            }

            $_SESSION['success'] = 'Employee updated successfully!';
        } else {
            $_SESSION['error'] = "Error updating employee: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "Error preparing SQL: " . $conn->error;
    }

    $conn->close();
    
    // Redirect to the addEmployee.php page with success or error message
    header("Location: addEmployee.php");
    exit();
} else {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: addEmployee.php");
    exit();
}
?>

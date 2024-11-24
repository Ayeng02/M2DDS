<?php
include '../includes/db_connect.php';
session_start(); // Start session to access emp_id

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = $_POST['category_name'];
    $categoryDesc = $_POST['category_desc'];
    $emp_id = $_SESSION['emp_id']; // Get emp_id from session

    // Define the target directory for category images
    $target_dir = "../category/";

    // Ensure the category directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true); // Create the directory if it doesn't exist
    }

    // Handle file upload
    $category_img = $_FILES['category_img'];
    $fileName = basename($category_img['name']);
    $targetFilePath = $target_dir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    // Allow certain file formats
    $allowedTypes = ['jpeg', 'jpg', 'png'];

    if (in_array(strtolower($fileType), $allowedTypes)) {
        // Upload file to server
        if (move_uploaded_file($category_img['tmp_name'], $targetFilePath)) {
            // Save only the relative path in the database
            $relative_path_to_store = "category/" . $fileName;

            // Prepare to call the stored procedure
            $stmt = $conn->prepare("CALL sp_InsertCategory(?, ?, ?)");
            $stmt->bind_param("sss", $categoryName, $categoryDesc, $relative_path_to_store);

            if ($stmt->execute()) {
                // Log the action in the system log
                $action = "Added new category: " . $categoryName;
                $logStmt = $conn->prepare("INSERT INTO systemlog_tbl (user_id, user_type, systemlog_action, systemlog_date) VALUES (?, 'Employee', ?, NOW())");
                $logStmt->bind_param("ss", $emp_id, $action);

                if ($logStmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Category added and action logged successfully!']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Category added, but logging action failed.']);
                }
                $logStmt->close();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to add category.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File upload failed.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Only JPG, JPEG, and PNG files are allowed.']);
    }
}
?>

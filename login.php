<?php
session_start(); // Start the session

// Set the time zone to Manila/Philippines
date_default_timezone_set('Asia/Manila');

// Redirect to landing page if already logged in
if (isset($_SESSION['EmpLogExist']) && $_SESSION['EmpLogExist'] === true || isset($_SESSION['AdminLogExist']) && $_SESSION['AdminLogExist'] === true) {
    if (isset($_SESSION['emp_role'])) {
        // Redirect based on employee role
        switch ($_SESSION['emp_role']) {
            case 'Shipper':
                header("Location: ./shipper/shipper.php");
                exit;
            case 'Order Manager':
                header("Location: ./ordr_manager/order_manager.php");
                exit;
            case 'Cashier':
                header("Location: ./shipper/dashboard2.php");
                exit;
            case 'Admin':
                header("Location: ./admin/admin_interface.php");
                exit;
            default:
        }
    }
}

// Database connections
include './includes/db_connect.php';

// Constants
const MAX_ATTEMPTS = 5;
const BAN_DURATION = 86400; // 1 day in seconds

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check login attempts
    $stmt = $conn->prepare("SELECT failed_attempts, last_failed FROM login_attempts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($failed_attempts, $last_failed);
    $stmt->fetch();
    $stmt->close();

    $current_time = time();

    // Check if user is banned
    if ($failed_attempts >= MAX_ATTEMPTS && ($current_time - strtotime($last_failed)) < BAN_DURATION) {
        $error_message = 'Account is locked. Please try again later.';
    } else {
        // Check if the email belongs to an admin
        $adminSql = "SELECT admin_id, admin_pass FROM admin_tbl WHERE admin_email = ?";
        $adminStmt = $conn->prepare($adminSql);
        $adminStmt->bind_param("s", $email);
        $adminStmt->execute();
        $adminStmt->store_result();

        if ($adminStmt->num_rows > 0) {
            // Admin found
            $adminStmt->bind_result($adminId, $adminHashedPassword);
            $adminStmt->fetch();

            if (password_verify($password, $adminHashedPassword)) {
                // Reset failed attempts on successful login
                resetFailedAttempts($email, $conn);

                $_SESSION['AdminLogExist'] = true;
                $_SESSION['admin_id'] = $adminId;
                $_SESSION['emp_role'] = 'Admin';

                // Log the successful login in the system log
                $action = "Successful login by Employee ID: " . $adminId;
                $logStmt = $conn->prepare("INSERT INTO systemlog_tbl (user_id, user_type, systemlog_action, systemlog_date) VALUES (?, 'Admin', ?, NOW())");
                $logStmt->bind_param("ss", $adminId, $action);

                if (!$logStmt->execute()) {
                    // Handle logging failure (optional)
                    error_log("Logging action failed for Employee ID: " . $adminId);
                }
                $logStmt->close();

                header('Location: ./admin/admin_interface.php');
                exit();
            } else {
                $error_message = handleFailedAttempt($email, $conn);
            }
            $adminStmt->close();
        } else {
            // Admin not found, check if email exists in employee table
            $empSql = "SELECT emp_id, emp_pass, emp_role FROM emp_tbl WHERE emp_email = ?";
            $empStmt = $conn->prepare($empSql);
            $empStmt->bind_param("s", $email);
            $empStmt->execute();
            $empStmt->store_result();

            if ($empStmt->num_rows > 0) {
                // Employee found
                $empStmt->bind_result($empId, $empHashedPassword, $empRole);
                $empStmt->fetch();

                if (password_verify($password, $empHashedPassword)) {
                    // Reset failed attempts on successful login
                    resetFailedAttempts($email, $conn);

                    // Check if the employee has logged in attendance for the current day
                    $today = date('Y-m-d');
                    $attSql = "SELECT att_id, time_out FROM att_tbl WHERE emp_id = ? AND DATE(att_date) = ?";
                    $attStmt = $conn->prepare($attSql);
                    $attStmt->bind_param("ss", $empId, $today);
                    $attStmt->execute();
                    $attStmt->store_result();
                    $attStmt->bind_result($attId, $timeOut);

                    if ($attStmt->num_rows > 0) {
                        $attStmt->fetch();

                        // Check if the time_out is already recorded for the day
                        if (!is_null($timeOut)) {
                            // Employee has logged out for the day, deny login
                            $error_message = 'Ooopss. You have already logged out for today!';
                        } else {
                            // Employee has already logged in for the day, proceed with redirection
                            $_SESSION['EmpLogExist'] = true;
                            $_SESSION['emp_id'] = $empId;
                            $_SESSION['emp_role'] = $empRole;

                            // Log the successful login in the system log
                            $action = "Successful login by Employee ID: " . $empId;
                            $logStmt = $conn->prepare("INSERT INTO systemlog_tbl (user_id, user_type, systemlog_action, systemlog_date) VALUES (?, 'Employee', ?, NOW())");
                            $logStmt->bind_param("ss", $empId, $action);

                            if (!$logStmt->execute()) {
                                // Handle logging failure (optional)
                                error_log("Logging action failed for Employee ID: " . $empId);
                            }
                            $logStmt->close();

                            // Update employee status to 'Active' only if the current status is not 'On Shipped'
                            $statusUpdateSql = "UPDATE emp_tbl SET emp_status = 'Active' WHERE emp_id = ? AND emp_status != 'On Shipped'";
                            $statusUpdateStmt = $conn->prepare($statusUpdateSql);
                            $statusUpdateStmt->bind_param("s", $empId);
                            $statusUpdateStmt->execute();
                            $statusUpdateStmt->close();

                            $redirectUrl = '';
                            switch ($empRole) {
                                case 'Shipper':
                                    $redirectUrl = './shipper/shipper.php';
                                    break;
                                case 'Order Manager':
                                    $redirectUrl = './ordr_manager/order_manager.php';
                                    break;
                                case 'Cashier':
                                    $redirectUrl = './cashier/cashier.php';
                                    break;
                                default:
                                    $redirectUrl = 'login.php';
                                    break;
                            }
                            header("Location: $redirectUrl");
                            exit();
                        }
                    } else {
                        // Employee has not logged in for the day, redirect to attendance page
                        $error_message = 'Ooopss. You did not log your attendance yet!';
                    }
                } else {
                    $error_message = handleFailedAttempt($email, $conn);
                }
                $empStmt->close();
            } else {
                $error_message = handleFailedAttempt($email, $conn);
            }
        }
    }
}

$conn->close();

// Helper function to reset failed attempts
function resetFailedAttempts($email, $conn) {
    $stmt = $conn->prepare("DELETE FROM login_attempts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->close();
}

// Helper function to handle failed attempts
function handleFailedAttempt($email, $conn) {
    $failed_attempts = 0; // Initialize with default value
    $last_failed = null; // Initialize with default value

    $stmt = $conn->prepare("SELECT failed_attempts, last_failed FROM login_attempts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($failed_attempts, $last_failed);

    // Fetch result, if exists
    if (!$stmt->fetch()) {
        // If no row exists, set defaults
        $failed_attempts = 0;
        $last_failed = null;
    }
    $stmt->close();

    $current_time = time();

    // Check and handle the ban duration
    if ($failed_attempts > 0 && $last_failed !== null) {
        $last_failed_time = strtotime($last_failed);
        if (($current_time - $last_failed_time) > BAN_DURATION) {
            // Reset attempts after ban duration
            $failed_attempts = 0;
        }
    }

    // Increment failed attempts
    $failed_attempts++;
    if ($failed_attempts > 1) {
        $stmt = $conn->prepare("UPDATE login_attempts SET failed_attempts = ?, last_failed = NOW() WHERE email = ?");
        $stmt->bind_param("is", $failed_attempts, $email);
    } else {
        $stmt = $conn->prepare("INSERT INTO login_attempts (email, failed_attempts, last_failed) VALUES (?, ?, NOW())");
        $stmt->bind_param("si", $email, $failed_attempts);
    }
    $stmt->execute();
    $stmt->close();

    if ($failed_attempts >= MAX_ATTEMPTS) {
        return 'Account is locked due to multiple failed login attempts. Please try again later.';
    } else {
        return 'Invalid email or password.';
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN | STAKEHOLDER</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts (Optional) -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/loginstake.css">
    <link rel="icon" href="./img/mtdd_logo.png" type="image/x-icon">
    <style>
        
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
        }

        .login-footer a {
            color: #007bff;
            text-decoration: none;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Logo Section -->
        <div class="logo">
            <img src="./img/mtdd_logo.png" alt="Logo">
        </div>
        <h2>Login</h2>

        <!-- Display Error Message -->
        <?php if (isset($error_message)): ?>
            <div id="error-alert" class="alert alert-danger">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form id="loginForm" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <!-- Show Password Checkbox -->
            <div class="show-password">
                <input type="checkbox" id="showPassword">
                <label for="showPassword">Show Password</label>
            </div>
            <button type="submit" class="btn btn-custom w-100">Login</button>
            <div class="login-footer">
                <p><a href="./stakeholder/send-reset-email.php">Forgot your password?</a></p>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS and Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS for Show Password -->
    <script>
        document.getElementById('showPassword').addEventListener('change', function() {
            const passwordInput = document.getElementById('password');
            passwordInput.type = this.checked ? 'text' : 'password';
        });

        // Hide the alert after 5 seconds
        setTimeout(function() {
            var errorAlert = document.getElementById('error-alert');
            if (errorAlert) {
                errorAlert.style.display = 'none';
            }
        }, 5000); // 5 seconds
    </script>
</body>

</html>
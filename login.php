<?php
session_start(); // Start the session



// Redirect to landing page if already logged in
if (isset($_SESSION['EmpLogExist']) && $_SESSION['EmpLogExist'] === true || isset($_SESSION['AdminLogExist']) && $_SESSION['AdminLogExist'] === true) {
    
    
    if (isset($_SESSION['emp_role'])) {
        // Redirect based on employee role
        switch ($_SESSION['emp_role']) {
            case 'Shipper':
                header("Location: ./shipper/shipper.php");
                exit;
            case 'Order Manager':
                header("Location: ./ordr_manager/ordr_manager.php");
                exit;
            case 'Cashier':
                header("Location: ./cashier/cashier.php");
                exit;
            case 'Admin':
                header("Location: ./admin/admin_interface.php");
                exit;
            default:
        }
    }

}


// Database connection
include './includes/db_connect.php';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

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
            // Password is correct, set session data and redirect to admin directory
            $_SESSION['AdminLogExist'] = true;
            $_SESSION['admin_id'] = $adminId;
            $_SESSION['emp_role'] = 'Admin';

            header('Location: ./admin/admin_interface.php');
            exit();
        } else {
            $error_message = 'Invalid email or password';
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
                // Password is correct, set session data and redirect based on emp_role
                $_SESSION['EmpLogExist'] = true;
                $_SESSION['emp_id'] = $empId;
                $_SESSION['emp_role'] = $empRole;

                $redirectUrl = '';
                switch ($empRole) {
                    case 'Shipper':
                        $redirectUrl = 'shipper_dashboard.php';
                        break;
                    case 'Order Manager':
                        $redirectUrl = 'order_manager_dashboard.php';
                        break;
                    case 'Cashier':
                        $redirectUrl = 'cashier_dashboard.php';
                        break;
                    default:
                        $redirectUrl = 'login.php'; // Default if no role matches
                        break;
                }
                header("Location: $redirectUrl");
                exit();
            } else {
                $error_message = 'Invalid email or password';
            }
            $empStmt->close();
        } else {
            $error_message = 'Invalid email or password';
        }
    }

    $conn->close();
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
    <!-- Custom CSS -->
    <style>
        body {
            background: linear-gradient(135deg, #ff6b6b, #f7d08a, #6b5b95, #d4e157);
            background-size: 400% 400%;
            animation: gradientAnimation 10s ease infinite;
            font-family: 'Roboto', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        @keyframes gradientAnimation {
            0% { background-position: 0% 0%; }
            50% { background-position: 100% 100%; }
            100% { background-position: 0% 0%; }
        }

        .login-container {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: slideIn 1s ease-out;
            width: 100%;
            max-width: 500px;
        }


        .login-container h2 {
            color: #a72828;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-control:focus {
            border-color: #FF8225;
            box-shadow: none;
        }

        .btn-custom {
            background-color: #a72828;
            border-color: #a72828;
            color: #fff;
            transition: background-color 0.3s ease;
        }

        .btn-custom:hover {
            background-color: #FF8225;
            border-color: #FF8225;
        }

        .logo {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .logo img {
            width: 100px;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .login-container {
                padding: 20px;
            }

            .login-container h2 {
                font-size: 24px;
                margin-bottom: 20px;
            }

            .btn-custom {
                font-size: 16px;
            }

            .form-control {
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 15px;
            }

            .logo img {
                width: 80px;
            }

            .login-container h2 {
                font-size: 22px;
            }

            .form-control {
                font-size: 13px;
                padding: 10px;
            }

            .btn-custom {
                padding: 10px 20px;
            }
        }

        /* Style for Show Password */
        .show-password {
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 15px;
            color: #333;
        }

        .show-password input {
            width: auto;
        }

        .alert {
            margin-top: 20px;
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
    <div class="alert alert-danger">
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
    </form>
</div>

    <!-- Bootstrap JS and Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS for Show Password -->
    <script>
        document.getElementById('showPassword').addEventListener('change', function () {
            const passwordInput = document.getElementById('password');
            if (this.checked) {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        });
    </script>
</body>

</html>

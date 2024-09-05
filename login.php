<?php
session_start(); // Start the session

// Initialize error message
$error_message = "";

// Redirect to the landing page if already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $emp_role = $_SESSION['emp_role'];
    
    switch ($emp_role) {
        case 'Admin':
            header("Location: admin_dashboard.php");
            break;
        case 'Shipper':
            header("Location: shipper_dashboard.php");
            break;
        case 'Order Manager':
            header("Location: order_manager_dashboard.php");
            break;
        case 'Cashier':
            header("Location: cashier_dashboard.php");
            break;
        default:
            $error_message = "Invalid role.";
            break;
    }
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Database connection
    include './includes/db_connect.php';
    
    // Check if the email belongs to an admin
    $stmt = $conn->prepare("SELECT admin_pass FROM admin_tbl WHERE admin_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        // Verify password for admin
        if (password_verify($password, $hashed_password)) {
            // Store session data
            $_SESSION['loggedin'] = true;
            $_SESSION['email'] = $email;
            $_SESSION['emp_role'] = 'Admin'; // Set role to Admin

            // Redirect to admin dashboard
            header("Location: admin_dashboard.php");
            exit();
        } else {
            // Password mismatch for admin
            $error_message = "Incorrect password.";
        }
    } else {
        // Check if email exists in employee table
        $stmt->close();
        $stmt = $conn->prepare("SELECT emp_pass, emp_role FROM emp_tbl WHERE emp_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($hashed_password, $emp_role);
            $stmt->fetch();

            // Verify password for employee
            if (password_verify($password, $hashed_password)) {
                // Store session data
                $_SESSION['loggedin'] = true;
                $_SESSION['email'] = $email;
                $_SESSION['emp_role'] = $emp_role;

                // Redirect based on role
                switch ($emp_role) {
                    case 'Shipper':
                        header("Location: shipper_dashboard.php");
                        break;
                    case 'Order Manager':
                        header("Location: order_manager_dashboard.php");
                        break;
                    case 'Cashier':
                        header("Location: cashier_dashboard.php");
                        break;
                    default:
                        $error_message = "Invalid role.";
                        break;
                }
                exit();
            } else {
                // Password mismatch for employee
                $error_message = "Incorrect password or email";
            }
        } else {
            // Email not found in employee table
            $error_message = "Email not found.";
        }
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsive Animated Login Form</title>
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
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Logo Section -->
        <div class="logo">
            <img src="./img/mtdd_logo.png" alt="Logo">
        </div>
        <h2>Login</h2>

        <!-- Display error message if any -->
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
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

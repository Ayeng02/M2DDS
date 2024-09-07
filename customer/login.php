<?php
session_start();

// Redirect to landing page if already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: customerLandingPage.php');
    exit();
}


// Database connection
include '../includes/db_connect.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute query
    $stmt = $conn->prepare("SELECT cust_id, cust_pass FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id, $hashed_password);
    $stmt->fetch();
    $stmt->close();

    // Validate password
    if ($user_id && password_verify($password, $hashed_password)) {
        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);

        // Set session variables
        $_SESSION['loggedin'] = true;
        $_SESSION['email'] = $email;
        $_SESSION['cust_id'] = $user_id;

        // Redirect to dashboard or landing page
        header('Location: customerLandingPage.php');
        exit();
    } else {
        $error = 'Invalid email or password.';
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Meat-To-Door Delivery</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">
    <style>
        body {
            display: flex;
            justify-content: center;
        }

        .background-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 110%;
            background: linear-gradient(135deg, #ff6b6b, #f7d08a, #6b5b95, #d4e157);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            z-index: -1;
        }

        @keyframes gradientAnimation {
            0% {
                background-position: 0% 0%;
            }

            50% {
                background-position: 100% 100%;
            }

            100% {
                background-position: 0% 0%;
            }
        }

        .login-container {
            width: 100%;
            max-width: 500px;
            padding: 2rem;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: absolute;
            z-index: 1;
            overflow: hidden;
            margin-top: 75px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .login-header img {
            max-width: 120px;
            margin-bottom: 1rem;
        }

        .login-header h1 {
            font-size: 1.75rem;
            color: #333;
            margin: 0;
        }

        .login-header p {
            color: #6c757d;
            margin: 0;
            font-size: 1rem;
        }

        .login-form .form-group {
            margin-bottom: 1.5rem;
        }

        .login-form .form-control {
            border-radius: 0.25rem;
            border: 1px solid #ced4da;
            padding: 0.75rem;
            font-size: 1rem;
        }

        .login-form .btn {
            border-radius: 0.25rem;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
        }

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

        .alert {
            display: none;
        }

        .navbar {
            width: 100%;
            z-index: 999;
            position: fixed;
            background-color: #FF8225;
        }

        .nav-link {
            color: black;
            transition: color 0.3s;
        }

        .nav-link:hover,
        .nav-item.active .nav-link {
            color: white;
        }

        .navbar-brand {
            color: white;
        }

        .navbar-brand:hover {
            color: white;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='crimson' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }

        .logo2 {
            height: 40px;
            margin-right: 10px;
        }

        .btn-primary {
            background-color: #FF8225;
            border-color: #FF8225;
        }

        .btn-primary:hover {
            background-color: #e36f10;
            border-color: #e36f10;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="#">
            <img class="logo2" src="../img/mtdd_logo.png" alt="Meat-To-Door Logo">
            Meat-To-Door
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php">
                        <i class="fas fa-home"></i> Home <span class="sr-only">(current)</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-info-circle"></i> About Us
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-envelope"></i> Contact
                    </a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="login.php">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="register.php">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                </li>
            </ul>
        </div>
    </nav>

 <div class="background-animation"></div>
    <div class="container login-container">
        <div class="login-header">
            <img src="../img/mtdd_logo.png" alt="Meat-To-Door Logo">
            <h1>Login</h1>
            <p>Where Quality Meets Affordability</p>
        </div>
        <form class="login-form" action="login.php" method="post">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert" id="errorAlert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe">
                <label class="form-check-label" for="rememberMe">Remember me</label>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        <div class="login-footer">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
            <p><a href="send-reset-email.php">Forgot your password?</a></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Show error alert for 5 seconds if it exists
        document.addEventListener('DOMContentLoaded', function() {
            var errorAlert = document.getElementById('errorAlert');
            if (errorAlert) {
                errorAlert.style.display = 'block';
                setTimeout(function() {
                    errorAlert.style.opacity = '0';
                    setTimeout(function() {
                        errorAlert.style.display = 'none';
                        errorAlert.style.opacity = '1';
                    }); // Time to complete fade out effect
                }, 5000); // Time to show error (5 seconds)
            }
        });
    </script>
</body>

</html>
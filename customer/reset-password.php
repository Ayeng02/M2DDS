<?php
session_start();


// Database connection (update with your credentials)
include '../includes/db_connect.php';


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['code'])) {
        // Handle verification code input
        $code = $_POST['code'];
        $email = $_SESSION['email'] ?? '';


        // Check if the code matches
        $stmt = $conn->prepare("SELECT id FROM password_resets WHERE email = ? AND code = ?");
        $stmt->bind_param("si", $email, $code);
        $stmt->execute();
        $stmt->bind_result($reset_id);
        $stmt->fetch();
        $stmt->close();

        if ($reset_id) {
            // Code is correct, proceed to reset password
            $_SESSION['verified'] = true;
        } else {
            $error = "Invalid verification code.";
        }
    } elseif (isset($_POST['new_password']) && isset($_SESSION['verified']) && $_SESSION['verified'] === true) {
        // Handle new password submission
        $new_password = $_POST['new_password'];
        $email = $_SESSION['email'] ?? '';

        // Update the password in the database
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE Customers SET cust_pass = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        $stmt->execute();
        $stmt->close();

        // Remove reset entry and session data
        $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();

        unset($_SESSION['verified']);
        unset($_SESSION['email']);
        $_SESSION['password_reset_success'] = true;
        header('Location: reset-success.php');
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - Meat-To-Door Delivery</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
            height: 100%;
            background: linear-gradient(135deg, #ff6b6b, #f7d08a, #6b5b95, #d4e157);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            z-index: -1;
        }
        @keyframes gradientAnimation {
            0% { background-position: 0% 0%; }
            50% { background-position: 100% 100%; }
            100% { background-position: 0% 0%; }
        }
        .login-container {
            width: 100%;
            max-width: 500px;
            padding: 2rem;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: absolute;
            z-index: 1;
            overflow: hidden;
            margin-top: 100px;
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
        .logo {
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
        .password-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .password-feedback.valid {
            color: #28a745;
        }


    </style>
</head>
<body>

    <div class="background-animation"></div>
    <div class="container login-container">
        <div class="login-header">
            <img src="../img/logo.ico" alt="Meat-To-Door Logo">
            <h1><?php echo isset($_SESSION['verified']) && $_SESSION['verified'] === true ? 'Reset Password' : 'Verify Code'; ?></h1>
            <p><?php echo isset($_SESSION['verified']) && $_SESSION['verified'] === true ? 'Enter your new password' : 'Enter the verification code sent to your email'; ?></p>
        </div>
        <form class="login-form" action="" method="post">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert" id="errorAlert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <?php if (!isset($_SESSION['verified']) || $_SESSION['verified'] !== true): ?>
                <div class="form-group">
                    <label for="code">Verification Code</label>
                    <input type="text" class="form-control" id="code" name="code" placeholder="Enter the verification code" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Verify Code</button>
            <?php else: ?>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter your new password" required>
                    <div id="passwordFeedback" class="password-feedback"></div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
            <?php endif; ?>
        </form>
        <div class="login-footer">
            <p>Remembered your password? <a href="login.php">Login here</a></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
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

        var passwordForm = document.querySelector('.login-form');
        var passwordInput = document.getElementById('new_password');
        var feedback = document.getElementById('passwordFeedback');

        passwordInput.addEventListener('input', function() {
            var password = passwordInput.value;
            var strength = checkPasswordStrength(password);

            if (strength === 'Strong') {
                feedback.textContent = 'Password strength: Strong';
                feedback.className = 'password-feedback valid';
            } else if (strength === 'Medium') {
                feedback.textContent = 'Password strength: Medium';
                feedback.className = 'password-feedback';
            } else {
                feedback.textContent = 'Password strength: Weak';
                feedback.className = 'password-feedback';
            }
        });

        passwordForm.addEventListener('submit', function(event) {
            var password = passwordInput.value;
            var strength = checkPasswordStrength(password);

            if (strength === 'Weak') {
                event.preventDefault(); // Prevent form submission
                Swal.fire({
                    title: 'Weak Password',
                    text: 'Your password is too weak. Please enter a stronger password.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            }
        });

        function checkPasswordStrength(password) {
            var strength = 'Weak';
            if (password.length >= 8 &&
                /[A-Z]/.test(password) &&
                /[a-z]/.test(password) &&
                /[0-9]/.test(password) &&
                /[!@#$%^&*()_+{}\[\]:;"'<>,.?~`-]/.test(password)) {
                strength = 'Strong';
            } else if (password.length >= 6 &&
                       /[A-Z]/.test(password) &&
                       /[a-z]/.test(password) &&
                       /[0-9]/.test(password)) {
                strength = 'Medium';
            }
            return strength;
        }
    });
</script>

</body>
</html>

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/PHPMailer/src/Exception.php';
require '../PHPMailer/PHPMailer/src/PHPMailer.php';
require '../PHPMailer/PHPMailer/src/SMTP.php';

session_start();

// Database connection (update with your credentials)
include '../includes/db_connect.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if the email exists
    $stmt = $conn->prepare("SELECT cust_id FROM Customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $stmt->close();

    if ($user_id) {
        // Generate a random verification code
        $code = rand(100000, 999999);

        // Save the verification code to the database
        $stmt = $conn->prepare("INSERT INTO password_resets (email, code) VALUES (?, ?) ON DUPLICATE KEY UPDATE code = ?");
        $stmt->bind_param("ssi", $email, $code, $code);
        $stmt->execute();
        $stmt->close();

        // Send the verification code to the user's email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Set the SMTP server to send through
            $mail->SMTPAuth   = true;
            $mail->Username   = 'arieldohinogbusiness@gmail.com'; // SMTP username
            $mail->Password   = 'lystrtavajrupmnq'; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            //Recipients
            $mail->setFrom('no-reply@meat-to-door.com', 'Meat-To-Door Delivery');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Verification Code';
            $mail->Body    = 'Your verification code is: ' . $code;

            $mail->send();
            $_SESSION['email'] = $email;
            header('Location: verify-code.php');
            exit();
        } catch (Exception $e) {
            $error = "Failed to send verification code. Please try again. Error: " . $mail->ErrorInfo;
        }
    } else {
        $error = "Email not found.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Meat-To-Door Delivery</title>
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
    </style>
</head>
<body>

    <div class="background-animation"></div>
    <div class="container login-container">
        <div class="login-header">
            <img src="../img/logo.ico" alt="Meat-To-Door Logo">
            <h1>Forgot Password</h1>
            <p>Enter your email address to receive a verification code</p>
        </div>
        <form class="login-form" action="send-reset-email.php" method="post">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert" id="errorAlert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Send Verification Code</button>
        </form>
        <div class="login-footer">
            <p>Remembered your password? <a href="login.php">Login here</a></p>
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
                    }, 1000); // Time to complete fade out effect
                }, 5000); // Time to show error (5 seconds)
            }
        });
    </script>
</body>
</html>

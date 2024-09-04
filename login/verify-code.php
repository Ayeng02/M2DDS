<?php
session_start();


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/PHPMailer/src/Exception.php';
require '../PHPMailer/PHPMailer/src/PHPMailer.php';
require '../PHPMailer/PHPMailer/src/SMTP.php';

// Database connection (update with your credentials)
include '../includes/db_connect.php';

if (isset($_SESSION['verified']) && $_SESSION['verified'] === true) {
    // Redirect to another page, e.g., login page or home page
    header('Location: reset-password.php');
    exit();
}

// Function to delete expired verification codes
function cleanUpExpiredCodes($conn) {
    $stmt = $conn->prepare("DELETE FROM password_resets WHERE created_at < NOW() - INTERVAL 1 MINUTE");
    $stmt->execute();
    $stmt->close();
}


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['email'] ?? '';

       // Clean up expired codes
       cleanUpExpiredCodes($conn);

    if (isset($_POST['resend_code'])) {
        // Resend code logic
        generateAndSendCode($email, $conn);
        header('Location: verify-code.php'); // Redirect to refresh the page
        exit();
    } else {
        $code = $_POST['code'];

        // Check if the code matches and is within the valid time frame
        $stmt = $conn->prepare("SELECT id FROM password_resets WHERE email = ? AND code = ? AND created_at >= NOW() - INTERVAL 1 MINUTE");
        $stmt->bind_param("si", $email, $code);
        $stmt->execute();
        $stmt->bind_result($reset_id);
        $stmt->fetch();
        $stmt->close();

        if ($reset_id) {
            // Code is correct, proceed to reset password
            $_SESSION['verified'] = true;
            header('Location: reset-password.php');
            exit();
        } else {
            $error = "Invalid or expired verification code.";
        }
    }
}

// Function to send verification code using PHPMailer
function sendVerificationCode($email, $code) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Set the SMTP server to send through
        $mail->SMTPAuth   = true;
        $mail->Username   = 'arieldohinogbusiness@gmail.com'; // SMTP username
        $mail->Password   = 'lystrtavajrupmnq'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('no-reply@meat-to-door.com', 'Meat-To-Door Delivery');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Verification Code';
        $mail->Body    = 'Your verification code is: ' . $code;

        $mail->send();
    } catch (Exception $e) {
        // Handle error if email sending fails
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Generate and send the code when needed
function generateAndSendCode($email, $conn) {
    $code = rand(100000, 999999); // Generate a 6-digit code

    // Insert or update the code in the database
    $stmt = $conn->prepare("REPLACE INTO password_resets (email, code, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("si", $email, $code);
    $stmt->execute();
    $stmt->close();

    // Send the code via email using PHPMailer
    sendVerificationCode($email, $code);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code - Meat-To-Door Delivery</title>
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
 

    <div class="background-animation"></div>
    <div class="container login-container">
    <div class="login-header">
        <img src="../img/logo.ico" alt="Meat-To-Door Logo">
        <h1>Verify Code</h1>
        <p>Enter the verification code sent to your email</p>
    </div>
    <form class="login-form" action="verify-code.php" method="post">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert" id="errorAlert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <div class="form-group">
            <label for="code">Verification Code</label>
            <input type="text" class="form-control" id="code" name="code" placeholder="Enter the verification code" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Verify Code</button>
    </form>
    <div class="countdown-timer">
        <p id="countdown">You can request a new code in <span id="timer">1:00</span></p>
        <div id="expiredMessage" style="display: none;">
            <p class="alert alert-danger">The verification code has expired. Please request a new code.</p>
        </div>
    </div>
    <form class="resend-form" action="verify-code.php" method="post">
        <button type="submit" class="btn btn-secondary btn-block" name="resend_code" id="resendButton" style="display: none;">Resend Code</button>
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
                    }); // Time to complete fade out effect
                }, 5000); // Time to show error (5 seconds)
            }


            // Countdown timer logic
        var countdownElement = document.getElementById('timer');
        var resendButton = document.getElementById('resendButton');
        var expiredMessage = document.getElementById('expiredMessage');
        var endTime = new Date().getTime() + 60000; // 1 minute from now

        function updateTimer() {
            var now = new Date().getTime();
            var distance = endTime - now;

            if (distance < 0) {
                countdownElement.innerHTML = "00:00";
                expiredMessage.style.display = 'block'; // Show expired message
                resendButton.style.display = 'block'; // Show resend button
                clearInterval(timerInterval);
                return;
            }

            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            minutes = (minutes < 10) ? "0" + minutes : minutes;
            seconds = (seconds < 10) ? "0" + seconds : seconds;

            countdownElement.innerHTML = minutes + ":" + seconds;
        }

        var timerInterval = setInterval(updateTimer, 1000);

        // Hide resend button initially
        resendButton.style.display = 'none';
        expiredMessage.style.display = 'none';
        });

        
    </script>
</body>
</html>

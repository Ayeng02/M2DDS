<?php
session_start();
include '../includes/db_connect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/PHPMailer/src/Exception.php';
require '../PHPMailer/PHPMailer/src/PHPMailer.php';
require '../PHPMailer/PHPMailer/src/SMTP.php';

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';
$redirect = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['resend_code'])) {
        // Generate a new code
        $verification_code = rand(100000, 999999);
        $email = $_SESSION['email'];

        // Update or insert the new code into the database
        $stmt = $conn->prepare("REPLACE INTO password_resets (email, code, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $email, $verification_code);
        $stmt->execute();
        $stmt->close();

        // Send the new code via email using PHPMailer
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

            $mail->setFrom('arieldohinogbusiness@gmail.com', 'Meat-To-Door');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Your Verification Code';
            $mail->Body = "Your verification code is: $verification_code";

            $mail->send();
            $success = 'A new verification code has been sent to your email.';
        } catch (Exception $e) {
            $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $verification_code = trim($_POST['verification_code']);
        $email = $_SESSION['email'] ?? '';

        if (!empty($verification_code)) {
            $stmt = $conn->prepare("SELECT code FROM password_resets WHERE email = ? AND created_at >= NOW() - INTERVAL 1 MINUTE");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($stored_code);
            $stmt->fetch();
            $stmt->close();

            // Trim and convert to uppercase for case-insensitive comparison
            $stored_code = trim($stored_code);
            $verification_code = trim($verification_code);

            if (strcasecmp($stored_code, $verification_code) === 0) {
                // Verification code is correct, complete registration
                $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->close();

                // Retrieve user details from session
                $f_name = $_SESSION['f_name'];
                $l_name = $_SESSION['l_name'];
                $username = $_SESSION['username'];
                $add_ress = $_SESSION['add_ress'];
                $phone_num = $_SESSION['phone_num'];
                $cust_pass = $_SESSION['cust_pass'];

                // Encrypt password
                $encrypted_pass = password_hash($cust_pass, PASSWORD_BCRYPT);

                // Call stored procedure
                $stmt = $conn->prepare("CALL InsertCustomer(?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssss", $f_name, $l_name, $username, $add_ress, $email, $phone_num, $encrypted_pass);

                if ($stmt->execute()) {
                    // Destroy and unset session data
                    unset($_SESSION['f_name']);
                    unset($_SESSION['l_name']);
                    unset($_SESSION['username']);
                    unset($_SESSION['add_ress']);
                    unset($_SESSION['phone_num']);
                    unset($_SESSION['cust_pass']);
                    unset($_SESSION['email']);

                    // Set success message and enable redirect
                    $success = 'Registration successful! Redirecting to login page in 5 seconds...';
                    $redirect = true;
                } else {
                    $error = 'Failed to complete registration. Please try again.';
                }
                $stmt->close();
            } else {
                $error = 'Invalid verification code or code has expired.';
            }
        } else {
            $error = 'Verification code is required.';
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .verification-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.7);
        }
        .verification-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .verification-header img {
            width: 100px;
        }
        .verification-footer {
            text-align: center;
            margin-top: 20px;
        }
        
        .btn1 {
            background-color: #FF8225;
            border-color: #FF8225;
        }
        .btn1:hover {
            background-color: #e36f10;
            border-color: #e36f10;
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
    </style>
</head>
<body>
<div class="background-animation"></div>
<div class="container verification-container">
        <div class="verification-header">
            <img src="../img/mtdd_logo.png" alt="Meat-To-Door Logo" class="img-fluid">
            <h1>Verify Your Email</h1>
            <p>Please enter the verification code sent to your email address.</p>
            <?php if ($redirect): ?>
                <p id="redirect-timer" class="text-success"></p>
            <?php else: ?>
                <p id="timer" class="text-danger"></p>
            <?php endif; ?>
        </div>
        <form class="verification-form" action="reg-authentication.php" method="post">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="code">Verification Code</label>
                <input type="text" class="form-control" id="code" name="verification_code" placeholder="Enter your verification code" required>
            </div>
            <button type="submit" id="btn-code" class="btn btn-primary btn-block btn1">Verify Code</button>
            <button type="submit" class="btn btn-secondary btn-block mt-2 btn1" id="resend-btn" name="resend_code">Resend Code</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        <?php if ($redirect): ?>
        // Countdown Timer for Redirection
        let redirectTimeLeft = 5;
        const redirectTimerDisplay = document.getElementById('redirect-timer');

        const redirectCountdown = setInterval(() => {
            if (redirectTimeLeft <= 0) {
                clearInterval(redirectCountdown);
                window.location.href = 'login.php'; // Redirect to login page
            } else {
                redirectTimerDisplay.innerHTML = `Redirecting in ${redirectTimeLeft} seconds...`;
            }
            redirectTimeLeft -= 1;
        }, 1000);

        <?php else: ?>
        // Countdown Timer for Code Expiration
        let timeLeft = 60;
        const timerDisplay = document.getElementById('timer');
        const resendBtn = document.getElementById('resend-btn');
        const codeInput = document.getElementById('code');
        const codeBtn = document.getElementById('btn-code');

        const countdown = setInterval(() => {
            if (timeLeft <= 0) {
                clearInterval(countdown);
                timerDisplay.innerHTML = "Code expired. Please resend the code.";
                codeInput.disabled = true;
                codeBtn.hidden = true;
                resendBtn.disabled = false;
            } else {
                timerDisplay.innerHTML = `Code expires in ${timeLeft} seconds`;
            }
            timeLeft -= 1;
        }, 1000);

        // Resend button should be disabled initially
        resendBtn.disabled = true;
        <?php endif; ?>
    </script>
</body>
</html>

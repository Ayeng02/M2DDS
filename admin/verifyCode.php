
<?php 
ob_start();
session_start();
include '../includes/db_connect.php';

// Redirect to landing page if already logged in
if (isset($_SESSION['EmpLogExist']) && $_SESSION['EmpLogExist'] === true || isset($_SESSION['AdminLogExist']) && $_SESSION['AdminLogExist'] === true) {


    if (isset($_SESSION['emp_role'])) {
        // Redirect based on employee role
        switch ($_SESSION['emp_role']) {
            case 'Shipper':
                header("Location: ../shipper/shipper.php");
                exit;
            case 'Order Manager':
                header("Location: ../ordr_manager/order_manager.php");
                exit;
            case 'Cashier':
                header("Location: ../cashier/cashier.php");
                exit;
                break;
            default:
        }
    }
} else {
    header("Location: ../login.php");
    exit;
}
?>
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/PHPMailer/src/Exception.php';
require '../PHPMailer/PHPMailer/src/PHPMailer.php';
require '../PHPMailer/PHPMailer/src/SMTP.php';

// Database connection (update with your credentials)
include '../includes/db_connect.php';

if (isset($_SESSION['verified']) && $_SESSION['verified'] === true) {
    // Redirect to another page, e.g., login page or home page
    header('Location: new-password.php');
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
        header('Location: verifyCode.php'); // Redirect to refresh the page
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
            header('Location: new-password.php');
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
    <title>Admin Interface</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
     <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <!-- Custom CSS -->
    <style>
        body {
            overflow-x: hidden;
            background-color: #f8f9fa;
        }


#sidebar-wrapper .sidebar-heading .sidebar-title {
    font-size: 1.5rem;
    display: inline;
}
        #wrapper {
            display: flex;
            width: 100%;
            height: 100%; /* Full viewport height */
        }
        #sidebar-wrapper {
    min-height: 100vh;
    width: 80px; /* Default width for icons only */
    background-color: #a72828;
    color: #fff;
    transition: width 0.3s ease;
    overflow-y: auto; /* Allow vertical scrolling */
    position: relative;
    overflow-x: hidden; /* Prevent horizontal scrolling */
    border-right: 1px solid #ddd; /* Light border to separate from content */
    box-shadow: 2px 0 5px rgba(0,0,0,0.1); /* Subtle shadow */
  
}
#sidebar-wrapper.expanded {
    width: 250px; /* Expanded width */
}
#sidebar-wrapper .sidebar-heading {
    padding: 1rem;
    display: flex;
    align-items: center;
    background-color: #FF8225;
    color: #fff;
    border-bottom: 1px solid #ddd; /* Border for separation */
}
#sidebar-wrapper .logo-img {
    width: 40px; /* Adjust size as needed */
    height: 40px;
    margin-right: 10px; /* Space between logo and text */
}
#sidebar-wrapper .sidebar-title {
    font-size: 1.5rem;
    display: inline; /* Ensure title is always visible */
}
        #sidebar-wrapper .list-group {
            width: 100%;
        }
        #sidebar-wrapper .list-group-item {
            background-color: #a72828;
            color: #fff;
            border: none;
            padding: 1rem;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            border-radius: 0; /* Remove default border radius */
            transition: background-color 0.2s ease; /* Smooth hover effect */
        }
        #sidebar-wrapper .list-group-item i {
            font-size: 1.5rem;
            margin-right: 15px;
        }
        #sidebar-wrapper .list-group-item span {
    display: none; /* Hide text in default state */
    margin-left: 10px;
    white-space: nowrap; /* Prevent text wrapping */
}
#sidebar-wrapper.expanded .list-group-item span {
    display: inline; /* Show text in expanded state */
}
        #sidebar-wrapper .list-group-item:hover {
            background-color: #8c1c1c; /* Darker color on hover */
        }
        #sidebar-wrapper .toggle-btn {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #FF8225;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 5px rgba(0,0,0,0.2); /* Button shadow */
        }
        #sidebar-wrapper .toggle-btn:hover {
            background-color: #a72828;
        }
        #page-content-wrapper {
            flex: 1;
            padding: 20px;
            transition: margin-left 0.3s ease;
            background-color: #f8f9fa; /* Slightly different background */
        }
        #page-content-wrapper.sidebar-expanded {
            margin-left:0px; /* Match the expanded sidebar width */
        }
        .navbar-light {
            background-color: #FF8225;
        }
        .navbar-light .navbar-nav .nav-link {
            color: black;
            
            
        }
        .navbar-light .navbar-nav .nav-link:hover {
            color: #a72828;
        }
        
        /* Hide sidebar heading text when collapsed */
#sidebar-wrapper:not(.expanded) .sidebar-title {
    display: none;
}

#sidebar-wrapper:not(.expanded) .logo-img {
    width: 30px; /* Adjust size when collapsed */
    height: 30px;
}
.profile-card {
            max-width: 50%;
         font-family: Arial, sans-serif;
            margin: 50px auto;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            background-color: #fff;
        }
 .strength-meter {
            margin-top: 10px;
            height: 5px;
            width: 100%;
            border-radius: 3px;
        }
        .strength-meter-weak {
            background-color: red;
        }
        .strength-meter-medium {
            background-color: orange;
        }
        .strength-meter-strong {
            background-color: green;
        }
        #password-feedback {
            font-size: 18px;
            margin-top: 5px;
        }
        #password-feedback.weak {
            color: red;
            
        }
        #password-feedback.medium {
            color: orange;
        }
        #password-feedback.strong {
            color: green;
        }
         .input-group {
            position: relative;
        }
        .input-group-append {
            position: absolute;
            right: 10px;
            top: 10px;
            cursor: pointer;
        }
        label{
            margin-right: 10px;
        }
        .info-card p {
            margin: 10px 0;
            font-size: 20px;
            color: #333;
        }
         .info-card span {
            color: gray;
        }

        /* Bold and colorize field labels (e.g., "Full Name:") */
        .info-card strong {
            font-weight: 600;
            color: #555;
        }
        .text-center{
            color: #a72828;
            font-weight: bold;
            font-size: 40px;
        }
    </style>
</head>
<body>

<div class="d-flex" id="wrapper">
<?php 
include '../includes/sidebar.php';
?>

    <!-- Page Content -->
    <div id="page-content-wrapper">
         <?php 
        include '../includes/admin-navbar.php';
        ?>  
       <?php
        $admin_id = $_SESSION['admin_id'];
        $sql = "SELECT admin_name, admin_email, admin_num, admin_role FROM admin_tbl WHERE admin_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $stmt->bind_result($name,$email, $contact, $admin_role);
        $stmt->fetch();
        $stmt->close();
        $_SESSION['admin_role'] = $admin_role;

// Check for alerts after the form processing
if (isset($_SESSION['alert'])) {
    $alert = $_SESSION['alert'];
    echo '<script>
            Swal.fire({
                icon: "' . $alert['icon'] . '",
                title: "' . $alert['title'] . '",
                showConfirmButton: true
            });
          </script>';
    unset($_SESSION['alert']); // Clear the alert so it doesn't show again
}
ob_end_flush(); 
?>
 <div class="container">
        <div class="profile-card">
           
    
            <h2 class="text-center">Verify Code</h2>
            <form class="login-form" action="verifyCode.php" method="post">
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
    <form class="resend-form" action="verifyCode.php" method="post">
        <button type="submit" class="btn btn-secondary btn-block" name="resend_code" id="resendButton" style="display: none;">Resend Code</button>
    </form>
        </div>
  
        <?php
        // Check if the 'success' parameter exists in the URL

        if (isset($_SESSION['success'])) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Admin Added',
                        text: '" . $_SESSION['success'] . "',
                        confirmButtonText: 'OK'
                    });
                });
            </script>";
            unset($_SESSION['success']);
        }
        ?>
   
    </div>
    <!-- /#page-content-wrapper -->
</div>
</div>
<!-- /#wrapper -->

<!-- Bootstrap and JavaScript -->
 <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
 

<script>
// Toggle sidebar
$("#menu-toggle, #menu-toggle-top").click(function(e) {
    e.preventDefault();
    $("#sidebar-wrapper").toggleClass("expanded");
    $("#page-content-wrapper").toggleClass("sidebar-expanded");
    // Change icon on toggle
    let icon = $("#sidebar-wrapper .toggle-btn i");
    if ($("#sidebar-wrapper").hasClass("expanded")) {
        icon.removeClass("fa-chevron-right").addClass("fa-chevron-left");
    } else {
        icon.removeClass("fa-chevron-left").addClass("fa-chevron-right");
    }
});

</script>
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
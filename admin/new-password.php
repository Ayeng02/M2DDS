
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
        $stmt = $conn->prepare("UPDATE admin_tbl SET admin_pass = ? WHERE admin_email = ?");
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
        header('Location: change-success.php');
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
             <h2 class="text-center">Enter New Password</h2>
             <div class="login-header">
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
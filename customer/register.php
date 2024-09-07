<?php
session_start();
include '../includes/db_connect.php';


if (isset($_SESSION['email'])) {
    header('Location: reg-authentication.php');
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/PHPMailer/src/Exception.php';
require '../PHPMailer/PHPMailer/src/PHPMailer.php';
require '../PHPMailer/PHPMailer/src/SMTP.php';

// Encryption key (must be kept secret and secure)
$encryption_key = 'your-secret-key';

// Helper functions
function validatePhoneNumber($phone) {
    return preg_match('/^(09\d{9})$/', $phone);
}

function validateAddress($address) {
    return preg_match('/^[\w\s\.,\-#]+, [\w\s]+, [\w\s]+$/', $address);
}

function validateUsername($username) {
    return preg_match('/^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z\d]{6,}$/', $username);
}

function validateName($name) {
    return preg_match('/^[A-Z][a-zA-Z\s]*$/', $name);
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

$error = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $f_name = trim($_POST['f_name']);
    $l_name = trim($_POST['l_name']);
    $username = trim($_POST['username']);
    $add_ress = trim($_POST['add_ress']);
    $email = trim($_POST['email']);
    $phone_num = trim($_POST['phone_num']);
    $cust_pass = $_POST['cust_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    if (!empty($f_name) && !empty($l_name) && !empty($username) && !empty($add_ress) && !empty($email) && !empty($phone_num) && !empty($cust_pass) && !empty($confirm_pass)) {
        if (validateName($f_name) && validateName($l_name)) {
            if (validateEmail($email)) {
                if ($cust_pass === $confirm_pass) {
                    if (strlen($cust_pass) >= 8 && preg_match('/[A-Z]/', $cust_pass) && preg_match('/[a-z]/', $cust_pass) && preg_match('/\d/', $cust_pass) && preg_match('/[\W_]/', $cust_pass)) {
                        if (validateUsername($username)) {
                            if (validatePhoneNumber($phone_num)) {
                                if (validateAddress($add_ress)) {
                                    // Check if email or username already exists
                                    $stmt = $conn->prepare("SELECT COUNT(*) FROM customers WHERE email = ? OR username = ?");
                                    $stmt->bind_param("ss", $email, $username);
                                    $stmt->execute();
                                    $stmt->bind_result($count);
                                    $stmt->fetch();
                                    $stmt->close();

                                    if ($count == 0) {
                                        // Generate a random verification code
                                        $verification_code = rand(100000, 999999);

                                        // Save the verification code to the database
                                        $stmt = $conn->prepare("INSERT INTO password_resets (email, code, created_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE code = ?, created_at = NOW()");
                                        $stmt->bind_param("ssi", $email, $verification_code, $verification_code);
                                        $stmt->execute();
                                        $stmt->close();

                                        // Send the verification code to the user's email using PHPMailer
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

                                            $mail->setFrom('no-reply@yourdomain.com', 'Your Website Name');
                                            $mail->addAddress($email);

                                            $mail->isHTML(true);
                                            $mail->Subject = 'Email Verification Code';
                                            $mail->Body    = 'Your verification code is: <b>' . $verification_code . '</b>';

                                            $mail->send();
                                            $_SESSION['email'] = $email;
                                            $_SESSION['f_name'] = $f_name;
                                            $_SESSION['l_name'] = $l_name;
                                            $_SESSION['username'] = $username;
                                            $_SESSION['add_ress'] = $add_ress;
                                            $_SESSION['phone_num'] = $phone_num;
                                            $_SESSION['cust_pass'] = $cust_pass;
                                            header('Location: reg-authentication.php');
                                            exit();
                                        } catch (Exception $e) {
                                            $error = "Failed to send verification code. Please try again. Error: " . $mail->ErrorInfo;
                                        }
                                    } else {
                                        $error = 'Email or username already exists.';
                                    }
                                } else {
                                    $error = 'Address format is invalid. Please use a more flexible format. (Purok-5 Apokon, Tagum, Davao del Norte)';
                                }
                            } else {
                                $error = 'Phone number must be in Philippine cellular format (09xxxxxxxxx).';
                            }
                        } else {
                            $error = 'Username must be at least 6 characters long and include both letters and numbers.';
                        }
                    } else {
                        $error = 'Password must be at least 8 characters long, include an uppercase letter, a lowercase letter, a number, and a special character.';
                    }
                } else {
                    $error = 'Passwords do not match.';
                }
            } else {
                $error = 'Invalid email format.';
            }
        } else {
            $error = 'First name and last name must start with a capital letter and contain only letters.';
        }
    } else {
        $error = 'All fields are required.';
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Meat-To-Door Delivery</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="./css/home.css">
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">
    <style>
        body {
            justify-content: center;
            display: flex;
        }

        .background-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 130%;
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

        .register-container {
            width: 100%;
            max-width: 700px;
            padding: 2rem;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: absolute;
            z-index: 1;
            overflow: hidden;
            margin-top: 70px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .register-header img {
            max-width: 100%;
            height: auto;
            margin-bottom: 1rem;
        }

        .register-header h1 {
            font-size: 1.75rem;
            color: #333;
            margin: 0;
        }

        .register-header p {
            color: #6c757d;
            margin: 0;
            font-size: 1rem;
        }

        .register-form .form-group {
            margin-bottom: 1.5rem;
        }

        .register-form .form-control {
            border-radius: 0.25rem;
            border: 1px solid #ced4da;
            padding: 0.75rem;
            font-size: 1rem;
        }

        .register-form .btn {
            border-radius: 0.25rem;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
        }

        .register-footer {
            text-align: center;
            margin-top: 1.5rem;
        }

        .register-footer a {
            color: #007bff;
            text-decoration: none;
        }

        .register-footer a:hover {
            text-decoration: underline;
        }

        .alert {
            display: none;
        }

        .img-fluid {
            width: 25%;
            height: auto;
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
                <li class="nav-item">
                    <a class="nav-link" href="login.php">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="register.php">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="background-animation"></div>
    <div class="container register-container">
        <div class="register-header">
            <img src="../img/logo.ico" alt="Meat-To-Door Logo" class="img-fluid">
            <p>Where Quality Meets Affordability</p>
            <h1 style="padding: 20px;">Register</h1>
        </div>
        <form class="register-form" action="register.php" method="post">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert" id="errorAlert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="f_name">First Name</label>
                    <input type="text" class="form-control" id="f_name" name="f_name" placeholder="Enter your first name" required oninput="validateName(this)">
                </div>
                <div class="form-group col-md-6">
                    <label for="l_name">Last Name</label>
                    <input type="text" class="form-control" id="l_name" name="l_name" placeholder="Enter your last name" required oninput="validateName(this)">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="email">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="phone_num">Phone Number</label>
                    <input type="text" class="form-control" id="phone_num" name="phone_num" placeholder="Enter your phone number" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="add_ress">Address</label>
                    <input type="text" class="form-control" id="add_ress" name="add_ress" placeholder="Enter your address" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="cust_pass">Password</label>
                    <input type="password" class="form-control" id="cust_pass" name="cust_pass" placeholder="Enter your password" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="confirm_pass">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_pass" name="confirm_pass" placeholder="Confirm your password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>
        <div class="register-footer">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($success): ?>
                Swal.fire({
                    title: 'Success!',
                    text: 'Registration successful. You will be redirected to the login page.',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = 'login.php';
                });
            <?php elseif ($error): ?>
                var errorAlert = document.getElementById('errorAlert');
                if (errorAlert) {
                    errorAlert.style.display = 'block';
                    setTimeout(function() {
                        errorAlert.style.opacity = '0';
                        setTimeout(function() {
                            errorAlert.style.display = 'none';
                            errorAlert.style.opacity = '1';
                        }); // Time to complete fade out effect
                    }, 5000); // Time to show error (2 seconds)
                }
            <?php endif; ?>
        });

        function validateName(input) {
        // Remove numbers and keep letters only
        input.value = input.value.replace(/[^a-zA-Z\s]/g, '');

        // Capitalize the first letter of each word
        input.value = input.value.replace(/\b\w/g, function(char) {
            return char.toUpperCase();
        });
    }
    </script>
</body>

</html>
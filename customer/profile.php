<?php
// Start the session
session_start();

// Set error reporting to ignore notices
error_reporting(E_ALL & ~E_NOTICE);

// Include your database connection script
include '../includes/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['cust_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch the customer's current information
$cust_id = $_SESSION['cust_id'];
$sql = "SELECT f_name, l_name, username, address, email, phone_num FROM Customers WHERE cust_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $cust_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../css/home.css">
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">
    <style>
        body {
            background-color: #f7f7f7;
        }

        .navbar {
            top: 0px;
        }

        .card {
            background: linear-gradient(145deg, #ffffff, #e6e6e6);
            box-shadow: 7px 7px 14px #cccccc, -7px -7px 14px #ffffff;
            border-radius: 15px;
            margin-bottom: 100px;
        }

        .card h2 {
            color: #a72828;
        }

        .form-group label {
            color: #FF8225;
            font-weight: bold;
        }

        .form-control {
            border-radius: 10px;
            box-shadow: inset 5px 5px 10px #e0e0e0, inset -5px -5px 10px #ffffff;
        }

        .form-control:focus {
            border-color: #FF8225;
            box-shadow: 0 0 5px rgba(255, 130, 37, 0.8);
        }

        .btn-primary {
            background-color: #a72828;
            border: none;
            border-radius: 50px;
            padding: 10px 20px;
            font-weight: bold;
            transition: all 0.3s ease-in-out;
        }

        .btn-primary:hover {
            background-color: #FF8225;
            box-shadow: 0 0 15px rgba(255, 130, 37, 0.8);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .form-control.is-valid {
            border-color: #28a745;
        }

        #responseMessage {
            margin-top: 20px;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 0.3em;
        }

        .navbar-light .navbar-nav .nav-link.act4 {
            color: #ffffff;
        }
    </style>
</head>

<body>

    <?php include '../includes/header.php'; ?>

    <div class="container mt-5">
        <div class="card p-4" style="margin-top: 100px;">
            <h2 class="mb-4 text-center"><i class="fa fa-user-circle"></i> Profile Information</h2>
            <div id="responseMessage"></div>
            <form id="profileForm">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="f_name">First Name</label>
                            <input type="text" class="form-control" id="f_name" name="f_name" value="<?php echo $customer['f_name']; ?>" required oninput="validateName(this)">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="l_name">Last Name</label>
                            <input type="text" class="form-control" id="l_name" name="l_name" value="<?php echo $customer['l_name']; ?>" required  oninput="validateName(this)">
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo $customer['username']; ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label for="add_ress">Address</label>
                    <input type="text" class="form-control" id="add_ress" name="add_ress" value="<?php echo $customer['address']; ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $customer['email']; ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label for="phone_num">Phone Number</label>
                    <input type="text" class="form-control" id="phone_num" name="phone_num" value="<?php echo $customer['phone_num']; ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Save Changes
                </button>
                <div id="loadingSpinner" class="text-center mt-3" style="display: none;">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        $(document).ready(function() {
            // Real-time validation for email field
            $('#email').on('input', function() {
                const email = $(this).val();
                if (!validateEmail(email)) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });

            // Real-time validation for phone number
            $('#phone_num').on('input', function() {
                const phone = $(this).val();
                if (!validatePhoneNumber(phone)) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });

            // Real-time validation for address
            $('#add_ress').on('input', function() {
                const address = $(this).val();
                if (!validateAddress(address)) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });

            // Real-time validation for username
            $('#username').on('input', function() {
                const username = $(this).val();
                if (!validateUsername(username)) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });

            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(String(email).toLowerCase());
            }

            function validatePhoneNumber(phone) {
                return /^(09\d{9})$/.test(phone);
            }

            function validateAddress(address) {
                return /^[\w\s\.,\-#]+, [\w\s]+, [\w\s]+$/.test(address);
            }

            function validateUsername(username) {
                return /^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z\d]{6,}$/.test(username);
            }

            // Ajax form submission
            $('#profileForm').on('submit', function(e) {
                e.preventDefault();

                // Show loading spinner
                $('#loadingSpinner').show();

                $.ajax({
                    type: 'POST',
                    url: 'update_profile.php',
                    data: $(this).serialize(),
                    success: function(response) {
                        const data = JSON.parse(response);
                        if (data.success) {
                            $('#responseMessage').html('<div class="alert alert-success animate__animated animate__fadeIn" role="alert">' + data.message + '</div>');
                        } else {
                            $('#responseMessage').html('<div class="alert alert-danger animate__animated animate__fadeIn" role="alert">' + data.message + '</div>');
                        }
                    },
                    error: function() {
                        $('#responseMessage').html('<div class="alert alert-danger animate__animated animate__fadeIn" role="alert">Failed to update profile.</div>');
                    },
                    complete: function() {
                        // Hide loading spinner
                        $('#loadingSpinner').hide();
                    }
                });
            });
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="./js/notif.js"></script>
</body>

</html>
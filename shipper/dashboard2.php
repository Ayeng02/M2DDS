<?php
session_start();

// Handle logout if the logout link is clicked
if (isset($_GET['logout'])) {
    session_unset(); // Remove all session variables
    session_destroy(); // Destroy the session
    header("Location: login.php"); // Redirect to the login page
    exit();
}

// Database connection
include '../includes/db_connect.php';

// Close the database connection
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.min.css">
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
            background-color: mistyrose;
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
            background-color: #ffffff;
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
            background-color: darksalmon;
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

        .swal2-styled {
            padding: 1.5rem !important; /* Add padding to make it look more like Bootstrap modals */
            border-radius: 0.3rem; /* Match Bootstrap's default modal border-radius */
            font-size: 1.1rem; /* Adjust font size for better readability */
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="#">
            <img class="logo2" src="../img/mtdd_logo.png" alt="Meat-To-Door Logo">
            Meat-to-Door
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="#">
                        <i class="fas fa-home"> HOME</i> <span class="sr-only">(current)</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-user-plus"> PROFILE</i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fa-solid fa-cart-plus"> ORDERS</i>
                    </a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link" href="#">
                        <i class="fa-solid fa-location-dot"> MAP</i>
                    </a>
                </li>
                <li class="nav-item">
                    <!-- logout button -->
                    <a class="nav-link" href="#" id="logoutBtn">
                        <i class="fas fa-sign-in-alt"> LOG OUT</i>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="background-animation"></div>
    <div class="container login-container">
        <div class="login-header">
            <img src="../img/mtdd_logo.png" alt="Meat-To-Door Logo">
            <p>Where Quality Meets Affordability</p>
        </div>
        <div class="login-footer">
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>
    <script>
        //  logout confirmation
        document.getElementById('logoutBtn').addEventListener('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out of your session!",
                icon: 'warning',
                showCancelButton: true,
                customClass: {
                    confirmButton: 'btn btn-success btn-lg mx-2', 
                    cancelButton: 'btn btn-danger btn-lg mx-2', 
                    popup: 'swal2-styled' 
                },
                buttonsStyling: false, 
                confirmButtonText: '<i class="fa fa-thumbs-up"></i> Yes',
                cancelButtonText: '<i class="fa fa-thumbs-down"></i> Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?logout=true';
                }
            });
        });

        // Show error alert for 5 seconds if it exists
        document.addEventListener('DOMContentLoaded', function () {
            var errorAlert = document.getElementById('errorAlert');
            if (errorAlert) {
                errorAlert.style.display = 'block';
                setTimeout(function () {
                    errorAlert.style.opacity = '0';
                    setTimeout(function () {
                        errorAlert.style.display = 'none';
                        errorAlert.style.opacity = '1';
                    }); 
                }, 5000);
            }
        });
    </script>
</body>

</html>

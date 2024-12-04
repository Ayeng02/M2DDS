<?php
session_start();


// Unset the session variable to prevent multiple redirects
unset($_SESSION['password_reset_success']);
unset($_SESSION['email']); // Unset the email session variable
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Success - Meat-To-Door Delivery</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: linear-gradient(135deg, #f6f9fc, #e9ecef);
            margin: 0;
            font-family: Arial, sans-serif;
            position: relative;
            overflow: hidden;
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
        .container {
            width: 100%;
            max-width: 500px;
            padding: 2rem;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: relative;
            z-index: 1;
        }
        .container h1 {
            font-size: 2rem;
            color: #28a745;
            margin-bottom: 1rem;
        }
        .container p {
            font-size: 1.25rem;
            color: #333;
        }
        .container a {
            color: #007bff;
            text-decoration: none;
        }
        .container a:hover {
            text-decoration: underline;
        }
        .progress-bar-container {
            position: relative;
            margin-top: 1rem;
        }
        .progress-bar {
            height: 5px;
            background: #007bff;
            width: 0%;
            transition: width 1s linear;
        }
        .countdown-timer {
            font-size: 1.25rem;
            color: #333;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="background-animation"></div>
    <div class="container">
        <h1>Password Reset Successful</h1>
        <p>Your password has been successfully reset. You will be redirected to the login page in <span id="countdown">5</span> seconds.</p>
        <div class="progress-bar-container">
            <div class="progress-bar" id="progressBar"></div>
        </div>
        <p class="countdown-timer">Redirecting in <span id="countdown">5</span> seconds...</p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var countdownElement = document.getElementById('countdown');
            var progressBar = document.getElementById('progressBar');
            var countdownTime = 5; // Countdown time in seconds

            function updateCountdown() {
                countdownElement.textContent = countdownTime;
                progressBar.style.width = (100 - (countdownTime * 20)) + '%'; // Adjust progress bar width

                if (countdownTime <= 0) {
                    window.location.href = '../login.php'; // Redirect when countdown is complete
                } else {
                    countdownTime--;
                    setTimeout(updateCountdown, 1000); // Update countdown every second
                }
            }

            updateCountdown(); // Start the countdown
        });
    </script>
</body>
</html>

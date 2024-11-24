<?php
// Manila, Philippine timezone
date_default_timezone_set('Asia/Manila');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
include '../includes/db_connect.php';
session_start();

// Initialize response variables
$response = [
    'success' => false,
    'image' => '',
    'name' => '',
    'role' => '',
    'error' => '',
    'success_message' => ''
];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emp_id = strtoupper(trim($_POST['employeeId']));

    // Use server time instead of fetching time from an external API
    $current_time = new DateTime(); // Get the current server time
    $current_time_str = $current_time->format('H:i:s'); // Get current time in HH:MM:SS format

    // Retrieve logout window from AttendSched_tbl
    $stmt = $conn->prepare("SELECT pm_logout_start, pm_logout_end FROM AttendSched_tbl");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $logout_start = $row['pm_logout_start'];
        $logout_end = $row['pm_logout_end'];

        // Convert logout times to 12-hour format with AM/PM
        $logout_start_time = new DateTime($logout_start);
        $logout_end_time = new DateTime($logout_end);

        // Format the times in 12-hour format with AM/PM
        $logout_start_formatted = $logout_start_time->format('h:i A');
        $logout_end_formatted = $logout_end_time->format('h:i A');
        // Check if the login range spans midnight

        if ($logout_start_time > $logout_end_time) {
            // The login window spans midnight
            if ($current_time_str >= $logout_start && $current_time_str < '23:59:59' || $current_time_str >= '00:00:00' && $current_time_str < $logout_end) {
                // Valid login time
            } else {
                $response['error'] = "Logout is open between $logout_start_formatted and $logout_end_formatted";
                echo json_encode($response);
                exit;
            }
        } else {
            // Normal login window, doesn't span midnight
            if ($current_time_str < $logout_start || $current_time_str >= $logout_end) {
                $response['error'] = "Logout is open between $logout_start_formatted and $logout_end_formatted";
                echo json_encode($response);
                exit;
            }
        }
    } else {
        // Handle case where no schedule is found
        $response['error'] = "Logout schedule not found.";
        echo json_encode($response);
        exit;
    }

    // Check if emp_id exists in emp_tbl
    $empQuery = "SELECT * FROM emp_tbl WHERE emp_id = ?";
    $stmt = $conn->prepare($empQuery);
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    $empResult = $stmt->get_result();

    if ($empResult->num_rows > 0) {
        // Employee exists, check if there is an entry in att_tbl with no time_out
        $attQuery = "SELECT * FROM att_tbl WHERE emp_id = ? AND time_out IS NULL";
        $stmt = $conn->prepare($attQuery);
        $stmt->bind_param("s", $emp_id);
        $stmt->execute();
        $attResult = $stmt->get_result();

        if ($attResult->num_rows > 0) {
            // Employee is logged in, update time_out and show success message
            $updateQuery = "UPDATE att_tbl SET time_out = NOW() WHERE emp_id = ? AND time_out IS NULL";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("s", $emp_id);
            $stmt->execute();

            // Destroy session data for this employee
            if (isset($_SESSION['emp_id']) && $_SESSION['emp_id'] == $emp_id) {
                session_destroy(); 
            }

            // Fetch employee's name and role to display in the info box
            $empData = $empResult->fetch_assoc();
            $response['success'] = true;
            $response['success_message'] = 'Logout successful';
            $response['image'] = $empData['emp_img'] ? '../' . $empData['emp_img'] : '../Shipper_Upload/sample1.png';
            $response['name'] = $empData['emp_fname'] . ' ' . $empData['emp_lname'];
            $response['role'] = $empData['emp_role'];
        } else {
            // Employee is not logged in
            $response['error'] = 'You are not logged in to the system.';
        }
    } else {
        // Employee ID not found
        $response['error'] = 'Employee ID Not Found.';
    }

    echo json_encode($response);
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - Meat-To-Door Delivery</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            position: relative;
            overflow: hidden;
        }

        video {
            position: absolute;
            top: 0;
            left: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -1;
            object-fit: cover;
            opacity: 0.65;
        }

        .container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            width: 100%;
            height: 70%;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        h1.title {
            text-align: center;
            margin-bottom: 25px;
            font-size: 36px;
            color: #FF8225;
            font-weight: bold;
            letter-spacing: 2px;
        }

        form {
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            height: 150px;
        }

        input[type="text"] {
            padding: 12px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 10px;
            transition: border 0.3s, box-shadow 0.3s;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-top: 5px;
        }

        input[type="text"]:focus {
            border-color: #FF8225;
            box-shadow: 0 4px 10px rgba(255, 130, 37, 0.2);
        }

        button {
            background-color: #FF8225;
            color: white;
            cursor: pointer;
            border: none;
            font-weight: bold;
            padding: 12px;
            border-radius: 10px;
            transition: background-color 0.3s, transform 0.3s;
            box-shadow: 0 4px 12px rgba(255, 130, 37, 0.15);
            margin-top: 10px;
        }

        button:hover {
            background-color: #e6741b;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(230, 116, 27, 0.3);
        }

        .choice-container {
            position: absolute;
            bottom: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .choice-button {
            background-color: #FF8225;
            color: white;
            border: none;
            font-weight: bold;
            padding: 10px 15px;
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(255, 130, 37, 0.15);
            transition: background-color 0.3s, transform 0.3s;
            cursor: pointer;
            font-size: 18px;
        }

        .choice-button:hover {
            background-color: #e6741b;
            transform: scale(1.1);
            box-shadow: 0 8px 20px rgba(230, 116, 27, 0.3);
        }

        .choice-menu {
            display: none;
            flex-direction: column;
            align-items: stretch;
            position: absolute;
            bottom: 60px;
            right: 0;
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            padding: 10px;
            width: 200px;
            z-index: 10;
            transition: opacity 0.3s, transform 0.3s;
            opacity: 0;
            transform: translateY(10px);
        }

        .choice-menu.show {
            display: flex;
            opacity: 1;
            transform: translateY(0);
        }

        .choice-menu a {
            text-decoration: none;
            color: black;
            margin: 5px 0;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
            cursor: pointer;
        }

        .choice-menu a:hover {
            background-color: #FF8225;
            color: white;
        }

        .choice-menu i {
            margin-right: 10px;
        }

        #infoBox {
            display: none;
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            /* Set to 100% to fit within the container */
            margin: 15px 0;
            /* Add margin to create space between elements */
            text-align: center;
            transition: opacity 0.5s ease-out, transform 0.5s ease-out;
            font-size: 25px;
            color: #333;
        }

        #employeeImage {
            width: 150px;
            height: 150px;
            margin-bottom: 15px;
            object-fit: cover;
            border-radius: 10px;
        }

        #infoBox.show {
            display: block;
            opacity: 1;
            transform: scale(1);
        }

        #infoBox.hide {
            opacity: 0;
            transform: scale(0.8);
        }

        #employeeName {
            margin-top: 10px;
            font-weight: bold;
        }

        .realTime-container {
            text-align: center;
            padding: 1px;
            width: 100%;
        }

        .clocktitle h3 {
            font-size: 20px;
            font-weight: bold;
            color: #8c1c1c;
        }

        .timeclockcontainer h1 {
            font-size: 23px;
            color:#8c1c1c;
            margin: 10px 0;
            font-weight: bold;
        }

        .timeclockcontainer h5 {
            font-size: 17px;
            color: #666;
        }
    </style>
</head>

<body>

    <!-- Video Background -->
    <video autoplay muted loop>
        <source src="../attendance/sample_only.mp4" type="video/mp4">
    </video>

    <div class="container-fluid">
        <div class="container">
            <h1 class="title">Meat to Door Delivery System Logout</h1>
            <div class="d-grid gap-10 col-14 ms-auto">
                <div class="realTime-container">
                    <div class="timeclockcontainer">
                        <h1 id="current-time">--:--:--</h1>
                        <h5 id="current-date">Loading...</h5>
                    </div>
                    <div class="clockfoot"></div>
                </div>
            </div>

            <div class="card text-bg-light mb-3" id="infoBox">
                <div class="card-body">
                    <img id="employeeImage"></img>
                    <div id="employeeName"></div>
                    <div id="employeeRole"></div>
                </div>
            </div>

            <form id="attendanceForm">
                <input type="text" id="employeeId" name="employeeId" placeholder="Enter ID" required>
                <button type="submit">Logout</button>
            </form>

        </div>
    </div>

    <!-- Choice Menu -->
    <div class="choice-container">
        <button class="choice-button" onclick="toggleMenu()"><i class="fas fa-cogs"></i></button>
        <div id="choiceMenu" class="choice-menu">
            <a href="login.php"><i class="fas fa-sign-out-alt"></i> Login</a>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function toggleMenu() {
            const menu = document.getElementById('choiceMenu');
            menu.classList.toggle('show');
        }

        document.getElementById('attendanceForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(this);
            const container = document.querySelector('.container');

            fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const infoBox = document.getElementById('infoBox');

                    if (data.success) {
                        // Handle success
                        document.getElementById('employeeName').innerText = `${data.name}`;
                        document.getElementById('employeeRole').innerText = `(${data.role})`;
                        document.getElementById('employeeImage').src = data.image;

                        infoBox.style.display = 'flex';

                        setTimeout(() => {
                            infoBox.style.opacity = '0';
                            setTimeout(() => {
                                infoBox.style.display = 'none';
                                infoBox.style.opacity = '1';
                            }, 500);
                        }, 3000);

                        // Reset form
                        document.getElementById('attendanceForm').reset();
                    } else {
                        // Handle error message
                        const errorMessage = data.error ? data.error : "An unknown error occurred.";
                        document.getElementById('employeeName').innerText = errorMessage;
                        document.getElementById('employeeImage').style.display = 'none';
                        document.getElementById('employeeRole').style.display = 'none';

                        infoBox.style.display = 'block';

                        setTimeout(() => {
                            infoBox.style.opacity = '0';
                            setTimeout(() => {
                                infoBox.style.display = 'none';
                                infoBox.style.opacity = '1';
                            }, 500);
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network Error: Please try again later.');
                });
        });

        function updateTime() {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    document.getElementById("current-time").innerHTML = response.time;
                    document.getElementById("current-date").innerHTML = response.date;
                }
            };
            xhr.open("GET", "../admin/get_time.php", true);
            xhr.send();
        }

        // Call updateTime initially and then every second
        updateTime();
        setInterval(updateTime, 1000);
    </script>

</body>

</html>
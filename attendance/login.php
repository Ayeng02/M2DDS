<?php
// Manila, Philippine timezone
date_default_timezone_set('Asia/Manila');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
include '../includes/db_connect.php';

// Initialize response variables
$response = [
    'success' => false,
    'image' => '',
    'name' => '',
    'role' => '',
    'error' => '',
    'success_message' => ''
];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the employee ID from the POST request and sanitize input
    $emp_id = strtoupper(trim($_POST['employeeId'])); // Convert emp_id to uppercase and trim spaces

    // Use server time instead of fetching time from an external API
    $current_time = new DateTime(); // Get the current server time
    $current_time_str = $current_time->format('H:i:s'); // Get current time in HH:MM:SS format
    
    // Retrieve login window from AttendSched_tbl
    $stmt = $conn->prepare("SELECT am_login_start, am_login_end FROM AttendSched_tbl");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $login_start = $row['am_login_start'];
        $login_end = $row['am_login_end'];

        // Convert logout times to 12-hour format with AM/PM
        $login_start_time = new DateTime($login_start);
        $login_end_time = new DateTime($login_end);

          // Format the times in 12-hour format with AM/PM
          $login_start_formatted = $login_start_time->format('h:i A');
          $login_end_formatted = $login_end_time->format('h:i A');
    
        // Check if the current time is within the allowed login window
        if ($current_time_str < $login_start || $current_time_str >= $login_end) {
            // Outside of login hours
            $response['error'] = "Logouts are open between $login_start_formatted and $login_end_formatted";
            echo json_encode($response);
            exit;
        }
    } else {
        // Handle case where no schedule is found
        $response['error'] = "Login schedule not found.";
        echo json_encode($response);
        exit;
    }

    // Proceed with login logic after time window check
    $stmt = $conn->prepare("SELECT * FROM emp_tbl WHERE emp_id = ?");
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($employee = $result->fetch_assoc()) {
        $current_timestamp = date("Y-m-d H:i:s"); // Record current timestamp
        $current_date = date("Y-m-d"); // Current date

        // Check if an attendance record exists for today with no time_out
        $stmt = $conn->prepare("SELECT * FROM att_tbl WHERE emp_id = ? AND att_date = ? AND time_out IS NULL");
        $stmt->bind_param("ss", $emp_id, $current_date);
        $stmt->execute();
        $attendance_result = $stmt->get_result();

        if ($attendance_row = $attendance_result->fetch_assoc()) {
            // Employee has already logged in today and has not logged out yet
            $response['error'] = "You're already logged in. Please log out first.";
        } else {
            // Check if there is a previous record for the same day but with time_out
            $stmt = $conn->prepare("SELECT * FROM att_tbl WHERE emp_id = ? AND att_date = ? AND time_out IS NOT NULL");
            $stmt->bind_param("ss", $emp_id, $current_date);
            $stmt->execute();
            $previous_attendance = $stmt->get_result();

            // Allow login if either no record exists for today, or there is a time_out
            if ($previous_attendance->num_rows > 0 || $attendance_result->num_rows === 0) {
                // Insert new attendance record (time_in)
                $stmt = $conn->prepare("INSERT INTO att_tbl (emp_id, time_in, att_date) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $emp_id, $current_timestamp, $current_date);
                $stmt->execute();

                // Set successful response with employee details
                $response['success'] = true;
                $response['success_message'] = "Login Success";
                $response['image'] = $employee['emp_img'] ? '../' . $employee['emp_img'] : '../Shipper_Upload/sample1.png'; // Default image path if not set
                $response['name'] = $employee['emp_fname'] . ' ' . $employee['emp_lname'];
                $response['role'] = $employee['emp_role'];
            } else {
                // Unexpected error case (shouldn't happen)
                $response['error'] = "Unexpected error. Please try again.";
            }
        }
    } else {
        // Employee not found in emp_tbl
        $response['error'] = "Employee ID Not Found.";
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();

    // Send response back as JSON
    echo json_encode($response);
    exit;
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            height: auto;
            display: flex;
            flex-direction: column;
            position: relative;
            transition: height 0.5s ease-out;
            min-height: 300px;
          
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

        #infoBox.show {
            display: block;
            opacity: 1;
            transform: scale(1);
        }

        #infoBox.hide {
            opacity: 0;
            transform: scale(0.8);
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
            <h1 class="title">Meat to Door Delivery System Login</h1>

            <div class="card text-bg-light mb-3" id="infoBox">
                <div class="card-body">
                    <img id="employeeImage"></img>
                    <div id="employeeName"></div>
                    <div id="employeeRole"></div>
                </div>
            </div>

            <form id="attendanceForm" method="POST">
                <input type="text" id="employeeId" name="employeeId" placeholder="Enter ID" required>
                <button type="submit">Login</button>
            </form>


        </div>
    </div>

    <!-- Choice Menu -->
    <div class="choice-container">
        <button class="choice-button" onclick="toggleMenu()"><i class="fas fa-cogs"></i></button>
        <div id="choiceMenu" class="choice-menu">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
            const container = document.querySelector('.container'); // Reference the container element

            fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const infoBox = document.getElementById('infoBox');

                    if (data.success) {
                        // Update the info box with employee details
                        document.getElementById('employeeName').innerHTML = `${data.name}`;
                        document.getElementById('employeeRole').innerText = `(${data.role})`;
                        document.getElementById('employeeImage').src = data.image;


                        // Show the info box
                        infoBox.style.display = 'flex';

                        // Make the info box disappear after 3 seconds
                        setTimeout(() => {
                            infoBox.style.opacity = '0';
                            setTimeout(() => {
                                infoBox.style.display = 'none'; // Fully hide after fade-out
                                infoBox.style.opacity = '1'; // Reset opacity for future use
                            }, 500); // Wait for fade-out transition to finish
                        }, 3000); // 3 seconds delay

                        // Reset the form
                        document.getElementById('attendanceForm').reset();
                    } else {
                        // Update the info box with an error message
                        document.getElementById('employeeName').innerText = data.error;
                        document.getElementById('employeeImage').style.display = 'none';
                        document.getElementById('employeeRole').style.display = 'none';

                        // Show the info box
                        infoBox.style.display = 'block';

                        // Make the info box disappear after 3 seconds
                        setTimeout(() => {
                            infoBox.style.opacity = '0';
                            setTimeout(() => {
                                infoBox.style.display = 'none'; // Fully hide after fade-out
                                infoBox.style.opacity = '1'; // Reset opacity for future use
                            }, 500); // Wait for fade-out transition to finish
                        }, 3000); // 3 seconds delay
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network Error: Please try again later.');
                });
        });
    </script>

</body>

</html>

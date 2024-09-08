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
    'error' => '',
    'success_message' => ''
];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emp_id = strtoupper(trim($_POST['employeeId'])); // Convert emp_id to uppercase and trim spaces

    // Check if employee exists
    $stmt = $conn->prepare("SELECT * FROM emp_tbl WHERE emp_id = ?");
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($employee = $result->fetch_assoc()) {
        // Employee found, handle attendance record
        $current_date = date("Y-m-d");
        $current_time = date("H:i:s"); // Record current time for time_in
    
        // Check if any attendance record exists for today
        $stmt = $conn->prepare("SELECT * FROM att_tbl WHERE emp_id = ? AND att_date = ? AND time_out IS NULL");
        $stmt->bind_param("ss", $emp_id, $current_date);
        $stmt->execute();
        $attendance_result = $stmt->get_result();
    
        if ($attendance_row = $attendance_result->fetch_assoc()) {
            // Employee has already logged in and not yet logged out
            $response['error'] = "You have already logged in today and have not logged out.";
        } else {
            // Employee has either not logged in today, or has logged out, allow new login
            $stmt = $conn->prepare("INSERT INTO att_tbl (emp_id, time_in, att_date) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $emp_id, $current_time, $current_date);
            $stmt->execute();
    
            // Return employee details in the response
            $response['success'] = true;
            $response['success_message'] = "Login Success";
            $response['image'] = $employee['emp_img'] ? $employee['emp_img'] : 'path/to/default-image.png';
            $response['name'] = $employee['emp_fname'] . ' ' . $employee['emp_lname'];
        }
    } else {
        // Employee not found
        $response['error'] = "Employee ID not found.";
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
    <title>Shipper - Meat-To-Door Delivery</title>
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
            height: 100%;
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

        #employeeInfo img {
            border-radius: 50%;
            width: 100px;
            height: 100px;
        }
        </style>
</head>
<body>

    <!-- Video Background -->
    <video autoplay muted loop>
        <source src="../attendance/sample_only.mp4" type="video/mp4">
    </video>

    <div class="container">
        <h1 class="title">Meat to Door Delivery System Login</h1>

        <form id="attendanceForm" method="POST">
            <input type="text" id="employeeId" name="employeeId" placeholder="Enter ID" required>
            <button type="submit">Login</button>
        </form>
        
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

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: data.success_message,
                        text: `Welcome, ${data.name}!`,
                        imageUrl: data.image,
                        imageWidth: 100,
                        imageHeight: 100,
                        imageAlt: 'Employee Image'
                    }).then(() => {
                        document.getElementById('attendanceForm').reset();
                        document.getElementById('employeeImage').src = data.image;
                        document.getElementById('employeeName').innerText = data.name;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.error
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Please try again later.'
                });
            });
        });
    </script>

</body>
</html>

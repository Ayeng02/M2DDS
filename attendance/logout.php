<?php
// Include database connection
include '../includes/db_connect.php';

// Manila, Philippine timezone
date_default_timezone_set('Asia/Manila');

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emp_id = $_POST['employeeId'];

    // Get the current time
    $current_time = date('H:i'); // e.g., "16:30" for 4:30 PM
    $logout_start = '13:00';     // 4:00 PM
    $logout_end = '17:30';       // 5:30 PM

    // Check if the current time is within the allowed logout window
    if ($current_time < $logout_start || $current_time > $logout_end) {
        $response = [
            'status' => 'error',
            'message' => 'You can only log out between 4:00 PM and 5:30 PM.'
        ];
        echo json_encode($response);
        exit();
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

            // Fetch employee's name and role to display in the info box
            $empData = $empResult->fetch_assoc();
            $response = [
                'status' => 'success',
                'message' => 'Logout successful',
                'image' => $empData['emp_img'] ? '../'. $empData['emp_img'] : '../Shipper_Upload/sample1.png',
                'name' => $empData['emp_fname'] . ' ' . $empData['emp_lname'],
                'role' => $empData['emp_role']
            ];
        } else {
            // Employee is not logged in
            $response = [
                'status' => 'error',
                'message' => 'You are not logged in to the system.'
            ];
        }
    } else {
        // Employee ID not found
        $response = [
            'status' => 'error',
            'message' => 'Employee ID Not Found.'
        ];
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
    <title>Shipper - Meat-To-Door Delivery</title>
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

        #employeeImage {
            width: 200px;

            height: 200px;
            margin-bottom: 15px;

            object-fit: cover;

            border-radius: 10px;

        }

        /* Styles for the information box */
        #infoBox {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            z-index: 100;
            max-width: 400px;
            text-align: center;
            transition: opacity 0.5s ease-out, transform 0.5s ease-out;
            font-size: 25px;
            color: #333;
        }

        #employeeName {
            margin-top: 10px;
            font-weight: bold;
        }


        #infoBox.show {
            display: block;
            opacity: 1;
            transform: translate(-50%, -50%) scale(1.05);
        }

        #infoBox.hide {
            opacity: 0;
            transform: translate(-50%, -50%) scale(0.8);
        }
    </style>
</head>

<body>

    <!-- Video Background -->
    <video autoplay muted loop>
        <source src="../attendance/sample_only.mp4" type="video/mp4">
    </video>

    <div class="container">
        <h1 class="title">Meat to Door Delivery System Logout</h1>

        <form id="attendanceForm">
            <input type="text" id="employeeId" name="employeeId" placeholder="Enter ID" required>
            <button type="submit">Logout</button>
        </form>

        <!-- Info Box -->
        <div id="infoBox">
            <img id="employeeImage"></img>
            <div id="employeeName"></div>
            <div id="employeeRole"></div>

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

        document.getElementById('attendanceForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent form submission

            const employeeId = document.getElementById('employeeId').value;

            // Send POST request to PHP to handle logout
            fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'employeeId=' + encodeURIComponent(employeeId)
                })
                .then(response => response.json())
                .then(data => {
                    const infoBox = document.getElementById('infoBox');
                    const employeeImg = document.getElementById('employeeImage');
                    const employeeName = document.getElementById('employeeName');
                    const employeeRole = document.getElementById('employeeRole');


                    if (data.status === 'success') {
                        // Show success message with employee's name and role
                        employeeImg.src = data.image;
                        employeeName.innerHTML = `${data.name}`;
                        employeeRole.innerText = `(${data.role})`

                    } else {
                        // Show error message
                        infoBox.style.width = '400px'
                        employeeImg.style.display = 'none';
                        employeeRole.style.display = 'none';
                        employeeName.innerText = data.message;
                    }

                    // Show the info box
                    infoBox.classList.add('show');

                    // Clear the input field
                    document.getElementById('employeeId').value = '';

                    // Hide the info box after 2 seconds
                    setTimeout(() => {
                        infoBox.classList.remove('show');
                    }, 2000);
                })
                .catch(error => console.error('Error:', error));
        });
    </script>

</body>

</html>
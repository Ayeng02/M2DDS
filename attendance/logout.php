<?php
// Database connection
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp_id = $_POST['employeeId'];

    // Check if employee has a valid entry with time_in but no time_out
    $query = "SELECT * FROM att_tbl WHERE emp_id = ? AND time_out IS NULL ORDER BY att_date DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Get the current timestamp for logout time
        $time_out = date('Y-m-d H:i:s');

        // Update the time_out for the corresponding record
        $row = $result->fetch_assoc();
        $att_id = $row['att_id'];

        $updateQuery = "UPDATE att_tbl SET time_out = ? WHERE att_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param('si', $time_out, $att_id);
        
        if ($updateStmt->execute()) {
            // Fetch employee details to show in SweetAlert
            $empQuery = "SELECT emp_fname, emp_lname, emp_img FROM emp_tbl WHERE emp_id = ?";
            $empStmt = $conn->prepare($empQuery);
            $empStmt->bind_param('s', $emp_id);
            $empStmt->execute();
            $empResult = $empStmt->get_result();

            if ($empResult->num_rows > 0) {
                $empRow = $empResult->fetch_assoc();

                // Prepare the data to send back to the client
                $response = [
                    'success' => true,
                    'success_message' => 'Logout successful!',
                    'name' => $empRow['emp_fname'] . ' ' . $empRow['emp_lname'],
                    'image' => $empRow['emp_img']
                ];
            } else {
                $response = [
                    'success' => false,
                    'error' => 'Employee details not found.'
                ];
            }
        } else {
            $response = [
                'success' => false,
                'error' => 'Failed to log out.'
            ];
        }
    } else {
        $response = [
            'success' => false,
            'error' => 'No active session found for this employee ID.'
        ];
    }

    // Return JSON response
    echo json_encode($response);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipper - Meat-To-Door Delivery Logout</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        body.show {
            opacity: 1;
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
    </style>
</head>
<body class="animate">

    <!-- Video Background -->
    <video autoplay muted loop>
        <source src="../attendance/sample_only.mp4" type="video/mp4">
    </video>

    <div class="container">
        <h1 class="title">Meat to Door Delivery System Logout</h1>

        <form id="logoutForm" action="logout.php" method="POST">
            <input type="text" name="employeeId" placeholder="Enter ID" required>
            <button type="submit">Logout</button>
        </form>
    </div>

    <!-- Choice Button -->
    <div class="choice-container">
        <button class="choice-button" id="choiceButton">
            <i class="fas fa-ellipsis-v"></i>
        </button>
        <div class="choice-menu" id="choiceMenu">
            <a href="login.php"><i class="fas fa-sign-in-alt"></i>Login</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.body.classList.add('show');
        });

        const choiceButton = document.getElementById('choiceButton');
        const choiceMenu = document.getElementById('choiceMenu');

        choiceButton.addEventListener('click', () => {
            if (choiceMenu.classList.contains('show')) {
                choiceMenu.classList.remove('show');
            } else {
                const otherMenus = document.querySelectorAll('.choice-menu.show');
                otherMenus.forEach(menu => menu.classList.remove('show'));
                choiceMenu.classList.add('show');
            }
        });

        document.addEventListener('click', (event) => {
            if (!choiceButton.contains(event.target) && !choiceMenu.contains(event.target)) {
                choiceMenu.classList.remove('show');
            }
        });
    </script>
</body>
</html>

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
        /* Basic styles and responsive layout */
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

        /* Fullscreen video background */
        video {
            position: absolute;
            top: 0;
            left: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -1;
            object-fit: cover;
            opacity: 0.65; /* Slight opacity for better contrast */
        }

        .container {
            background: rgba(255, 255, 255, 0.9); /* Smooth white background */
            backdrop-filter: blur(10px); /* Smooth blur effect */
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            width: 100%;
            height: 70%; /* Ensures container takes up full height */
            display: flex;
            flex-direction: column;
            position: relative;
        }

        h1.title {
            text-align: center;
            margin-bottom: 25px;
            font-size: 36px; /* Increased font size for a more prominent title */
            color: #FF8225; /* Smooth orange color for title */
            font-weight: bold;
            letter-spacing: 2px; /* Increased spacing between letters for better readability */
        }

        form {
            display: flex;
            flex-direction: column;
            justify-content: flex-end; /* Aligns the input and button to the bottom */
            height: 100%; /* Makes the form fill the container's height */
        }

        input[type="text"] {
            padding: 12px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 10px;
            transition: border 0.3s, box-shadow 0.3s;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-top: 5px; /* Adds space between input and button */
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
            margin-top: 10px; /* Adds some space at the very bottom of the container */
        }

        button:hover {
            background-color: #e6741b;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(230, 116, 27, 0.3);
        }

        /* Choice button and menu */
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
            bottom: 60px; /* Adjust to position menu above button */
            right: 0;
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px); /* Smooth blur effect */
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            padding: 10px;
            width: 200px; /* Wider container */
            z-index: 10;
            transition: opacity 0.3s, transform 0.3s; /* Add transition for smoother appearance */
            opacity: 0; /* Initially hidden */
            transform: translateY(10px); /* Start slightly below */
        }

        .choice-menu.show {
            display: flex;
            opacity: 1; /* Show with opacity */
            transform: translateY(0); /* Move into place */
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
            cursor: pointer; /* Pointer cursor on hover */
        }

        .choice-menu a:hover {
            background-color: #FF8225; /* Change background color on hover */
            color: white; /* Change text color to white on hover */
        }

        .choice-menu i {
            margin-right: 10px;
        }

    </style>
</head>
<body>

    <!-- Video Background -->
    <video autoplay muted loop>
        <source src="../attendance/sample_only.mp4" type="video/mp4">
    </video>

    <div class="container">
        <h1 class="title">Meat to Door Delivery System Login </h1>

        <form id="attendanceForm" method="POST">
            <input type="text" id="employeeId" name="employeeId" placeholder="Enter ID" required>
            <button type="submit">Login</button>
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
        const choiceButton = document.getElementById('choiceButton');
        const choiceMenu = document.getElementById('choiceMenu');

        choiceButton.addEventListener('click', () => {
            // Toggle the 'show' class to control visibility
            if (choiceMenu.classList.contains('show')) {
                choiceMenu.classList.remove('show');
            } else {
                // Hide any other open menus (if necessary)
                const otherMenus = document.querySelectorAll('.choice-menu.show');
                otherMenus.forEach(menu => menu.classList.remove('show'));

                // Show the current menu
                choiceMenu.classList.add('show');
            }
        });

        document.addEventListener('click', (event) => {
            // Close the menu if clicking outside
            if (!choiceButton.contains(event.target) && !choiceMenu.contains(event.target)) {
                choiceMenu.classList.remove('show');
            }
        });

    </script>
</body>
</html>

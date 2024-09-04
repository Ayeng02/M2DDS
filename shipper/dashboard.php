<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Meat-To-Door Delivery</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">
    <style>
        body {
            display: flex;
            margin-left: 200px;
        }

        .background-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 150%;
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

        .navbar {
            height: 100vh; /* Full viewport height */
            width: 200px; /* Set the desired width */
            position: fixed;
            top: 0;
            left: 0;
            background-color: #FF8225;
            z-index: 999;
            overflow-y: auto; 
        }

        .navbar-nav {
            flex-direction: row; 
            width: 100%; 
            justify-content: space-around; 
            flex-wrap: wrap; 
        }

        .nav-link {
            color: black;
            transition: color 0.3s;
            padding: 0.5rem 1rem; 
            text-align: left; 
        }

        .nav-link:hover,
        .nav-item.active .nav-link {
            color: white;
        }

        .navbar-brand {
            color: white;
            margin-bottom: 1rem;
        }

        .navbar-brand:hover {
            color: white;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='crimson' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }

        .main-content {
            margin-left: 200px;
            padding: 2rem;
            flex-grow: 1;
        }

        @media (max-width: 100%) {
            .navbar {
                width: 75%;
                height: auto; /* Allow height to adjust based on content */
                position: static; /* Make the navbar scrollable with the rest of the content */
            }

            body {
                margin-left: 0; /* Reset margin for small screens */
            }
        }

            .main-content {
                margin-left: 0;
            }

            .navbar-nav {
                justify-content: space-around;
            }
        
    </style>
</head>

<body>
    <div class="background-animation"></div>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg">
        <!-- <a class="navbar-brand" href="#">

        </a> -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a class="nav-link" href="#">
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
                    <a class="nav-link" href="#">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        |
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-book"></i> User Manual
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Your main content goes here -->
        <h1>Welcome to Meat-To-Door Delivery</h1>
        <p>This is where you can include more information, login forms, etc.</p>
    </div>

</body>

</html>
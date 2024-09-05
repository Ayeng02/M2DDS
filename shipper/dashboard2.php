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
        body {
            display: flex;
            flex-direction: column;
            margin: 0;
        }

        .background-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 150%;
            background-color: #F5F5DC;
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
            background-color: salmon ;
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
        .navbar-light {
            background-color: #FF8225;
        }
        .navbar-light .navbar-nav .nav-link {
            color: black;
            
            
        }
        .navbar-light .navbar-nav .nav-link:hover {
            color: #a72828;
        }

        .main-content {
            margin-left: 200px;
            padding: 2rem;
            flex-grow: 1;
        }

        @media (max-width: 768px) {
            .navbar {
                height: auto;
                width: 100%;
                position: static;
            }

            .navbar-nav {
                justify-content: flex-end;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
                text-align: left;
            }
        }
    </style>
</head>

<body>
    <div class="background-animation"></div>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
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
                <li class="nav-item">
                    <a class="nav-link" href="#">
                    <i class="fa-solid fa-location-dot"> MAP</i> 
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-sign-in-alt"> LOG OUT</i>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
        
    <!-- Main Content Area -->
    <div class="main-content">
        <h1 style = "font-family:'Dancing Script';">Welcome to Meat-To-Door Delivery, (name of shipper)!</h1>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

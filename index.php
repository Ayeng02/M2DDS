<?php

session_start();

// Redirect to landing page if already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: ./customer/customerLandingPage.php');
}

include './includes/prefereces_shop.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meat-To-Door Delivery</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">
    <link rel="manifest" href="./manifest.json" />
    <style>
        body {
            background-color: #f0f0f0;
        }

        .navbar {
            width: 100%;
            z-index: 999;
            position: fixed;
            background-color: #FF8225;
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

        .product-img {
            width: 100%;
            height: auto;
            object-fit: cover;
        }

        .carousel-item img {
            width: 100%;
            height: auto;
        }

        .carousel-border {
            border: 2px solid #a72828;
            border-radius: 10px;
            overflow: hidden;
        }

        .rating {
            color: #ffc107;
        }

        .cart-badge {
            position: relative;
            top: -5px;
            right: -5px;
        }

        .con1 {
            margin-top: 100px;
        }

        .btn-primary {
            border-radius: 20px;
        }

        .btn-outline-success {
            border-radius: 20px;
            border: 1px solid #a72828;
            background-color: transparent;
            color: #a72828;
            text-align: center;
            font-weight: normal;
            transition: background-color 0.3s, color 0.3s, border-color 0.3s;
        }

        .btn-outline-success:hover {
            background-color: #a72828;
            color: white;
            border-color: #a72828;
        }

        footer {
            background-color: #a72828;
            color: #ffffff;
        }

        footer a {
            color: #ffffff;
        }

        footer a:hover {
            color: #e0e0e0;
            text-decoration: none;
        }

        .footer-section {
            padding: 20px 0;
        }

        .footer-section h5 {
            margin-bottom: 15px;
            font-weight: bold;
        }

        .product-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-card {
            border: 1px solid #a72828;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            flex: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            margin-left: -8px;
            margin-right: -8px;
        }



        .product-card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(255, 7, 7, 0.2);
        }

        .product-card img {
            transition: transform 0.3s ease;
        }

        .product-card:hover img {
            transform: scale(1.05);
        }


        .product-img:hover {
            transform: scale(1.05);
        }

        .logo {
            height: 40px;
            margin-right: 10px;
        }

        .best-seller {
            overflow: hidden;
        }

        .catIndex {
            padding-top: 20px;
            padding-bottom: 0px;
        }

        .under {
            width: 100px;
            border: 2px solid #a72828;
            margin-bottom: 40px;
        }

        .star-yellow {
            color: #FFB200;
            /* Set star color to yellow */
            font-size: 0.9rem;
            /* Adjust size as needed */
        }

        .rate,
        .rate i {
            font-size: 13px;
        }

        /* Base styles for carousel caption and button */
        /* Base styles for carousel caption and button */
        .carousel-caption {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            padding: 15px;
            border-radius: 5px;
            transition: all 0.5s ease-in-out;
        }

        .carousel-caption h5 {
            color: #fff;
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 10px;
            transition: all 0.5s ease-in-out;
        }

        .carousel-caption .btn {
            margin-top: 10px;
            padding: 10px 20px;
            font-size: 1rem;
            background: #a72828;
            border: #a72828;
            transition: all 0.5s ease-in-out;
        }

        #installApp {
            display: none;
        }

        /* Adjustments for screens between 600px and 900px */
        @media (min-width: 500px) and (max-width: 1000px) {
            .carousel-caption h5 {
                font-size: 1.2rem;
                /* Adjust name font size */
                margin-top: -20px;
                margin-bottom: 20px;
            }

            .carousel-caption .btn {
                padding: 10px 20px;
                /* Adjust button padding */
                font-size: 1rem;
                /* Adjust button font size */
                border-radius: 5px;
                /* Optional: adjust border-radius */
                margin-top: -10px;
            }
        }

        /* Responsive adjustments for smaller screens */
        @media (max-width: 768px) {
            .carousel-caption h5 {
                font-size: 1rem;
                /* Reduce caption font size for medium screens */
            }

            .carousel-caption .btn {
                padding: 8px 16px;
                /* Reduce button size for medium screens */
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .carousel-caption h5 {
                font-size: 0.8rem;
                /* Further reduce caption font size for small screens */
                margin-top: -20px;
            }

            .carousel-caption .btn {
                padding: 8px;
                font-size: 0.7rem;
                margin-top: -10px;
                border-radius: 10px;
            }
        }



        @media (max-width: 768px) {
            .card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="#">
            <img class="logo" src="./img/logo.ico" alt="Meat-To-Door Logo">
            Meat-To-Door
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
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
                    <a class="nav-link" href="./customer/login.php">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./customer/register.php">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                </li>
                <li class="nav-item">
                    <a id="installApp" class="nav-link" href="#">
                        <i class="fas fa-download"></i> Install App
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    <!-- Header -->
    <header class="jumbotron jumbotron-fluid text-center" style="background-image: url('./<?php echo $shop_bg; ?>'); background-size: cover; background-position: center center; color:#ffffff;">
        <div class="con1 container">
            <h1 class="display-4">Fresh Meat Delivered to Your Doorstep</h1>
            <p class="lead">Quality meat from trusted sources, delivered quickly and safely.</p>
            <a href="./customer/login.php" class="btn btn-primary btn-lg">Shop Now</a>
        </div>
    </header>

    <!-- Categories Carousel -->
    <div class="container my-5">
        <h2 class="text-center mb-4">Categories</h2>
        <div id="categoriesCarousel" class="carousel slide carousel-border" data-ride="carousel">
            <div class="carousel-inner">
                <?php
                include './includes/db_connect.php';
                $query = "SELECT category_name, category_img FROM category_tbl";
                $result = mysqli_query($conn, $query);
                $first = true;

                while ($row = mysqli_fetch_assoc($result)) {
                    $activeClass = $first ? 'active' : '';
                    $first = false;
                    echo '<div class="carousel-item ' . $activeClass . '">';
                    echo '<img src="' . htmlspecialchars($row['category_img']) . '" class="d-block w-100" alt="' . htmlspecialchars($row['category_name']) . '">';
                    echo '<div class="carousel-caption">';
                    echo '<h5>' . htmlspecialchars($row['category_name']) . '</h5>';
                    echo '<a href="./customer/login.php" class="btn btn-primary">Shop Now</a>';
                    echo '</div>';
                    echo '</div>';
                }

                mysqli_close($conn);
                ?>
            </div>
            <a class="carousel-control-prev" href="#categoriesCarousel" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#categoriesCarousel" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
    </div>


    <?php
    // Database connection
    include './includes/db_connect.php';

    // Fetch the top 8 best sellers with correct average ratings, review count, and total sales
    $sql = "
    SELECT p.prod_code, p.prod_name, p.prod_desc, p.prod_price, p.prod_discount, p.prod_img, 
        IFNULL(AVG(r.rev_star), 0) as avg_rating, 
        IFNULL(review_counts.review_count, 0) as review_count, 
        COUNT(o.prod_code) as total_sales
    FROM product_tbl p
    LEFT JOIN (
        SELECT prod_code, COUNT(rev_star) as review_count
        FROM ratings_tbl
        GROUP BY prod_code
    ) review_counts ON p.prod_code = review_counts.prod_code
    LEFT JOIN ratings_tbl r ON p.prod_code = r.prod_code
    JOIN order_tbl o ON p.prod_code = o.prod_code
    GROUP BY p.prod_code, p.prod_name, p.prod_desc, p.prod_price, p.prod_img, p.prod_discount, review_counts.review_count
    ORDER BY total_sales DESC
    LIMIT 8
    ";


    $result = $conn->query($sql);

    // Check if there are any best sellers
    if ($result->num_rows > 0) {
    ?>

        <!-- Best Sellers -->
        <h3 class="mb-4 text-center" style="font-weight: bold; color: #FF8225;">Best Sellers</h3>
        <hr class="under">
        <div class="container my-5">
            <div class="row">
                <?php
                while ($row = $result->fetch_assoc()) {
                    $prod_code = $row['prod_code'];
                    $prod_name = $row['prod_name'];
                    $prod_desc = $row['prod_desc'];
                    $prod_price = $row['prod_price'];
                    $prod_discount = $row['prod_discount'];
                    $prod_img = $row['prod_img'];
                    $avg_rating = number_format($row['avg_rating'], 1);
                    $review_count = $row['review_count'];

                    // Generate star ratings using Font Awesome
                    $full_stars = floor($avg_rating);
                    $half_star = $avg_rating - $full_stars >= 0.5 ? true : false;
                    $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
                ?>
                    <!-- Best Seller Product -->
                    <div class="col-6 col-md-3 mb-4">
                        <div class="card product-card">
                            <img src="./<?php echo htmlspecialchars($prod_img); ?>" class="card-img-top product-img" alt="<?php echo htmlspecialchars($prod_name); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($prod_name); ?></h5>
                                <p class="card-text">Price:
                                    <?php if ($prod_discount > 0) { ?>
                                        <span style="text-decoration: line-through;">₱<?php echo number_format($prod_price, 2); ?></span>
                                        <span style="color: #FF8225; font-weight: bold;">₱<?php echo number_format($prod_discount, 2); ?></span>
                                    <?php } else { ?>
                                        ₱<?php echo number_format($prod_price, 2); ?>
                                    <?php } ?>
                                </p>
                                <p class="card-text rate">Ratings:
                                    <?php
                                    // Full stars
                                    for ($i = 0; $i < $full_stars; $i++) {
                                        echo '<i class="fas fa-star star-yellow"></i>';
                                    }
                                    // Half star
                                    if ($half_star) {
                                        echo '<i class="fas fa-star-half-alt star-yellow"></i>';
                                    }
                                    // Empty stars
                                    for ($i = 0; $i < $empty_stars; $i++) {
                                        echo '<i class="far fa-star star-yellow"></i>';
                                    }
                                    ?>
                                    (<?php echo $avg_rating; ?>/5, <?php echo $review_count; ?> reviews)
                                </p>
                                <div class="d-flex flex-column align-items-center mb-3">
                                    <button class="btn btn-outline-success" style="margin-top: 10px;" onclick="handleAddToCart()">Add to Cart</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    <?php
    } // End if statement
    ?>




    <!-- Categories with Products -->
    <div class="container my-5">
        <?php
        include './includes/db_connect.php';

        // Fetch categories
        $categories_query = "SELECT category_code, category_name FROM category_tbl LIMIT 4";
        $categories_result = mysqli_query($conn, $categories_query);

        while ($category = mysqli_fetch_assoc($categories_result)) {
            echo '<h2 class="catIndex text-center mb-4">' . htmlspecialchars($category['category_name']) . '</h2>';
            echo '<hr class="under">';
            echo '<div class="row">';

            // Fetch products with ratings
            $products_query = "
        SELECT p.prod_code, p.prod_name, p.prod_img, p.prod_price, p.prod_discount,
               IFNULL(AVG(r.rev_star), 0) as avg_rating, COUNT(r.rev_star) as review_count
        FROM product_tbl p
        LEFT JOIN ratings_tbl r ON p.prod_code = r.prod_code
        WHERE p.category_code = '" . $category['category_code'] . "'
        GROUP BY p.prod_code, p.prod_name, p.prod_img, p.prod_price, p.prod_discount
        LIMIT 8
    ";
            $products_result = mysqli_query($conn, $products_query);

            while ($product = mysqli_fetch_assoc($products_result)) {
                $prod_code = $product['prod_code'];
                $prod_name = $product['prod_name'];
                $prod_img = $product['prod_img'];
                $prod_price = number_format($product['prod_price'], 2);
                $prod_discount = number_format($product['prod_discount'], 2);
                $avg_rating = number_format($product['avg_rating'], 1);
                $review_count = $product['review_count'];

                // Generate star ratings using Font Awesome
                $full_stars = floor($avg_rating);
                $half_star = $avg_rating - $full_stars >= 0.5 ? true : false;
                $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
        ?>
                <!-- Product Card -->
                <div class="col-6 col-md-3 mb-4">
                    <div class="card product-card">
                        <img src="./<?php echo htmlspecialchars($prod_img); ?>" class="card-img-top product-img" alt="<?php echo htmlspecialchars($prod_name); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($prod_name); ?></h5>
                            <p class="card-text">
                                <?php if ($prod_discount > 0) { ?>
                                    <span style="text-decoration: line-through;">₱<?php echo $prod_price; ?></span>
                                    <span style="color: #FF8225; font-weight: bold;">₱<?php echo $prod_discount; ?></span>
                                <?php } else { ?>
                                    <strong>₱<?php echo $prod_price; ?></strong>
                                <?php } ?>
                            </p>
                            <p class="card-text">Ratings:
                                <?php
                                // Full stars
                                for ($i = 0; $i < $full_stars; $i++) {
                                    echo '<i class="fas fa-star star-yellow"></i>';
                                }
                                // Half star
                                if ($half_star) {
                                    echo '<i class="fas fa-star-half-alt star-yellow"></i>';
                                }
                                // Empty stars
                                for ($i = 0; $i < $empty_stars; $i++) {
                                    echo '<i class="far fa-star star-yellow"></i>';
                                }
                                ?>
                                (<?php echo $avg_rating; ?>/5, <?php echo $review_count; ?> reviews)
                            </p>
                            <div class="d-flex justify-content-center mt-3">
                                <a href="./customer/login.php" class="btn btn-primary btn-outline-success">Add to Cart</a>
                            </div>
                        </div>
                    </div>
                </div>
        <?php
            }

            echo '</div>';
        }

        mysqli_close($conn);
        ?>
    </div>


    <!-- Footer -->
    <?php include './includes/footer.php' ?>
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function handleAddToCart() {
            <?php if (!isset($_SESSION['cust_id'])) { ?>
                // If not logged in, redirect to login.php
                window.location.href = './customer/login.php';
            <?php } else { ?>
                // If logged in, you can add further functionality here
                alert('You are already logged in. Proceed with adding to cart!');
                // You can also implement AJAX call or other actions here
            <?php } ?>
        }

        let deferredPrompt; // Variable to store the beforeinstallprompt event

        // Listen for the `beforeinstallprompt` event
        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault(); // Prevent the default mini-infobar prompt
            deferredPrompt = event; // Save the event for triggering later
            console.log('[PWA] beforeinstallprompt fired');
            document.getElementById('installApp').style.display = 'block'; // Show the Install button
        });

        // Handle "Install App" click
        document.getElementById('installApp').addEventListener('click', async () => {
            if (!deferredPrompt) {
                console.log('[PWA] No deferred prompt available');
                return;
            }

            // Show the install prompt
            deferredPrompt.prompt();

            // Wait for the user to respond to the prompt
            const choiceResult = await deferredPrompt.userChoice;
            console.log(`[PWA] User response to the install prompt: ${choiceResult.outcome}`);

            // Clear the saved prompt, as it can only be used once
            deferredPrompt = null;
        });

        // Optionally, listen for the `appinstalled` event
        window.addEventListener('appinstalled', () => {
            console.log('[PWA] App successfully installed');
            alert('App installed successfully!');
        });
    </script>

</body>

</html>
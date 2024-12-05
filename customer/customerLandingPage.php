<?php

include '../includes/db_connect.php';

// Fetch current shop background and video
$shop_bg_query = "SELECT olshopmgt_bg FROM olshopmgt_tbl LIMIT 1";  // Replace with actual table and column
$shop_vid_query = "SELECT olshopmgt_vid FROM olshopmgt_tbl LIMIT 1";  // Replace with actual table and column

$shop_bg_result = $conn->query($shop_bg_query);
$shop_vid_result = $conn->query($shop_vid_query);

$default_bg = '../img/meat-bg.png'; // Default background image
$default_vid = '../img/sampleVid.mp4'; // Default video file

$shop_bg = $default_bg; // Set default image path
$shop_vid = $default_vid; // Set default video path

if ($shop_bg_result && $shop_bg_result->num_rows > 0) {
    $row = $shop_bg_result->fetch_assoc();
    $shop_bg = $row['olshopmgt_bg']; // Replace with actual path from the database
}

if ($shop_vid_result && $shop_vid_result->num_rows > 0) {
    $row = $shop_vid_result->fetch_assoc();
    $shop_vid = $row['olshopmgt_vid']; // Replace with actual path from the database
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/home.css">
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">
    <style>
        body {
            background-color: #f0f0f0;
        }

        .navbar {
            top: 0px;
        }

        .navbar-light .navbar-nav .nav-link.act1 {
            color: #ffffff;
        }

        .product-card {
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

        .card-body {
            flex-grow: 1;
        }

        .card-img-top {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .star-yellow {
            color: #FFB200;
            font-size: 0.9rem;
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            opacity: 0;
            transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
            transform: translateY(-20px);
            margin-top: 60px;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast.hide {
            opacity: 0;
            transform: translateY(-20px);
        }

        .carousel-caption {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;

        }

        .btn-primary {
            background-color: #FF8225;
            border-color: #FF8225;
        }

        .btn-primary:hover {
            background-color: #e36f10;
            border-color: #e36f10;
        }

        .badge-custom {
            color: #ffffff;
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php' ?>

    <!-- Landing Page Section -->
    <div class="container my-5">
        <div class="welcome-message text-center mb-5">
            <h1 style="margin-top: 100px;">Welcome, <?php echo htmlspecialchars($customer['f_name'] . ' ' . $customer['l_name']); ?>!</h1>
            <p>Thank you for logging in. Explore our latest offers and manage your account.</p>
        </div>

        <!-- Categories Carousel -->
        <?php
        // Database connection
        include '../includes/db_connect.php';

        // Fetch categories from database
        $sql = "SELECT category_code, category_name, category_desc, category_img FROM category_tbl";
        $result = $conn->query($sql);

        echo '<div id="categoriesCarousel" class="carousel slide" data-ride="carousel">
                 <div class="carousel-inner">';

        if ($result->num_rows > 0) {
            $isActive = true;
            while ($row = $result->fetch_assoc()) {
                $activeClass = $isActive ? 'active' : '';
                $isActive = false; // Only the first item should be active

                echo '<div class="carousel-item ' . $activeClass . '">
                                    <a href="category.php?code=' . $row["category_code"] . '">
                                        <img src="../' . $row["category_img"] . '" class="d-block w-100" alt="' . $row["category_name"] . '">
                                    </a>
                                    <div class="carousel-caption d-flex flex-column justify-content-center align-items-center">
                                        <h5>' . $row["category_name"] . '</h5>
                                        <p>' . $row["category_desc"] . '</p>
                                    </div>
                            </div>';
            }
        } else {
            // Display default carousel item if no categories are found
            echo '<div class="carousel-item active">
                        <img src="https://via.placeholder.com/1200x300?text=No+Categories+Available" class="d-block w-100" alt="No Categories">
                        <div class="carousel-caption d-flex flex-column justify-content-center align-items-center">
                            <h5>No Categories Available</h5>
                            <p>Please check back later for updates.</p>
                        </div>
                    </div>';
        }

        echo '  </div>
                <a class="carousel-control-prev" href="#categoriesCarousel" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#categoriesCarousel" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
             </div>';

        $conn->close();
        ?>


        <!-- Recent Orders -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <h3>Recent Orders</h3>
                <div class="list-group">
                    <?php
                    include '../includes/db_connect.php';

                    // Get the customer ID from the session
                    $cust_id = $_SESSION['cust_id'];

                    if (!isset($cust_id)) {
                        echo "<p class='text-danger'>Customer ID is not set. Please log in.</p>";
                        exit();
                    }

                    // Create a query to get the latest three orders with product code, status, and total
                    $sql = "SELECT 
                                o.order_id AS order_id,
                                s.status_name AS status,
                                o.order_total
                            FROM 
                                order_tbl o
                            JOIN 
                                status_tbl s ON o.status_code = s.status_code
                            WHERE 
                                o.cust_id = ? 
                            ORDER BY 
                                o.order_date DESC
                            LIMIT 5";

                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        die("<p class='text-danger'>Preparation failed: " . $conn->error . "</p>");
                    }

                    $stmt->bind_param("s", $cust_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if (!$result) {
                        die("<p class='text-danger'>Execution failed: " . $stmt->error . "</p>");
                    }

                    // Fetch and display orders
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $order_id = $row['order_id'];
                            $status = $row['status'];
                            $order_total = $row['order_total'];

                            echo "<a href='order-details.php?order_id=$order_id' class='list-group-item list-group-item-action d-flex justify-content-between align-items-center'>";
                            echo "<div>";
                            echo "<h5 class='mb-1'>Order #$order_id</h5>";
                            echo "<p class='mb-1'>Status: $status</p>";
                            echo "</div>";
                            echo "<span class='badge badge-custom bg-primary rounded-pill'>₱" . number_format($order_total, 2) . "</span>";
                            echo "</a>";
                        }
                    } else {
                        echo "<p class='text-muted' style='text-align:center; margin-top:50px; font-size:20px; opacity:0.5;'> 
                                <img src='../img/sad-emoji-svgrepo-com.svg' alt='sad face svg' />        
                        </p>";
                        echo "<p class='text-muted' style='text-align:center; margin-top:0px; font-size:20px;'>You haven't placed any orders yet.</p>";
                    }

                    $stmt->close();
                    $conn->close();
                    ?>
                </div>
            </div>


            <!-- Special Offers -->
            <div class="col-lg-6">
                <!--
                <h3>Special Offers</h3>
                <div class="card-deck">
                    <div class="card">
                        <img src="https://via.placeholder.com/400x200?text=Offer+1" class="card-img-top" alt="Offer 1">
                        <div class="card-body">
                            <h5 class="card-title">Offer Title 1</h5>
                            <p class="card-text">Save up to 20% on select items. Limited time only!</p>
                            <a href="offer-details.html" class="btn btn-primary">Learn More</a>
                        </div>
                    </div>
                    <div class="card">
                        <img src="https://via.placeholder.com/400x200?text=Offer+2" class="card-img-top" alt="Offer 2">
                        <div class="card-body">
                            <h5 class="card-title">Offer Title 2</h5>
                            <p class="card-text">Buy 1 Get 1 Free on all products in this category.</p>
                            <a href="offer-details.html" class="btn btn-primary">Learn More</a>
                        </div>
                    </div>
                    <div class="card">
                        <img src="https://via.placeholder.com/400x200?text=Offer+3" class="card-img-top" alt="Offer 3">
                        <div class="card-body">
                            <h5 class="card-title">Offer Title 3</h5>
                            <p class="card-text">Special discount on selected items.</p>
                            <a href="offer-details.html" class="btn btn-primary">Learn More</a>
                        </div>
                    </div>
                </div>
                -->
            </div>
        </div>

        <!-- Product Cards for what's New-->
        <h3 class="mb-4" style="text-align: center; padding-top: 20px; font-weight: bold; color: crimson;">What's New</h3>
        <hr class="under">
        <div class="row g-1">
            <?php
            include '../includes/db_connect.php';

            // Query to get products with average star rating and the number of reviews, limited to 4
            $sql = "SELECT p.prod_code, p.prod_name, p.prod_desc, p.prod_price, p.prod_img, p.prod_discount, p.prod_qoh, 
                        IFNULL(AVG(r.rev_star), 0) as avg_rating, COUNT(r.rev_star) as review_count
                    FROM product_tbl p
                    LEFT JOIN ratings_tbl r ON p.prod_code = r.prod_code
                    GROUP BY p.prod_code, p.prod_name, p.prod_desc, p.prod_price, p.prod_img, p.prod_discount
                    ORDER BY p.created_at DESC
                    LIMIT 4 ";


            $result = $conn->query($sql);

            // Check if there are results
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $prod_code = $row['prod_code'];
                    $prod_qoh = $row['prod_qoh'];
                    $prod_name = $row['prod_name'];
                    $prod_desc = $row['prod_desc'];
                    $prod_price = $row['prod_price'];
                    $prod_img = $row['prod_img'];
                    $prod_discount = $row['prod_discount'];
                    $avg_rating = number_format($row['avg_rating'], 1);
                    $review_count = $row['review_count'];

                    // Generate star ratings using Font Awesome
                    $full_stars = floor($avg_rating);
                    $half_star = $avg_rating - $full_stars >= 0.5 ? true : false;
                    $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
            ?>
                    <!-- Product Card -->
                    <div class="col-6 col-md-3 mb-4">
                        <div class="card product-card">
                            <img src="../<?php echo htmlspecialchars($prod_img); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($prod_name); ?>" style="height: 200px; object-fit: cover;">
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
                                    for ($i = 0; $i < $full_stars; $i++) {
                                        echo '<i class="fas fa-star star-yellow"></i>';
                                    }
                                    if ($half_star) {
                                        echo '<i class="fas fa-star-half-alt star-yellow"></i>';
                                    }
                                    for ($i = 0; $i < $empty_stars; $i++) {
                                        echo '<i class="far fa-star star-yellow"></i>';
                                    }
                                    ?>
                                    (<?php echo $avg_rating; ?>/5, <?php echo $review_count; ?> reviews)
                                </p>
                                <div class="d-flex flex-column align-items-center mb-3">
                                    <?php if ($prod_qoh > 0): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <button class="incBtn1 btn btn-outline-secondary btn-sm" onclick="changeQuantity('decrease', '<?php echo $prod_code; ?>')">-</button>
                                            <input type="text" id="quantity-<?php echo $prod_code; ?>" class="form-control form-control-sm mx-1" value="1" readonly style="width: 50px; text-align: center; background-color: #FF8225; color: #f0f0f0; font-weight: 500; font-size:12px;">
                                            <button class="incBtn2 btn btn-outline-secondary btn-sm" onclick="changeQuantity('increase', '<?php echo $prod_code; ?>')">+</button>
                                        </div>
                                        <button class="btn btn-outline-success" style="margin-top: 10px;" onclick="addToCart('<?php echo $prod_code; ?>')">Add to Cart</button>
                                    <?php else: ?>
                                        <p class="text-danger" style="font-weight: 800;">Out of Stock</p>
                                    <?php endif; ?>
                                    <a href="product-details.php?id=<?php echo $prod_code; ?>" class="btn btn-primary mt-2">See Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
            } else {
                // Display default product card
                ?>
                <div class="col-6 col-md-3 mb-4">
                    <div class="card product-card">
                        <img src="https://via.placeholder.com/400x200?text=No+Product+Available" class="card-img-top" alt="No Product Available" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title">No Product Available</h5>
                            <p class="card-text">Price: $0.00</p>
                            <p class="card-text">Ratings: <i class="far fa-star star-yellow"></i><i class="far fa-star star-yellow"></i><i class="far fa-star star-yellow"></i><i class="far fa-star star-yellow"></i><i class="far fa-star star-yellow"></i> (0/5, 0 reviews)</p>
                            <div class="d-flex flex-column align-items-center mb-3">
                                <button class="incBtn1 btn btn-outline-secondary" disabled>-</button>
                                <input type="text" class="form-control mx-2" value="0" readonly style="width: 60px; text-align: center; background-color: #FF8225; color: #f0f0f0; font-weight: 500;">
                                <button class="incBtn2 btn btn-outline-secondary" disabled>+</button>
                                <button class="btn btn-outline-success" style="margin-top: 10px;" disabled>Add to Cart</button>
                                <a href="#" class="btn btn-primary mt-2 disabled">See Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }

            $conn->close();
            ?>
        </div>

        <?php
        // Database connection
        include '../includes/db_connect.php';

        // Fetch the top 8 best sellers with correct average ratings, review count, and total sales
        $sql = "SELECT p.prod_code, p.prod_name, p.prod_desc, p.prod_price, p.prod_discount, p.prod_img, p.prod_qoh, 
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
            <h3 class="mb-4" style="text-align: center; padding-top: 20px; font-weight: bold; color: #FF8225;">Best Sellers</h3>
            <hr class="under">
            <div class="row">
                <?php
                while ($row = $result->fetch_assoc()) {
                    $prod_code = $row['prod_code'];
                    $prod_qoh = $row['prod_qoh'];
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
                            <img src="../<?php echo htmlspecialchars($prod_img); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($prod_name); ?>">
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
                                    <!--
                                    <div class="d-flex align-items-center mb-2">
                                        <button class="incBtn1 btn btn-outline-secondary btn-sm" onclick="changeQuantity('decrease', '<?php echo $prod_code; ?>')">-</button>
                                        <input type="text" id="quantity-<?php echo $prod_code; ?>" class="form-control form-control-sm mx-1" value="1" readonly style="width: 50px; text-align: center; background-color: #FF8225; color: #f0f0f0; font-weight: 500; font-size:12px;">
                                        <button class="incBtn2 btn btn-outline-secondary btn-sm" onclick="changeQuantity('increase', '<?php echo $prod_code; ?>')">+</button>
                                    </div>
                                -->
                                    <?php if ($prod_qoh > 0): ?>
                                        <button class="btn btn-outline-success" style="margin-top: 10px;" onclick="addToCart('<?php echo $prod_code; ?>')">Add to Cart</button>
                                    <?php else: ?>
                                        <p class="text-danger" style="font-weight: 800;">Out of Stock</p>
                                    <?php endif; ?>
                                    <a href="product-details.php?id=<?php echo $prod_code; ?>" class="btn btn-primary mt-2">See Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        <?php
        } // End if statement
        ?>

        <?php
        // Database connection
        include '../includes/db_connect.php';

        // Fetch discounted products with average ratings
        $sql = "SELECT p.prod_code, p.prod_name, p.prod_desc, p.prod_price, p.prod_discount, p.prod_img, p.prod_qoh,
                    IFNULL(AVG(r.rev_star), 0) as avg_rating, COUNT(r.rev_star) as rating_count
                FROM product_tbl p
                LEFT JOIN ratings_tbl r ON p.prod_code = r.prod_code
                WHERE p.prod_discount > 0
                GROUP BY p.prod_code, p.prod_name, p.prod_desc, p.prod_price, p.prod_discount, p.prod_img";

        $result = $conn->query($sql);

        // Check if there are any discounted products
        if ($result->num_rows > 0) {
        ?>

            <!-- Fresh Deals -->
            <h3 class="mb-4" style="text-align: center; padding-top: 20px; font-weight: bold; color: green;">Fresh Deals</h3>
            <hr class="under">
            <div class="row">
                <?php
                while ($row = $result->fetch_assoc()) {
                    $prod_code = $row['prod_code'];
                    $prod_qoh = $row['prod_qoh'];
                    $prod_name = $row['prod_name'];
                    $prod_desc = $row['prod_desc'];
                    $prod_price = $row['prod_price'];
                    $prod_discount = $row['prod_discount'];
                    $prod_img = $row['prod_img'];
                    $discounted_price = number_format($prod_discount, 2);
                    $original_price = number_format($prod_price, 2);
                    $avg_rating = number_format($row['avg_rating'], 1);
                    $rating_count = $row['rating_count'];

                    // Generate star ratings using Font Awesome
                    $full_stars = floor($avg_rating);
                    $half_star = $avg_rating - $full_stars >= 0.5 ? true : false;
                    $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
                ?>
                    <!-- Fresh Deal -->
                    <div class="col-6 col-md-3 mb-4">
                        <div class="card product-card">
                            <img src="../<?php echo htmlspecialchars($prod_img); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($prod_name); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($prod_name); ?></h5>
                                <p class="card-text">Price:
                                    <span style="text-decoration: line-through; color: #888;">₱<?php echo $original_price; ?></span><br>
                                    <strong style="color: #FF8225;">₱<?php echo $discounted_price; ?></strong>
                                </p>
                                <p class="card-text rate">Ratings:
                                    <?php
                                    for ($i = 0; $i < $full_stars; $i++) {
                                        echo '<i class="fas fa-star star-yellow"></i>';
                                    }
                                    if ($half_star) {
                                        echo '<i class="fas fa-star-half-alt star-yellow"></i>';
                                    }
                                    for ($i = 0; $i < $empty_stars; $i++) {
                                        echo '<i class="far fa-star star-yellow"></i>';
                                    }
                                    ?>
                                    (<?php echo $avg_rating; ?>/5, <?php echo $rating_count; ?> reviews)
                                </p>
                                <div class="d-flex flex-column align-items-center mb-3">
                                    <!--
                                    <div class="d-flex align-items-center mb-2">
                                        <button class="incBtn1 btn btn-outline-secondary btn-sm" onclick="changeQuantity('decrease', '<?php echo $prod_code; ?>')">-</button>
                                        <input type="text" id="quantity-<?php echo $prod_code; ?>" class="form-control form-control-sm mx-1" value="1" readonly style="width: 50px; text-align: center; background-color: #FF8225; color: #f0f0f0; font-weight: 500; font-size:12px;">
                                        <button class="incBtn2 btn btn-outline-secondary btn-sm" onclick="changeQuantity('increase', '<?php echo $prod_code; ?>')">+</button>
                                    </div>
                                -->
                                    <?php if ($prod_qoh > 0): ?>
                                        <button class="btn btn-outline-success" style="margin-top: 10px;" onclick="addToCart('<?php echo $prod_code; ?>')">Add to Cart</button>
                                    <?php else: ?>
                                        <p class="text-danger" style="font-weight: 800;">Out of Stock</p>
                                    <?php endif; ?>
                                    <a href="product-details.php?id=<?php echo $prod_code; ?>" class="btn btn-primary mt-2">See Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        <?php
        } else {
        }
        //connection close
        $conn->close();
        ?>



        <div class="video-section">
            <h3>Watch Our Latest Promo Video</h3>
            <hr class="under">
            <video controls autoplay muted loop>
                <source src="../<?php echo $shop_vid; ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>


    </div>

    <!-- Toast Notification -->
    <div class="toast" id="successToast" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="true">
        <div class="toast-header" style="background-color: #0ab001;">
            <strong class="mr-auto" style="color: #ffffff;">Success</strong>
            <small style="color: #ffffff;">Now</small>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body" id="toastMessage">
            <!-- Success message will be injected here -->
        </div>
    </div>

    <?php include '../includes/message.php'; ?>

    <!-- Footer -->
    <?php include '../includes/footer.php' ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../js/notif.js"></script>
    <script>
        function changeQuantity(action, productId) {
            console.log(`Action: ${action}, Product ID: ${productId}`); // Debugging

            const quantityInput = document.getElementById(`quantity-${productId}`);
            let currentQuantity = parseFloat(quantityInput.value);

            console.log(`Current Quantity: ${currentQuantity}`); // Debugging

            if (action === 'increase') {
                quantityInput.value = (currentQuantity + 0.25).toFixed(2);
            } else if (action === 'decrease') {
                if (currentQuantity > 1) { // Prevents decreasing below 1
                    quantityInput.value = (currentQuantity - 0.25).toFixed(2);
                }
            }
        }



        // For adding cart function
        function addToCart(prodCode) {
            var qtyInput = document.getElementById('quantity-' + prodCode);
            var cartQty = qtyInput ? qtyInput.value : 1; // Default to 1 if quantity input is not found

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'add_to_cart.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    var toastMessage = document.getElementById('toastMessage');
                    var toast = document.getElementById('successToast');

                    if (response.status === 'success') {
                        toastMessage.innerHTML = response.message;
                        toast.classList.add('show');
                        setTimeout(function() {
                            toast.classList.remove('show');
                            location.reload();
                        }, 3000); // Hide after 3 seconds
                    } else {
                        toastMessage.innerHTML = response.message;
                        toast.classList.add('show');
                        setTimeout(function() {
                            toast.classList.remove('show');
                        }, 3000); // Hide after 3 seconds
                    }
                } else {
                    alert('An error occurred while adding the product to the cart.');
                }
            };

            xhr.send('prod_code=' + encodeURIComponent(prodCode) + '&cart_qty=' + encodeURIComponent(cartQty));
        }
    </script>
</body>

</html>
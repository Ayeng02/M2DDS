<?php

// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set error reporting to ignore notices
error_reporting(E_ALL & ~E_NOTICE);

// Include database connection
include '../includes/db_connect.php';

if (!isset($_GET['id'])) {
    echo "<p>Product not found.</p>";
    exit;
}

$prod_code = $conn->real_escape_string($_GET['id']);

// Query to get product details
$sql_product = "
    SELECT p.prod_code, p.prod_name, p.prod_desc, p.prod_price, p.prod_img, p.prod_discount, p.prod_qoh,
           IFNULL(AVG(r.rev_star), 0) as avg_rating, COUNT(r.rev_star) as review_count
    FROM product_tbl p
    LEFT JOIN ratings_tbl r ON p.prod_code = r.prod_code
    WHERE p.prod_code = '$prod_code'
    GROUP BY p.prod_code, p.prod_name, p.prod_desc, p.prod_price, p.prod_img, p.prod_discount, p.prod_qoh
";

$result_product = $conn->query($sql_product);

if ($result_product->num_rows > 0) {
    $product = $result_product->fetch_assoc();
    $prod_name = $product['prod_name'];
    $prod_desc = $product['prod_desc'];
    $prod_price = $product['prod_price'];
    $prod_img = $product['prod_img'];
    $avg_rating = number_format($product['avg_rating'], 1);
    $review_count = $product['review_count'];
    $prod_discount = $product['prod_discount'];
    $prod_qoh = $product['prod_qoh'];
} else {
    echo "<p>Product not found.</p>";
    exit;
}

// Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submitted'])) {
    // Check if customer ID is set in the session
    if (!isset($_SESSION['cust_id'])) {
        echo "Error: Customer ID not set in session.";
        exit;
    }

    $cust_id = $_SESSION['cust_id']; // Get customer ID from session
    $rev_message = $conn->real_escape_string($_POST['rev_message']);
    $rev_star = intval($_POST['rev_star']);
    $rev_date = date('Y-m-d');

    // Insert review using stored procedure
    $sql_insert_review = "CALL sp_InsertRating('$prod_code', '$cust_id', '$rev_message', $rev_star, '$rev_date')";
    if ($conn->query($sql_insert_review) === TRUE) {
        $_SESSION['review_message'] = "Review submitted successfully!";
    } else {
        $_SESSION['review_error'] = "Error submitting review: " . $conn->error;
    }

    header("Location: product-details.php?id=$prod_code");
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/home.css">
    <style>
        body {
            background-color: #f0f0f0;
        }

        .star-yellow {
            color: #FFD700;
            /* Gold color for stars */
        }

        .product-details img {
            max-width: 100%;
            height: auto;
        }

        .review {
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .recommendation-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .recommendation-card img {
            width: 100%;
            height: 200px;
            /* Set a fixed height for all images */
            object-fit: cover;
            /* Ensure the image covers the card without distortion */
        }

        .recommendation-card .card-body {
            padding: 15px;
        }

        .centered-content {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        .navbar {
            top: 0px;
        }

        .product-details {
            margin-top: 50px;
        }

        .user-icon-circle {
            margin-left: 10px;
            color: #333;
            /* Icon color */
            border-radius: 50%;
            /* Makes the icon circular */
            background-color: #ffffff;
            /* Background color of the circle */
            padding: 10px;
            font-size: 1.2em;
            /* Adjust the icon*/
            vertical-align: middle;
            /* Aligns the icon with the text */
            margin-left: -0px;
            margin-right: 1px;
        }

        .review-container {
            max-height: none;
            overflow: hidden;
        }

        .review-container.show-more {
            max-height: none;
        }

        .review-item {
            display: none;
        }

        .review-item.visible {
            display: block;
        }

        .show-more-link {
            cursor: pointer;
            color: #007bff;
            text-decoration: underline;
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

        .btn-primary {
            background-color: #FF8225;
            border-color: #FF8225;
        }

        .btn-primary:hover {
            background-color: #e36f10;
            border-color: #e36f10;
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <div class="container my-5">
        <div class="row">
            <div class="col-md-12 centered-content">
                <div class="col-md-8">
                    <?php
                    // Display product details
                    ?>
                    <div class="product-details">
                        <h3><?php echo htmlspecialchars($prod_name); ?></h3>
                        <div class="d-flex flex-column flex-md-row">
                            <img src="../<?php echo htmlspecialchars($prod_img); ?>" alt="<?php echo htmlspecialchars($prod_name); ?>" class="mb-3" style="width: 450px; height: 300px; object-fit: cover; border-radius:10px;">
                            <div class="d-flex flex-column justify-content-between ml-md-4">
                                <div>
                                    <p>Price: ₱<?php echo number_format($prod_price, 2); ?></p>
                                    <p>Discount: ₱<?php echo number_format($prod_discount, 2); ?></p>
                                    <p>QOH (kg.): <?php echo htmlspecialchars($prod_qoh); ?> kg.</p>
                                    <p>Average Rating: <?php echo $avg_rating; ?>/5 (<?php echo $review_count; ?> reviews)</p>
                                </div>
                                <?php if ($prod_qoh > 0): ?>
                                    <!-- Quantity Adjustment -->
                                    <div class="d-flex align-items-center mb-3" style="justify-content: center;">
                                        <button class="incBtn1 btn btn-outline-secondary" onclick="changeQuantity('decrease', '<?php echo $prod_code; ?>')">-</button>
                                        <input type="text" id="quantity-<?php echo $prod_code; ?>" class="form-control mx-2" value="1" readonly style="width: 60px; text-align: center; background-color: #FF8225; color: #f0f0f0; font-weight: 500; font-size:12px;">
                                        <button class="incBtn2 btn btn-outline-secondary" onclick="changeQuantity('increase', '<?php echo $prod_code; ?>')">+</button>
                                    </div>
                                    <button class="btn btn-outline-success mb-3" onclick="addToCart('<?php echo $prod_code; ?>')">Add to Cart</button>
                                <?php else: ?>
                                    <p class="text-danger" style="font-weight:800; margin-bottom:80px;">Out of Stock</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p>Description: <?php echo htmlspecialchars($prod_desc); ?></p>
                    </div>


                    <!-- Review Submission Form -->
                    <h4 style="padding-top: 20px;">Submit Your Review</h4>
                    <form method="post" action="">
                        <input type="hidden" name="review_submitted" value="1">
                        <div class="form-group">
                            <label for="rev_star">Rating</label>
                            <select id="rev_star" name="rev_star" class="form-control" required>
                                <option value="1">1 Star</option>
                                <option value="2">2 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="5">5 Stars</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="rev_message">Review Message</label>
                            <textarea id="rev_message" name="rev_message" class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-bottom: 20px;">Submit Review</button>
                    </form>

                    <?php
                    // Display success or error message
                    if (isset($_SESSION['review_message'])) {
                        echo "<div id='success-alert' class='alert alert-success'>" . $_SESSION['review_message'] . "</div>";
                        unset($_SESSION['review_message']);
                    }

                    if (isset($_SESSION['review_error'])) {
                        echo "<div id='error-alert' class='alert alert-danger'>" . $_SESSION['review_error'] . "</div>";
                        unset($_SESSION['review_error']);
                    }

                    // Query to get reviews for the product
                    $sql_reviews = "
                        SELECT r.rev_message, r.rev_star, c.f_name, c.l_name, r.rev_date
                        FROM ratings_tbl r
                        JOIN Customers c ON r.cust_id = c.cust_id
                        WHERE r.prod_code = '$prod_code'
                        ORDER BY r.rev_date DESC
                    ";

                    $result_reviews = $conn->query($sql_reviews);
                    ?>

                    <h4 style="font-weight: bold; margin-bottom:20px;">Customer Reviews</h4>
                    <div class="review-container">
                        <?php
                        if ($result_reviews->num_rows > 0) {
                            $review_counter = 0;
                            while ($review = $result_reviews->fetch_assoc()) {
                                $rev_message = htmlspecialchars($review['rev_message']);
                                $rev_star = intval($review['rev_star']);
                                $f_name = htmlspecialchars($review['f_name']);
                                $l_name = htmlspecialchars($review['l_name']);
                                $rev_date = htmlspecialchars($review['rev_date']);
                        ?>
                                <div class="review-item <?php echo $review_counter < 5 ? 'visible' : ''; ?>">
                                    <div class="review">
                                        <p><i class="fas fa-user-circle user-icon-circle"></i> <strong><?php echo "$f_name $l_name"; ?></strong> <span class="text-muted">(<?php echo $rev_date; ?>)</span></p>
                                        <p>Rating: <?php echo str_repeat('<i class="fas fa-star star-yellow"></i>', $rev_star) . str_repeat('<i class="far fa-star"></i>', 5 - $rev_star); ?></p>
                                        <p><?php echo $rev_message; ?></p>
                                    </div>
                                </div>
                        <?php
                                $review_counter++;
                            }
                        } else {
                            echo "<p>No reviews yet.</p>";
                        }
                        ?>
                    </div>

                    <?php if ($result_reviews->num_rows > 5) { ?>
                        <p class="show-more-link" onclick="toggleReviewVisibility(event)">Show More Reviews</p>
                    <?php } ?>


                    <!-- Recommended Product Section -->
                    <h4 style="padding: 20px; text-align:center; color:crimson; font-weight:600;">Recommended Products</h4>
                    <div class="row">
                        <?php
                        $sql_recommend = "
                        SELECT prod_code, prod_name, prod_img
                        FROM product_tbl
                        WHERE prod_code != '$prod_code'
                        ORDER BY RAND()
                        LIMIT 3
                    ";
                        $result_recommend = $conn->query($sql_recommend);

                        if ($result_recommend->num_rows > 0) {
                            while ($recommend = $result_recommend->fetch_assoc()) {
                                $recommend_prod_code = $recommend['prod_code'];
                                $recommend_prod_name = $recommend['prod_name'];
                                $recommend_prod_img = $recommend['prod_img'];
                        ?>
                                <div class="col-6 col-md-4">
                                    <div class="recommendation-card">
                                        <img src="../<?php echo htmlspecialchars($recommend_prod_img); ?>" alt="<?php echo htmlspecialchars($recommend_prod_name); ?>">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($recommend_prod_name); ?></h5>
                                            <a href="product-details.php?id=<?php echo htmlspecialchars($recommend_prod_code); ?>" class="btn btn-primary">View Details</a>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            }
                        } else {
                            echo "<p>No recommended products found.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!--Footer-->
    <?php include '../includes/footer.php' ?>

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

    <!-- Bootstrap and jQuery scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../js/notif.js"></script>
    <script>
        function changeQuantity(action, prod_code) {
            var quantityInput = document.getElementById('quantity-' + prod_code);
            let currentQuantity = parseFloat(quantityInput.value);
            if (action === 'increase') {
                quantityInput.value = (currentQuantity + 0.25).toFixed(2);
            } else if (action === 'decrease') {
                if (currentQuantity > 1) {
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

        // Hide success or error alerts after a delay
        setTimeout(() => {
            const successAlert = document.getElementById('success-alert');
            if (successAlert) successAlert.style.display = 'none';

            const errorAlert = document.getElementById('error-alert');
            if (errorAlert) errorAlert.style.display = 'none';
        }, 3000);

        function toggleReviewVisibility(event) {
            event.preventDefault();
            const reviews = document.querySelectorAll('.review-item');
            const linkText = event.target;

            reviews.forEach((review, index) => {
                if (index >= 5) {
                    review.classList.toggle('visible');
                }
            });

            if (linkText.textContent === 'Show More Reviews') {
                linkText.textContent = 'Show Less Reviews';
            } else {
                linkText.textContent = 'Show More Reviews';
            }
        }
    </script>
</body>

</html>
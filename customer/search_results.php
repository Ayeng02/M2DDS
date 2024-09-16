<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search_query = "%" . $_GET['v'] . "%";
    $search_term = $_GET['v'];

    // Prepare a statement to check if the search term is a valid category name
    $category_check_sql = "SELECT category_name FROM category_tbl WHERE category_name = ?";
    $category_check_stmt = $conn->prepare($category_check_sql);
    $category_check_stmt->bind_param("s", $search_term);
    $category_check_stmt->execute();
    $category_check_result = $category_check_stmt->get_result();

    // Check if the search term is a valid category name
    if ($category_check_result->num_rows > 0) {
        // If it's a category name, query products associated with that category
        $sql = "
            SELECT p.prod_code, p.prod_name, p.prod_desc, p.prod_price, p.prod_img, p.prod_discount, p.prod_qoh,
                   IFNULL(AVG(r.rev_star), 0) as avg_rating, COUNT(r.rev_star) as review_count, c.category_name
            FROM product_tbl p
            LEFT JOIN ratings_tbl r ON p.prod_code = r.prod_code
            JOIN category_tbl c ON p.category_code = c.category_code
            WHERE c.category_name = ?
            GROUP BY p.prod_code, p.prod_name, p.prod_desc, p.prod_price, p.prod_img, p.prod_discount, c.category_name
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $search_term);
    } else {
        // If not a category name, query products based on name, description, or category name
        $sql = "
            SELECT p.prod_code, p.prod_name, p.prod_desc, p.prod_price, p.prod_img, p.prod_discount, p.prod_qoh,
                   IFNULL(AVG(r.rev_star), 0) as avg_rating, COUNT(r.rev_star) as review_count, c.category_name
            FROM product_tbl p
            LEFT JOIN ratings_tbl r ON p.prod_code = r.prod_code
            JOIN category_tbl c ON p.category_code = c.category_code
            WHERE p.prod_name LIKE ? OR p.prod_desc LIKE ? OR c.category_name LIKE ?
            GROUP BY p.prod_code, p.prod_name, p.prod_desc, p.prod_price, p.prod_img, p.prod_discount, c.category_name
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $search_query, $search_query, $search_query);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
    $category_check_stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/home.css">
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>


    <style>
        body{
            background-color: #f0f0f0;    
        }
        .container {
            max-width: 960px;
            margin: auto;
            padding: 20px;
        }

        .navbar {
            top: 0px;
        }

        .product-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .card-body {
            flex-grow: 1;
        }

        .product-card:hover {
            transform: scale(1px);
            box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.1);
        }

        .card-img-top {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .card-text {
            font-size: 0.9rem;
            color: #555;
        }

        .star-yellow {
            color: #FFD700;
        }

        .btn-primary {
            background-color: #FF8225;
            border-color: #FF8225;
        }

        .btn-primary:hover {
            background-color: #e36f10;
            border-color: #e36f10;
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

        footer {
            margin-top: 200px;
            margin-bottom: 0px;
        }
    </style>
</head>

<body>

    <?php include '../includes/header.php' ?>

    <div class="container mt-5">
        <h2 class="mb-4 text-center" style="margin-top: 80px;">Search Results</h2>
        <hr class="under">
        <div class="row justify-content-center">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product):
                    $prod_code = htmlspecialchars($product['prod_code']);
                    $prod_qoh = htmlspecialchars($product['prod_qoh']);
                    $prod_name = htmlspecialchars($product['prod_name']);
                    $prod_desc = htmlspecialchars($product['prod_desc']);
                    $prod_price = number_format($product['prod_price'], 2);
                    $prod_img = htmlspecialchars($product['prod_img']);
                    $avg_rating = number_format($product['avg_rating'], 1);
                    $review_count = $product['review_count'];
                    $prod_discount = $product['prod_discount'] > 0 ? number_format($product['prod_discount'], 2) : null;

                    // Calculate star rating
                    $full_stars = floor($avg_rating);
                    $half_star = ($avg_rating - $full_stars) >= 0.5;
                    $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
                ?>
                    <div class="col-6 col-sm-4 col-md-4 col-lg-3 mb-4">
                        <div class="card product-card">
                            <img src="../<?php echo $prod_img; ?>" class="card-img-top" alt="<?php echo $prod_name; ?>" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $prod_name; ?></h5>
                                <p class="card-text">Price:
                                    <?php if ($prod_discount > 0) { ?>
                                        <span style="text-decoration: line-through;">₱<?php echo number_format($prod_price, 2); ?></span>
                                        <span style="color: #FF8225; font-weight: bold;">₱<?php echo number_format($prod_discount, 2); ?></span>
                                    <?php } else { ?>
                                        ₱<?php echo number_format($prod_price, 2); ?>
                                    <?php } ?>
                                </p>

                                <p class="card-text">Ratings:
                                    <?php for ($i = 0; $i < $full_stars; $i++) {
                                        echo '<i class="fas fa-star star-yellow"></i>';
                                    } ?>
                                    <?php if ($half_star) echo '<i class="fas fa-star-half-alt star-yellow"></i>'; ?>
                                    <?php for ($i = 0; $i < $empty_stars; $i++) {
                                        echo '<i class="far fa-star star-yellow"></i>';
                                    } ?>
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
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted text-center">No products found matching your search criteria.</p>
            <?php endif; ?>
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

    <!-- Footer -->
    <?php include '../includes/footer.php' ?>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="./js/notif.js"></script>
    <script>
        function changeQuantity(action, productId) {
            const quantityInput = document.getElementById(`quantity-${productId}`);
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
    </script>
</body>

</html>
<?php
// Database connection
include './includes/db_connect.php';

// Get category code and filter type from URL or default to 'all'
$category_code = $_GET['code'];
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Fetch category details
$category_sql = "SELECT category_name, category_img, category_desc FROM category_tbl WHERE category_code = ?";
$category_stmt = $conn->prepare($category_sql);
$category_stmt->bind_param("s", $category_code);
$category_stmt->execute();
$category_result = $category_stmt->get_result();
$category = $category_result->fetch_assoc();

// Set up base query for products
$product_sql = "SELECT prod_code, prod_name, prod_desc, prod_price, prod_discount, prod_img FROM product_tbl WHERE category_code = ?";

// Modify query based on filter
if ($filter === 'new') {
    $product_sql .= " AND DATE_ADD(created_at, INTERVAL 30 DAY) >= NOW()";
} elseif ($filter === 'old') {
    $product_sql .= " AND DATE_ADD(created_at, INTERVAL 30 DAY) < NOW()";
} elseif ($filter === 'discounted') {
    $product_sql .= " AND prod_discount > 0";
}

// Prepare and execute statement
$product_stmt = $conn->prepare($product_sql);
$product_stmt->bind_param("s", $category_code);
$product_stmt->execute();
$product_result = $product_stmt->get_result();

// Check if any products are available for the selected filter
$no_products_message = '';
if ($product_result->num_rows === 0) {
    if ($filter === 'new') {
        $no_products_message = 'No new products available yet.';
    } elseif ($filter === 'old') {
        $no_products_message = 'No old products available yet.';
    } elseif ($filter === 'discounted') {
        $no_products_message = 'No discounted products available yet.';
    } else {
        $no_products_message = 'No products found for this category.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - <?php echo htmlspecialchars($category['category_name']); ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="./css/home.css">
    <style>
        .card-img-top {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }
        .quantity-control {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        .quantity-control button {
            width: 30px;
            height: 30px;
            padding: 0;
        }
        .quantity-control input {
            width: 40px;
            text-align: center;
        }
        .add-to-cart {
            width: 100%;
            font-size: 14px;
            padding: 5px 10px;
        }
        .category-img {
            width: 100%;
            height: auto;
            margin-bottom: 20px;
            object-fit: cover;
            margin-top: 100px;
        }
        .category-description {
            margin-bottom: 20px;
            font-size: 1.1em;
            color: #333;
        }
        .container {
            margin-bottom: 50px;
        }
        .filter-form {
            margin-bottom: 20px;
        }
        .no-products-message {
            text-align: center;
            font-size: 1.2em;
            color: #888;
        }
        .discount-info {
            color: red;
            font-weight: bold;
        }
        .discount-info .original-price {
            text-decoration: line-through;
            color: #888;
        }
        .aboutCat{
            text-align: center;
            padding: 10px;
        }
        .category-description{
            text-align: center;
            margin-bottom: 20px;
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
    </style>
</head>
<body>
<?php include './includes/header.php' ?>

<div class="container">
    <!-- Category Image Section -->
    <?php if (!empty($category['category_img'])): ?>
        <img src="./<?php echo htmlspecialchars($category['category_img']); ?>" alt="Category Image" class="category-img">
    <?php endif; ?>

    <!-- Category Description Section -->
    <h4 class="aboutCat">About <?php echo htmlspecialchars($category['category_name']); ?> Category</h4>
    <?php if (!empty($category['category_desc'])): ?>
        <div class="category-description">
            <?php echo nl2br(htmlspecialchars($category['category_desc'])); ?>
        </div>
    <?php endif; ?>

    <h1 class="my-4"><?php echo htmlspecialchars($category['category_name']); ?></h1>

    <!-- Filter Form -->
    <form method="get" action="" class="filter-form">
        <input type="hidden" name="code" value="<?php echo htmlspecialchars($category_code); ?>">
        <div class="form-group">
            <label for="filter">Filter by:</label>
            <select id="filter" name="filter" class="form-control" onchange="this.form.submit()">
                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All</option>
                <option value="new" <?php echo $filter === 'new' ? 'selected' : ''; ?>>New</option>
                <option value="old" <?php echo $filter === 'old' ? 'selected' : ''; ?>>Old</option>
                <option value="discounted" <?php echo $filter === 'discounted' ? 'selected' : ''; ?>>Discounted</option>
            </select>
        </div>
    </form>

    <!-- Product Listings -->
    <div class="row">
        <?php
        if ($no_products_message) {
            echo '<div class="col-12 no-products-message">' . htmlspecialchars($no_products_message) . '</div>';
        } else {
            while ($product = $product_result->fetch_assoc()) {
                echo '<div class="col-lg-2 col-md-3 col-sm-4 col-6 mb-4">
                    <div class="card h-100">
                        <a href="product-details.php?id=' . htmlspecialchars($product["prod_code"]) . '">
                            <img class="card-img-top" src="./' . htmlspecialchars($product["prod_img"]) . '" alt="' . htmlspecialchars($product["prod_name"]) . '">
                        </a>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="product-details.php?id=' . htmlspecialchars($product["prod_code"]) . '">' . htmlspecialchars($product["prod_name"]) . '</a>
                            </h5>';

                if ($product["prod_discount"] > 0) {
                    echo '<h6 class="discount-info">
                            <span class="original-price">$' . number_format($product["prod_price"], 2) . '</span>
                            $' . number_format($product["prod_discount"], 2) . '
                        </h6>';
                } else {
                    echo '<h6>$' . number_format($product["prod_price"], 2) . '</h6>';
                }

                echo '<div class="quantity-control">
                        <button class="incBtn1 btn btn-outline-secondary btn-sm" type="button" onclick="changeQuantity(\'decrease\', \'' . htmlspecialchars($product["prod_code"]) . '\')">-</button>
                        <input type="text" id="quantity-' . htmlspecialchars($product["prod_code"]) . '" class="form-control form-control-sm mx-1" value="1" readonly style="width:50px; background-color: #FF8225; color: #f0f0f0; font-weight: 500; font-size:12px;">
                        <button class="incBtn2 btn btn-outline-secondary btn-sm" type="button" onclick="changeQuantity(\'increase\', \'' . htmlspecialchars($product["prod_code"]) . '\')">+</button>
                    </div>
                    <button class="btn btn-outline-success add-to-cart" type="button" onclick="addToCart(\'' . htmlspecialchars($product["prod_code"]) . '\')">Add to Cart</button>
                </div>
            </div>
        </div>';
            }
        }
        ?>
    </div>
</div>

<!-- Footer -->
<?php include './includes/footer.php' ?>


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

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="./js/notif.js"></script>
<script>
    function changeQuantity(action, productId) {
        const quantityInput = document.getElementById(`quantity-${productId}`);
        let currentQuantity = parseFloat(quantityInput.value);

        if (action === 'increase') {
            quantityInput.value = (currentQuantity + 0.25).toFixed(2);
        } else if (action === 'decrease') {
            if(currentQuantity > 1){
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

<?php
$conn->close();
?>

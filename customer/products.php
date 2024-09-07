<?php
// Database connection
include '../includes/db_connect.php';

// Fetch categories for the filter
$category_sql = "SELECT category_code, category_name FROM category_tbl";
$categories = $conn->query($category_sql);

// Determine selected category and sort option from the filter form
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';
$sort_option = isset($_GET['sort']) ? $_GET['sort'] : '';

// Build sorting SQL clause
$sort_sql = '';
switch ($sort_option) {
    case 'price_asc':
        $sort_sql = 'ORDER BY prod_price ASC';
        break;
    case 'price_desc':
        $sort_sql = 'ORDER BY prod_price DESC';
        break;
    case 'name_asc':
        $sort_sql = 'ORDER BY prod_name ASC';
        break;
    case 'name_desc':
        $sort_sql = 'ORDER BY prod_name DESC';
        break;
    default:
        $sort_sql = 'ORDER BY prod_name ASC'; // Default sort option
}

// Prepare SQL statement with placeholders
$product_sql = "SELECT * FROM product_tbl";
$params = [];
$types = '';
if (!empty($selected_category)) {
    $product_sql .= " WHERE category_code = ?";
    $params[] = $selected_category;
    $types .= 's'; // 's' denotes string type
}
$product_sql .= " " . $sort_sql;

// Prepare and execute the query
$stmt = $conn->prepare($product_sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Meat-to-Door Delivery</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/home.css">
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">
    <style>
        body {
            background-color: #f8f9fa;
            color: #333;
        }

        .header,
        .footer {
            background-color: #a72828;
            /* Deep red */
            color: #fff;
        }

        .header h1 {
            margin: 0;
        }

        .product-card {
            display: flex;
            flex-direction: column;
            border: 1px solid #ddd;
            border-radius: 10px;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
            overflow: hidden;
            margin-left: -8px;
            /* Adjust left and right margin */
            margin-right: -8px;

        }

        .product-card:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .product-card img {
            width: 100%;
            height: 200px;
            /* Adjust height as needed */
            object-fit: cover;
        }

        .product-card-body {
            flex: 1;
            /* Make the body take up available space */
            padding: 1rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .btn-custom {
            background-color: #FF8225;
            /* Theme color */
            color: #fff;
            border: none;
        }

        .btn-custom:hover {
            background-color: #e67e22;
            color: #fff;
        }

        .footer {
            text-align: center;
            padding: 1rem;
        }

        .form-group label {
            font-weight: bold;
        }

        .filter-panel {
            background-color: #fff;
            border-radius: 10px;
            padding: 1rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .filter-panel select {
            cursor: pointer;
        }

        .price-original {
            text-decoration: line-through;
            color: #888;
        }

        .price-discounted {
            color: #FF8225;
        }

        .navbar {
            top: 0;
        }

        .navbar-light .navbar-nav .nav-link.act2 {
            color: #ffffff;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <?php include '../includes/header.php' ?>

    <div class="container mt-4">
        <h2 class="text-center mb-4" style="margin-top:100px;">Product List</h2>

        <!-- Filter and Sort Form -->
        <div class="filter-panel mb-4">
            <form method="GET" action="products.php">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="categorySelect">Filter by Category</label>
                        <select class="form-control" id="categorySelect" name="category">
                            <option value="">All Categories</option>
                            <?php
                            if ($categories->num_rows > 0) {
                                while ($cat = $categories->fetch_assoc()) {
                                    $selected = ($selected_category == $cat['category_code']) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($cat['category_code']) . '" ' . $selected . '>' . htmlspecialchars($cat['category_name']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="sortSelect">Sort By</label>
                        <select class="form-control" id="sortSelect" name="sort">
                            <option value="">Default</option>
                            <option value="price_asc" <?php echo ($sort_option == 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php echo ($sort_option == 'price_desc') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name_asc" <?php echo ($sort_option == 'name_asc') ? 'selected' : ''; ?>>Name: A to Z</option>
                            <option value="name_desc" <?php echo ($sort_option == 'name_desc') ? 'selected' : ''; ?>>Name: Z to A</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-custom btn-block">Apply</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Products Display -->
        <div class="row">
            <?php
            if ($products->num_rows > 0) {
                while ($row = $products->fetch_assoc()) {
                    echo '<div class="col-6 col-md-3 mb-4">';
                    echo '<div class="card product-card">';
                    echo '<img src="../' . htmlspecialchars($row['prod_img']) . '" class="card-img-top product-img" alt="' . htmlspecialchars($row['prod_name']) . '">';
                    echo '<div class="card-body product-card-body">';
                    echo '<h5 class="card-title">' . htmlspecialchars($row['prod_name']) . '</h5>';

                    // Display pricing
                    if ($row['prod_discount'] > 0) {
                        $original_price = number_format($row['prod_price'], 2);
                        $discounted_price = number_format($row['prod_price'] - $row['prod_discount'], 2);
                        echo '<p class="card-text">Price: <span class="price-original">₱' . $original_price . '</span><br><strong class="price-discounted">₱' . $discounted_price . '</strong></p>';
                    } else {
                        $price = number_format($row['prod_price'], 2);
                        echo '<p class="card-text">Price: <strong>₱' . $price . '</strong></p>';
                    }

                    echo '<a href="product-details.php?id=' . urlencode($row['prod_code']) . '" class="btn btn-custom mt-2">See Details</a>';
                    echo '</div>'; // End of card-body
                    echo '</div>'; // End of card
                    echo '</div>'; // End of column
                }
            } else {
                echo '<div class="col-12"><p class="text-center">No products found.</p></div>';
            }
            ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php' ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../js/notif.js"></script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>
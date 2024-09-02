<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Meat-To-Door Delivery</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/home.css">
    <link rel="icon" href="../img/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            background-color: #f0f0f0;
        }

        .navbar {
            top: 0px;
        }

        .product-img {
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }

        .navbar-light .navbar-nav .nav-link.act6 {
            color: #ffffff;
        }

        .cart-summary {
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin-top: 15px;
        }

        .item-checkbox:checked~.item-total {
            font-weight: bold;
            color: #007bff;
        }

        .remove-item {
            cursor: pointer;
            color: #ffffff;
            transition: color 0.2s;
        }

        .card-body {
            padding: 20px;
        }

        .item-total {
            font-weight: normal;
            transition: color 0.2s;
        }

        .titCart {
            -webkit-text-stroke: 1px #bc0000;
            color: #FF8225;
            font-weight: 800;
        }

        .CObtn {
            background-color: #bc0000;
            border: none;
            padding: 10px;
            transition: background-color 0.5s ease;
        }

        .CObtn:hover {
            background-color: #FF8225;

        }
    </style>
</head>

<body>

    <?php include '../includes/header.php';
    include '../includes/db_connect.php';
    // Fetch the shop status
    $sql = "SELECT shopstatus FROM shopstatus_tbl WHERE shopstatus = 'Close'";
    $result = $conn->query($sql);
    $is_shop_closed = $result->num_rows > 0; // True if shop is closed
    ?>

    <!-- Cart Section -->
    <div class="container my-5">
        <h2 class="titCart text-center mb-4" style="margin-top: 150px;">Shopping Cart</h2>
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">
                                    <input type="checkbox" id="select-all" title="Select All">
                                </th>
                                <th scope="col">Product</th>
                                <th scope="col">Price</th>
                                <th scope="col">Quantity</th>
                                <th scope="col">Total</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody id="cart-items">
                            <?php
                            include '../includes/db_connect.php';
                            // Initialize flag for empty cart
                            $is_cart_empty = true;

                            // Check if the user is logged in
                            if (!isset($_SESSION['cust_id'])) {
                                echo '<tr><td colspan="6" class="text-center">User not logged in</td></tr>';
                                exit();
                            }

                            $cust_id = $_SESSION['cust_id'];

                            // Fetch cart items from the database
                            $sql = "SELECT cart_table.*, p.prod_name, p.prod_desc, p.prod_price, p.prod_discount, p.prod_img, p.prod_qoh
                            FROM cart_table
                            JOIN product_tbl p ON cart_table.prod_code = p.prod_code
                            WHERE cart_table.cust_id = ?";

                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param('s', $cust_id);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                $is_cart_empty = false; // Cart is not empty
                                while ($row = $result->fetch_assoc()) {
                                    // Check if there's a discount; if so, use it, otherwise use the original price
                                    $display_price = $row['prod_discount'] > 0 ? $row['prod_discount'] : $row['prod_price'];
                                    $total_price = $row['cart_qty'] * $display_price;

                                    echo '<tr data-prod-code="' . htmlspecialchars($row['prod_code']) . '" data-available-qty="' . htmlspecialchars($row['prod_qoh']) . '">';
                                    echo '<td><input type="checkbox" class="item-checkbox" data-price="' . htmlspecialchars($display_price) . '" data-available-qty="' . htmlspecialchars($row['prod_qoh']) . '"></td>';
                                    echo '<td><div class="d-flex align-items-center">';
                                    echo '<img src="../' . htmlspecialchars($row['prod_img']) . '" class="product-img mr-3" alt="' . htmlspecialchars($row['prod_name']) . '">';
                                    echo '<div><h5 class="mb-0">' . htmlspecialchars($row['prod_name']) . '</h5></div>';
                                    echo '</div></td>';
                                    echo '<td>₱' . number_format($display_price, 2) . '</td>';
                                    echo '<td><input type="number" class="form-control item-quantity" value="' . htmlspecialchars($row['cart_qty']) . '" min="1"></td>';
                                    echo '<td class="item-total">₱' . number_format($total_price, 2) . '</td>';
                                    echo '<td>';
                                    echo '<button type="button" class="btn btn-danger btn-sm remove-item" data-prod-code="' . htmlspecialchars($row['prod_code']) . '">Remove</button>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="6" class="text-center">No items in cart</td></tr>';
                            }

                            $stmt->close();
                            $conn->close();
                            ?>

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card cart-summary">
                    <div class="card-body">
                        <h5 class="card-title">Cart Summary</h5>
                        <hr>
                        <p class="card-text">Subtotal: <span class="float-right" id="subtotal">₱0.00</span></p>
                        <p class="card-text">Item(s): <span class="float-right" id="selected-items">0</span></p>
                        <h5 class="card-text">Total: <span class="float-right" id="total">₱0.00</span></h5>
                        <hr>
                        <form action="checkout.php" method="POST" id="checkout-form">
                            <input type="hidden" name="selected-items" id="hidden-selected-items" value="0">
                            <input type="hidden" name="total" id="hidden-total" value="0.00">
                            <input type="hidden" name="product-details" id="hidden-product-details" value='{}'>
                            <button type="submit" class="CObtn btn-primary btn-block">Proceed to Checkout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../js/notif.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var isShopClosed = <?php echo json_encode($is_shop_closed); ?>;

            // Function to update cart totals
            function updateCart() {
                let subtotal = 0;
                let selectedItems = 0;
                let productDetails = [];

                // Calculate totals and update subtotal
                document.querySelectorAll('#cart-items tr').forEach(function(row) {
                    let checkbox = row.querySelector('input.item-checkbox');
                    if (checkbox.checked) {
                        let price = parseFloat(checkbox.getAttribute('data-price'));
                        let quantity = parseFloat(row.querySelector('input.item-quantity').value);
                        let total = price * quantity;
                        row.querySelector('td.item-total').textContent = `₱${total.toFixed(2)}`;
                        subtotal += total;
                        selectedItems++;

                        // Add product details
                        let prodName = row.querySelector('td img').alt;
                        let prodCode = row.getAttribute('data-prod-code');
                        productDetails.push({
                            code: prodCode,
                            name: prodName,
                            quantity: quantity,
                            price: price
                        });
                    } else {
                        row.querySelector('td.item-total').textContent = `₱0.00`;
                    }
                });

                // Update subtotal and selected items count
                document.getElementById('subtotal').textContent = `₱${subtotal.toFixed(2)}`;
                document.getElementById('selected-items').textContent = selectedItems;

                // Update total
                document.getElementById('total').textContent = `₱${subtotal.toFixed(2)}`;

                // Set hidden inputs for checkout
                document.getElementById('hidden-selected-items').value = selectedItems;
                document.getElementById('hidden-total').value = subtotal.toFixed(2);
                document.getElementById('hidden-product-details').value = JSON.stringify(productDetails);

                // Send data to server to store in session
                $.ajax({
                    type: 'POST',
                    url: 'save_cart.php', // PHP file to handle session storage
                    data: {
                        selected_items: selectedItems,
                        total: subtotal.toFixed(2),
                        product_details: JSON.stringify(productDetails)
                    },
                    success: function(response) {
                        // Handle success if needed
                    },
                    error: function() {
                        alert('An error occurred while saving cart details.');
                    }
                });
            }

            // Select all checkbox event
            document.getElementById('select-all').addEventListener('change', function() {
                let isChecked = this.checked;
                document.querySelectorAll('input.item-checkbox').forEach(function(checkbox) {
                    checkbox.checked = isChecked;
                });
                updateCart();
            });

            // Quantity change event
            document.querySelectorAll('input.item-quantity').forEach(function(input) {
                input.addEventListener('change', function() {
                    updateCart(); // Update totals when quantity changes
                });
            });

            // Checkbox change event
            document.querySelectorAll('input.item-checkbox').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    updateCart(); // Update totals when checkbox selection changes
                });
            });

            // Initial update of cart totals
            updateCart();

            // Remove item button click event
            document.querySelectorAll('.remove-item').forEach(function(button) {
                button.addEventListener('click', function() {
                    let prodCode = button.getAttribute('data-prod-code');

                    // Send AJAX request to remove item
                    $.ajax({
                        type: 'POST',
                        url: 'remove_item.php', // Adjust the URL to point to the new PHP file
                        data: {
                            remove_prod_code: prodCode
                        },
                        success: function(response) {
                            let result = JSON.parse(response);
                            if (result.status === 'success') {
                                location.reload(); // Reload the page on success
                            } else {
                                alert('Failed to remove item. Please try again.');
                            }
                        },
                        error: function() {
                            alert('An error occurred. Please try again.');
                        }
                    });
                });
            });

            // Checkout button click event
            document.querySelector('.CObtn').addEventListener('click', function(event) {
                let productDetails = document.getElementById('hidden-product-details').value;

                if (!productDetails || JSON.parse(productDetails).length === 0) {
                    event.preventDefault(); // Prevent form submission
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Items Selected',
                        text: 'Please select at least one item before proceeding to checkout.',
                        confirmButtonText: 'OK'
                    });
                } else if (isShopClosed) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Shop Closed',
                        text: 'The shop is currently closed. Please try again later.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            var isCartEmpty = <?php echo json_encode($is_cart_empty); ?>;

            // Checkout button click event
            document.querySelector('.CObtn').addEventListener('click', function(event) {
                if (isCartEmpty) {
                    event.preventDefault(); // Prevent form submission
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cart is Empty',
                        text: 'Your cart is empty. Please add items to the cart before proceeding to checkout.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Function to validate cart quantities
            function validateQuantities() {
                let isValid = true;
                let invalidItems = [];

                document.querySelectorAll('#cart-items tr').forEach(function(row) {
                    let checkbox = row.querySelector('input.item-checkbox');
                    if (checkbox.checked) {
                        let prodCode = row.getAttribute('data-prod-code');
                        let quantity = parseFloat(row.querySelector('input.item-quantity').value);

                        // Fetch product quantity from hidden field
                        let availableQty = parseFloat(checkbox.getAttribute('data-available-qty'));

                        if (quantity > availableQty) {
                            isValid = false;
                            invalidItems.push({
                                code: prodCode,
                                name: row.querySelector('td img').alt,
                                availableQty: availableQty,
                                requestedQty: quantity
                            });
                        }
                    }
                });

                if (!isValid) {
                    let message = 'The following items have quantities exceeding the available stock:\n';
                    invalidItems.forEach(item => {
                        message += `${item.name} (Requested: ${item.requestedQty}, Available: ${item.availableQty})\n`;
                    });
                    Swal.fire({
                        icon: 'warning',
                        title: 'Quantity Exceeds Stock',
                        text: message,
                        confirmButtonText: 'OK'
                    });
                }

                return isValid;
            }

            // Checkout button click event
            document.querySelector('.CObtn').addEventListener('click', function(event) {
                if (!validateQuantities()) {
                    event.preventDefault(); // Prevent form submission
                }
            });
        });
    </script>
</body>

</html>
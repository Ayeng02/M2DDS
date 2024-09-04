<?php 
error_reporting(E_ALL & ~E_NOTICE) ;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Meat-To-Door Delivery</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel="stylesheet" href="../css/home.css">
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #f5f5f5, #d8d8d8);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .checkout-container {
            margin-top: 20px;
            margin-bottom: 50px;
            padding: 30px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .titCheckout {
            color: #bc0000;
            font-weight: 900;
            margin-bottom: 2rem;
            text-align: center;
            font-size: 2.5rem;
        }
        .form-control, .form-control:focus {
            border-color: #FF8225;
            border-radius: 8px;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            box-shadow: none;
        }
        .form-group label {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .btn-primary {
            background-color: #bc0000;
            border: none;
            border-radius: 8px;
            padding: 12px;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #FF8225;
        }
        .form-group select {
            background: #f8f8f8;
        }
        .form-group input {
            background: #f8f8f8;
        }
        .form-control::placeholder {
            color: #888;
        }
        .form-group {
            position: relative;
        }
        .form-group .required::after {
            content: '*';
            color: #d9534f;
            margin-left: 0.2rem;
        }
        .success-message, .error-message {
            display: none;
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            border-radius: 8px;
        }
        .success-message {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .error-message {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        .cart-summary {
        margin-top: 20px;
    }
    .card {
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .card-body {
        padding: 20px;
    }
    .card-title {
        font-size: 1.5rem;
        color: #bc0000;
        margin-bottom: 1rem;
    }
    .table thead th {
        background-color: #FF8225;
        color: #fff;
        font-weight: bold;
    }
    .table tfoot th {
        font-weight: bold;
        background-color: #f8f8f8;
    }
    .table tfoot td {
        font-weight: bold;
        background-color: #f8f8f8;
    }
    .table td, .table th {
        vertical-align: middle;
    }
    .btn-secondary {
    background-color: #6c757d;
    border: none;
    border-radius: 8px;
    padding: 12px;
    color: #fff;
    transition: background-color 0.3s ease;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php
    session_start();
    include '../includes/db_connect.php'; 

    // Fetch barangay data
    $barangayQuery = "SELECT Brgy_Name, Brgy_df FROM Brgy_Tbl";
    $barangayResult = mysqli_query($conn, $barangayQuery);

    // Retrieve cart data from session
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    $selectedItems = $cart['selected_items'] ?? 0;
    $total = $cart['total'] ?? 0;
    $productDetails = $cart['product_details'] ?? [];
    ?>
    <div class="container checkout-container">
        <h2 class="titCheckout">Checkout</h2>
        <h4 class="text-center mb-4">Contact and Delivery Address Information</h4>
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <form id="checkout-form">
                    <input type="hidden" id="prod-codes" name="prod-codes" value="<?php echo implode(',', array_column($productDetails, 'code')); ?>">
                    <div class="form-group">
                        <label for="full-name" class="required">Full Name</label>
                        <input type="text" class="form-control" id="full-name" name="full-name" placeholder="Enter Full Name" required>
                    </div>
                    <div class="form-group">
                        <label for="phone-number" class="required">Phone Number</label>
                        <input type="tel" class="form-control" id="phone-number" name="phone-number" placeholder="Enter Phone Number" required>
                    </div>
                    <div class="form-group">
                        <label for="barangay" class="required">Barangay</label>
                        <select class="form-control" id="barangay" name="barangay" required>
                            <option value="" data-df="0">Select Barangay</option>
                            <?php while($row = mysqli_fetch_assoc($barangayResult)) { ?>
                                <option value="<?php echo $row['Brgy_Name']; ?>" data-df="<?php echo $row['Brgy_df']; ?>"><?php echo $row['Brgy_Name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="purok" class="required">Purok/Street</label>
                        <input type="text" class="form-control" id="purok" name="purok" placeholder="Enter Purok/Street" required>
                    </div>
                    <div class="form-group">
                        <label for="province" class="required">Province</label>
                        <select class="form-control" id="province" name="province" required>
                            <option value="Davao del Norte">Davao del Norte</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mode-of-payment" class="required">Mode of Payment</label>
                        <select class="form-control" id="mode-of-payment" name="mode-of-payment" required>
                            <option value="COD">Cash on Delivery</option>
                        </select>
                    </div>
                    <div class="cart-summary">
                        <h4 class="text-center mb-4">Cart Summary</h4>
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Order Details</h5>
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($productDetails as $product) { ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                <td><?php echo $product['quantity']; ?></td>
                                                <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Total Items:</th>
                                            <td colspan="2"><?php echo $selectedItems; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Delivery Fee:</th>
                                            <td colspan="2" id="delivery-fee">₱0.00</td>
                                        </tr>
                                        <tr>
                                            <th>Total Price:</th>
                                            <td colspan="2" id="total-price">₱<?php echo number_format($total, 2); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn-primary btn btn-lg" style="margin-top: 20px;">Place Order</button>
                        <button type="button" class="btn-secondary btn btn-lg" style="margin-top: 20px; margin-left: 10px;" onclick="window.location.href='cart.php';">Cancel</button>
                    </div>
                    <div id="feedback" class="mt-4">
                        <div class="success-message" id="success-message">Your order has been placed successfully!</div>
                        <div class="error-message" id="error-message">There was an error placing your order. Please try again.</div>
                    </div>
                </form>
            </div>
        </div>
    </div>

<!-- Bootstrap Modal HTML -->
<div class="modal fade" id="orderConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="orderConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center" id="orderConfirmationModalLabel">
                    <i class="fas fa-info-circle mr-2"></i> <!-- Note icon -->
                    Important Notice
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Note:</strong> You can cancel your order while it is still in the <strong>Pending</strong> or <strong>Processing</strong> stage. However, once it is <strong>Shipped</strong>, cancellation is not possible.</p>
                <p>We are committed to offering a service where quality meets affordability. Thank you for choosing us!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmOrderBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>



    <?php include '../includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="./js/notif.js"></script>
    <script>
$(document).ready(function() {
    function updateTotalPrice() {
        const deliveryFee = parseFloat($('#barangay').find(':selected').data('df')) || 0;
        const total = parseFloat(<?php echo $total; ?>) + deliveryFee;
        $('#delivery-fee').text('₱' + deliveryFee.toFixed(2));
        $('#total-price').text('₱' + total.toFixed(2));
    }

    $('#barangay').on('change', updateTotalPrice);

    $('#checkout-form').on('submit', function(event) {
        event.preventDefault();

        // Show the confirmation modal with pop-up effect
        $('#orderConfirmationModal').modal('show');
    });

    $('#confirmOrderBtn').on('click', function() {
        // Collect form data
        const formData = {
            'prod-codes': $('#prod-codes').val(),
            'full-name': $('#full-name').val(),
            'phone-number': $('#phone-number').val(),
            'barangay': $('#barangay').val(),
            'purok': $('#purok').val(),
            'province': $('#province').val(),
            'mode-of-payment': $('#mode-of-payment').val(),
            'total-price': parseFloat($('#total-price').text().replace('₱', '')) || 0,
            'delivery-fee': parseFloat($('#delivery-fee').text().replace('₱', '')) || 0
        };

        $.ajax({
            url: 'insert_order.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#orderConfirmationModal').modal('hide'); // Hide the modal
                    $('#success-message').show().text('Order placed successfully!');
                    $('#error-message').hide();

                    // Redirect to customer landing page after a short delay
                    setTimeout(function() {
                        window.location.href = 'customerLandingPage.php';
                    }, 2000); // Delay of 2 seconds for the success message to be visible
                } else {
                    $('#success-message').hide();
                    $('#error-message').text('Error: ' + (response.error || 'An error occurred while placing your order.')).show();
                }
            },
            error: function(xhr, status, error) {
                let errorMsg = 'An unexpected error occurred: ' + error + '. Please try again.';
                
                // Handle cases where the response is not JSON
                if (xhr.responseText && xhr.responseText.trim().startsWith('<')) {
                    errorMsg = 'An error occurred on the server. Please check your code or contact support.';
                    console.error('Server returned HTML instead of JSON. Check your PHP code for errors or redirects.', xhr.responseText);
                }

                $('#success-message').hide();
                $('#error-message').text(errorMsg).show();
            }
        });
    });

    // Update the total price on page load in case a barangay is pre-selected
    updateTotalPrice();
});
</script>

</script>

</body>
</html>

<?php
include '../includes/sf_getEmpInfo.php'; // Existing employee info retrieval

// Get the parameters from the URL
$cust_id = $_GET['cust_id'];
$order_barangay = urldecode($_GET['order_barangay']);
$order_date = urldecode($_GET['order_date']);

// Get the emp_id from the session
$emp_id = $_SESSION['emp_id']; // Assuming emp_id is stored in session

// Query to fetch order details based on cust_id, order_barangay, emp_id, and where the transact_status is 'Ongoing'
$orderDetailsQuery = "
    SELECT 
        o.cust_id,
        CONCAT(c.f_name, ' ', c.l_name) AS cust_name,
        CONCAT(o.order_purok, ' ', o.order_barangay, ' ', o.order_province) AS deliv_address,
        o.prod_code,
        o.order_id,
        o.order_date,
        o.status_code,
        p.prod_name,
        o.order_qty,
        o.order_total,
        b.Brgy_df, -- Include the Brgy_df column from brgy_tbl
        dt.transact_status -- Join with delivery_transactbl to get transact_status
    FROM 
        order_tbl o
    JOIN 
        brgy_tbl b ON o.order_barangay = b.brgy_name
    JOIN
        customers c ON o.cust_id = c.cust_id
    JOIN
        product_tbl p ON o.prod_code = p.prod_code
    JOIN 
        delivery_transactbl dt ON o.order_id = dt.order_id -- Joining with delivery_transactbl
    WHERE 
        o.cust_id = '$cust_id' 
        AND o.order_barangay = '$order_barangay'
        AND o.order_date = '$order_date'
        AND dt.transact_status = 'Ongoing'  -- Filter for 'Ongoing' status from delivery_transactbl
        AND dt.shipper_id = '$emp_id'  -- Filter for the emp_id of the shipper
    ORDER BY 
        o.order_date DESC;
";

$result = mysqli_query($conn, $orderDetailsQuery);

if (!$result) {
    die("Query failed: " . mysqli_error($conn)); // Print error message
}

// Initialize variables for total calculation
$grand_total = 0;
$brgy_fee = 0;
$products = []; // Store products for the modal display

// Fetch results
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $cust_name = $row['cust_name'];
        $deliv_address = $row['deliv_address'];
        $prod_name = $row['prod_name'];
        $order_total = $row['order_total'];
        $brgy_fee = $row['Brgy_df']; // Barangay Delivery Fee
        $grand_total += $order_total; // Sum the order_total for all products
        $products[] = $row; // Store each product for later use
    }
} else {
    $no_orders_message = "No ongoing order details found for this customer.";
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="icon" href="../img/mtdd_logo.png" type="image/x-icon">

    <style>
        .card {
            transition: transform 0.2s;
        }

        .card:hover {
            transform: scale(1.02);
        }

        .table th,
        .table td {
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <!-- Back to Dashboard -->
        <div class="mb-4">
            <a href="shipper.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <div class="mb-3 btn-group" role="group" aria-label="Order Actions">
            <button class="btn btn-primary" id="scanButton">
                <i class="fas fa-truck-fast"></i> Deliver Order
            </button>
            <button class="btn btn-danger" id="failedOrder">
                <i class="fas fa-circle-exclamation"></i> Pick-up Unsuccessful
            </button>
        </div>

        <!-- ORDERS DETAILS Section -->
        <div class="order-details-section mt-4">
            <h4>Order Details for Customer: <?php echo $cust_id; ?></h4>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Customer Name: <?php echo isset($cust_name) ? $cust_name : ''; ?></h5>
                    <p class="card-text">
                        <strong>Delivery Address:</strong> <?php echo isset($deliv_address) ? $deliv_address : ''; ?><br>
                    </p>
                    <!-- Product Table -->
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Product Name</th>
                                <th>Unit Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($no_orders_message)) {
                                echo "<tr><td colspan='2'>{$no_orders_message}</td></tr>";
                            } else {
                                foreach ($products as $product) {
                                    echo "<tr data-toggle='modal' data-target='#productModal' data-prod-name='{$product['prod_name']}' data-order-total='{$product['order_total']}' data-order-id='{$product['order_id']}'>
                                            <td>{$product['order_id']}</td>
                                            <td>{$product['prod_name']}</td>
                                            <td>₱" . number_format($product['order_total'], 2) . "</td>
                                        </tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                    <!-- Grand Total -->
                    <h6 class="mt-3">Total Amount (Including Delivery Fee): ₱<?php echo number_format($grand_total + $brgy_fee, 2); ?></h6>
                </div>
            </div>
        </div>

        <!-- Product Modal -->
        <div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productModalLabel">Product Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Product Name:</strong> <span id="modal-prod-name"></span></p>
                        <p><strong>Order Total:</strong> ₱<span id="modal-order-total"></span></p>
                        <!-- Add more details here as needed -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cash Input Modal -->
        <div class="modal fade" id="cashInputModal" tabindex="-1" role="dialog" aria-labelledby="cashInputModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cashInputModalLabel">Confirm Delivery</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="cashInputForm">
                            <p>Please enter the cash amount for the delivery:</p>
                            <input type="hidden" name="order_ids" id="order_ids" value="">
                            <input type="number" name="order_cash" id="cashAmount" class="form-control" placeholder="Cash Amount">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Confirm Delivery</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

    <script>
        // Populate product modal with data
        $('#productModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var prodName = button.data('prod-name'); // Extract info from data-* attributes
            var orderTotal = button.data('order-total');
            var orderId = button.data('order-id');

            // Update the modal's content
            var modal = $(this);
            modal.find('#modal-prod-name').text(prodName);
            modal.find('#modal-order-total').text(parseFloat(orderTotal).toFixed(2)); // Display total with two decimal places
        });



        // Handle click event for SCAN button
        $('#scanButton').on('click', function() {
            var orderIds = []; // Array to store order IDs

            // Collect all order IDs from the product table
            $('tbody tr').each(function() {
                var orderId = $(this).data('order-id'); // Assuming each row has a data attribute for order_id
                orderIds.push(orderId); // Store the order ID
            });

            // Set the order IDs in the hidden input field
            $('#order_ids').val(orderIds.join(',')); // Join IDs with a comma
            $('#cashInputModal').modal('show'); // Show cash input modal
        });

        $(document).ready(function() {
            $('#cashInputForm').on('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                // Get the cash amount value
                var cashAmount = $('#cashAmount').val().trim(); // Trim whitespace

                // Check if cash amount is empty
                if (cashAmount === '') {
                    Swal.fire("Error!", "Cash amount cannot be empty.", "error"); // Use Swal instead of swal
                    return; // Exit the function
                }

                // Parse cash amount to a float
                cashAmount = parseFloat(cashAmount);

                // Get total amount from the card body or other element
                var totalAmount = <?php echo json_encode($grand_total + $brgy_fee); ?>; // Use PHP to get the total amount

                // Validate the cash amount
                if (isNaN(cashAmount) || cashAmount <= 0) {
                    Swal.fire("Invalid!", "Please enter a valid cash amount.", "warning");
                    return; // Exit the function
                }
                if (cashAmount < totalAmount) {
                    Swal.fire("Insufficient Amount!", "Cash amount must be greater than or equal to the total amount.", "warning");
                    return; // Exit the function
                }

                // Serialize form data
                var formData = $(this).serialize();

                $.ajax({
                    type: 'POST',
                    url: 'update_order_status.php', // PHP script to process the form
                    data: formData,
                    success: function(response) {
                        var res = JSON.parse(response);
                        if (res.status === 'success') {
                            let timerInterval;
                            Swal.fire({
                                title: "Success!",
                                html: "Delivery confirmed successfully! I will close in <b></b> milliseconds.",
                                timer: 3000, // Auto close after 3 seconds
                                timerProgressBar: true,
                                didOpen: () => {
                                    Swal.showLoading();
                                    const timer = Swal.getPopup().querySelector("b");
                                    timerInterval = setInterval(() => {
                                        timer.textContent = `${Swal.getTimerLeft()}`;
                                    }, 100);
                                },
                                willClose: () => {
                                    clearInterval(timerInterval);
                                }
                            }).then((result) => {
                                if (result.dismiss === Swal.DismissReason.timer) {
                                    console.log("I was closed by the timer");
                                }
                                window.location.href = 'shipper.php'; // Redirect to shipper.php after closing
                            });
                        } else {
                            Swal.fire("Error!", res.message || "An error occurred while confirming the delivery.", "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire("Error!", "An unexpected error occurred: " + error, "error");
                    }
                });

            });
        });


        // Handle click event for Pick-up Unsuccessful button
        $('#failedOrder').on('click', function() {
            var orderIds = []; // Array to store order IDs

            // Collect all order IDs from the product table
            $('tbody tr').each(function() {
                var orderId = $(this).data('order-id'); // Assuming each row has a data attribute for order_id
                orderIds.push(orderId); // Store the order ID
            });

            // Confirm action with the user
            Swal.fire({
                title: 'Are you sure?',
                text: "This will mark order unsuccessful pick-up.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send AJAX request to update status
                    $.ajax({
                        type: 'POST',
                        url: 'unsuccessfulOrder.php', // PHP script to handle the update
                        data: {
                            order_ids: orderIds.join(','), // Join order IDs with a comma
                            status: 'Failed' // Set the status to Failed
                        },
                        success: function(response) {
                            var res = JSON.parse(response);
                            if (res.status === 'success') {
                                // Successful update alert with auto-close
                                let timerInterval;
                                Swal.fire({
                                    title: 'Updated!',
                                    html: 'Orders marked as unsuccessful pick-up.<br>I will close in <b></b> milliseconds.',
                                    timer: 2000,
                                    timerProgressBar: true,
                                    didOpen: () => {
                                        Swal.showLoading();
                                        const timer = Swal.getPopup().querySelector("b");
                                        timerInterval = setInterval(() => {
                                            timer.textContent = Swal.getTimerLeft();
                                        }, 100);
                                    },
                                    willClose: () => {
                                        clearInterval(timerInterval);
                                    }
                                }).then((result) => {
                                    // Redirect to shipper page after alert is closed
                                    window.location.href = 'shipper.php';
                                });
                            } else {
                                Swal.fire("Error!", res.message || "An error occurred while updating the orders.", "error");
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire("Error!", "An unexpected error occurred: " + error, "error");
                        }
                    });
                }
            });
        });
    </script>

</body>

</html>
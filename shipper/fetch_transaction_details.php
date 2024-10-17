<?php
include '../includes/db_connect.php'; // Include your database connection

// Get parameters passed via AJAX
$cust_id = $_GET['cust_id'];
$order_barangay = $_GET['order_barangay'];
$transact_date = $_GET['transact_date'];

// Ensure that the transaction date is in the correct format
$transact_date = date('Y-m-d H:i:s', strtotime($transact_date)); // Include time

// Query to get the order details for the specified customer, barangay, and transaction date
$query = "
    SELECT 
        o.order_id,
        CONCAT(c.f_name, ' ', c.l_name) AS cust_name,
        o.order_date,
        p.prod_name,
        o.order_total
    FROM 
        order_tbl o
    JOIN 
        customers c ON o.cust_id = c.cust_id
    JOIN 
        delivery_transactbl d ON d.order_id = o.order_id
    JOIN 
        brgy_tbl b ON o.order_barangay = b.Brgy_name
    JOIN 
        product_tbl p ON o.prod_code = p.prod_code
    WHERE 
        o.cust_id = '$cust_id'
        AND o.order_barangay = '$order_barangay'
        AND DATE_FORMAT(d.transact_date, '%Y-%m-%d %H:%i:%s') = '$transact_date'
";

// Execute the query
$result = mysqli_query($conn, $query);

// Get the delivery fee for the specified barangay
$brgyQuery = "
    SELECT Brgy_df FROM brgy_tbl WHERE Brgy_name = '$order_barangay'
";
$brgyResult = mysqli_query($conn, $brgyQuery);
$brgy_row = mysqli_fetch_assoc($brgyResult);
$delivery_fee = $brgy_row['Brgy_df'];

if (mysqli_num_rows($result) > 0) {
    // Fetch the first row to display customer name and order date
    $firstRow = mysqli_fetch_assoc($result);
    $cust_name = $firstRow['cust_name'];
    $order_date = date('F j, Y, g:i A', strtotime($firstRow['order_date'])); // Displaying order date with time

    // Initialize total amount variable
    $totalAmount = 0; 

    // Create the card for displaying customer details
    echo '<div class="mb-4">
            <div class="card">
                <div class="card-body p-3"> <!-- Reduced padding for card body -->
                    <h5 class="card-title mb-2">Customer Details</h5> <!-- Reduced margin for card title -->
                    <p class="card-text mb-1"><strong>Customer Name:</strong> ' . $cust_name . '</p> <!-- Reduced margin for card text -->
                    <p class="card-text mb-1"><strong>Order Date:</strong> ' . $order_date . '</p> <!-- Reduced margin for card text -->
                    <p class="card-text mb-1"><strong>Delivery Fee:</strong> ₱' . number_format($delivery_fee, 2) . '</p> <!-- Reduced margin for card text -->
                </div>
            </div>
          </div>'; // Close the card div

    // Create the table for displaying order details
    echo '<div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Product Name</th>
                        <th>Order Total</th>
                    </tr>
                </thead>
                <tbody>';
    
    // Start outputting the first row again to avoid skipping it
    echo '<tr>
            <td>' . $firstRow['order_id'] . '</td>
            <td>' . $firstRow['prod_name'] . '</td>
            <td>₱' . number_format($firstRow['order_total'], 2) . '</td>
        </tr>';
    
    $totalAmount += $firstRow['order_total']; // Include the first row's total
    
    // Continue fetching and displaying remaining rows
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>
                <td>' . $row['order_id'] . '</td>
                <td>' . $row['prod_name'] . '</td>
                <td>₱' . number_format($row['order_total'], 2) . '</td>
            </tr>';
        $totalAmount += $row['order_total']; // Sum total amount for all orders
    }

    // Add the delivery fee to the total amount
    $totalAmount += $delivery_fee;

    echo '</tbody>
          <tfoot>
              <tr>
                  <td colspan="2" class="text-right"><strong>Total Amount (with Delivery Fee):</strong></td>
                  <td>₱' . number_format($totalAmount, 2) . '</td>
              </tr>
          </tfoot>
          </table>
          </div>'; // Close the responsive table div
} else {
    echo '<p>No details found for this transaction.</p>';
}
?>

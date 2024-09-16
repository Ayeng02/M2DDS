<?php
include '../includes/db_connect.php';

require '../vendor/autoload.php'; // Include the Composer autoload file

use Picqer\Barcode\BarcodeGeneratorPNG;

if (isset($_GET['orders'])) {
    $orderIds = explode(',', $_GET['orders']);

    // Fetch the orders based on the IDs
    $orderQuery = "SELECT *, Brgy_df, prod_name, prod_price, prod_discount
                   FROM order_tbl o
                   JOIN product_tbl p ON o.prod_code = p.prod_code
                   JOIN brgy_tbl b ON o.order_barangay = b.Brgy_name
                   JOIN customers c ON o.cust_id = c.cust_id
                   WHERE order_id IN ('" . implode("','", $orderIds) . "')";
    $result = $conn->query($orderQuery);

    if ($result->num_rows > 0) {
        $receipts = [];
        $generator = new BarcodeGeneratorPNG();
        $barcodeDataUris = [];

        while ($row = $result->fetch_assoc()) {
            $cust_id = $row['cust_id'];

            if (!isset($receipts[$cust_id])) {
                $receipts[$cust_id] = [];
                // Generate barcode for each unique customer ID and store it in an array
                $barcode = $generator->getBarcode($cust_id, $generator::TYPE_CODE_128);
                $barcodeDataUris[$cust_id] = 'data:image/png;base64,' . base64_encode($barcode);
            }

            $receipts[$cust_id][] = $row;
        }

        // HTML for print layout
        echo '<html><head><title>Order Receipts</title>';
        echo '<style>
                body { font-family: Arial, sans-serif; margin: 80px; }
                .receipt { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; page-break-after: always; }
                .header-logo { width: 80px; height: auto; }
                .header-text { text-align: center; }
                .business-name { font-size: 24px; font-weight: bold; color: #333; }
                .business-details { font-size: 14px; color: #555; }
                .table { margin-bottom: 0; }
                .total { font-weight: bold; font-size: 18px; }
                .text-center { text-align: center; }
                .barcode { margin: 10px 0; }
            </style>';
        echo '</head><body onload="window.print()">';

        // Business details
        $businessName = "Meat-To-Door";
        $businessAddress = "Apokon RD, Tagum, Davao del Norte";
        $businessContact = "+63 xxxxxx";
        $businessEmail = "xxxxxxxxxxxxxxxxxx";
        $logoPath = "../img/mtdd_logo.png"; // Adjust the logo path as necessary

        foreach ($receipts as $cust_id => $orders) {
            $customerName = $orders[0]['order_fullname'];
            $address = $orders[0]['order_purok'] . ', ' . $orders[0]['order_barangay'] . ', ' . $orders[0]['order_province'];

            echo "<div class='receipt'>";

            // Header with logo and business details
            echo "<div class='row'>
                    <div class='col-2'>
                        <img src='$logoPath' class='header-logo' alt='Meat-To-Door Logo'>
                    </div>
                    <div class='col-10 header-text'>
                        <div class='business-name'>$businessName</div>
                        <div class='business-details'>$businessAddress | Contact: $businessContact | Email: $businessEmail</div>
                    </div>
                  </div>";
            echo "<hr>";

            // Official Receipt Title
            echo "<h3 class='text-center'>Order Receipt</h3>";
            echo "<hr>";

            // Customer and order info
            echo "<p><strong>Customer Name:</strong> $customerName</p>";
            echo "<p><strong>Address:</strong> $address</p>";
            echo "<p><strong>Phone Number:</strong> {$orders[0]['order_phonenum']}</p>";
            echo "<p><strong>Order Date:</strong> " . date('F j, Y g:i A', strtotime($orders[0]['order_date'])) . "</p>";
            echo "<hr>";

            // Display the barcode using the generated base64 URI
            echo "<div class='barcode'>
                    <img src='" . $barcodeDataUris[$cust_id] . "' alt='Customer Barcode'>
                    <p style='letter-spacing: 19px; margin-top: -0.4px; color: #0000007e;'>$cust_id</p>
                  </div>";

            // Table for order details
            echo "<table class='table table-bordered'>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Product Name</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>";
            echo "<tbody>";

            $totalAmount = 0;
            $deliveryFee = $orders[0]['Brgy_df']; // Use the delivery fee from the first order

            foreach ($orders as $order) {
                // Check if there's a discount
                $unit_price = ($order['prod_discount'] > 0) ? $order['prod_discount'] : $order['prod_price'];

                echo "<tr>
                        <td>{$order['order_id']}</td>
                        <td>{$order['prod_name']}</td>
                        <td>" . number_format($unit_price, 2) . "</td>
                        <td>{$order['order_qty']}</td>
                        <td>" . number_format($order['order_total'], 2) . "</td>
                      </tr>";
                $totalAmount += $order['order_total'];
            }

            echo "</tbody>";
            echo "<tfoot>
                    <tr>
                        <td colspan='4' class='text-end total'>Delivery Fee</td>
                        <td class='text-center'>" . number_format($deliveryFee, 2) . "</td>
                    </tr>
                    <tr>
                        <td colspan='4' class='text-end total'>Total Amount</td>
                        <td class='total'>" . number_format($totalAmount + $deliveryFee, 2) . "</td>
                    </tr>
                  </tfoot>";
            echo "</table>";

            echo "</div>";  // Close receipt div
        }

        echo '</body></html>';
    } else {
        echo "No orders found.";
    }
}
?>

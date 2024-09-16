<?php
include '../includes/db_connect.php';

if (isset($_POST['shipper_id'])) {
    $shipperId = $_POST['shipper_id'];

    // Debugging: Check if the correct shipper_id is passed
    error_log("Shipper ID: " . $shipperId);

    // Extracting both date and time from the `order_date` timestamp field
    $query = "SELECT o.order_id, o.cust_id, DATE(o.order_date) AS order_date, TIME(o.order_date) AS order_time, 
                     p.prod_name, d.transact_status, d.shipper_id
              FROM delivery_transactbl d
              INNER JOIN order_tbl o ON d.order_id = o.order_id
              INNER JOIN product_tbl p ON o.prod_code = p.prod_code
              WHERE d.shipper_id = ? AND DATE(d.transact_date) = CURDATE() 
              AND (d.transact_status = 'Ongoing')
              ORDER BY o.order_date, o.cust_id";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $shipperId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $orders = [];
            
            while ($row = $result->fetch_assoc()) {
                $date = date('F j, Y', strtotime($row['order_date']));
                $time = date('h:i A', strtotime($row['order_time']));  // Extracted time
                $custId = $row['cust_id'];
                $shipper = $row['shipper_id'];

                // Separate orders by date, time, cust_id, and shipper_id
                if (!isset($orders[$shipper][$date][$time][$custId])) {
                    $orders[$shipper][$date][$time][$custId] = [];
                }
                $orders[$shipper][$date][$time][$custId][] = $row;
            }

            // Enhanced table styling using Bootstrap classes and inline styles
            $html = '<div class="table-responsive">
                        <table class="table table-hover table-bordered text-center align-middle">
                            <thead>
                                <tr>
                                    <th>Shipper ID</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Customer ID</th>
                                    <th>Order ID</th>
                                    <th>Product Name</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>';
            
            foreach ($orders as $shipper => $dateGroups) {
                foreach ($dateGroups as $date => $timeGroups) {
                    foreach ($timeGroups as $time => $custOrders) {
                        foreach ($custOrders as $custId => $orderList) {
                            $rowspan = count($orderList);

                            // Light background for alternating rows
                            $html .= '<tr style="background-color: #f7f7f7;">
                                        <td rowspan="' . $rowspan . '" style="vertical-align: middle; background-color: #4caf50; color: white;">
                                            <span class="badge" style="background-color:#FF8225;">' . $shipper . '</span>
                                        </td>
                                        <td rowspan="' . $rowspan . '" style="vertical-align: middle; background-color: #ff6b6b; color: white;">' . $date . '</td>
                                        <td rowspan="' . $rowspan . '" style="vertical-align: middle; background-color: #f5a623; color: white;">' . $time . '</td>
                                        <td rowspan="' . $rowspan . '" style="vertical-align: middle; background-color: #a72828; color: white;">
                                            <span class="badge" style="background-color:#FF8225;">' . $custId . '</span>
                                        </td>';
                            
                            foreach ($orderList as $index => $order) {
                                if ($index > 0) {
                                    $html .= '<tr>';
                                }

                                // Adding tooltips to the status and product name for better interaction
                                $html .= '<td style="background-color: #f7f7f7; vertical-align: middle;">' . $order['order_id'] . '</td>
                                          <td style="background-color: #f7f7f7; vertical-align: middle;">
                                              <span data-bs-toggle="tooltip" data-bs-placement="top" title="Product Name">' . $order['prod_name'] . '</span>
                                          </td>
                                          <td style="background-color: #f7f7f7; vertical-align: middle;">
                                              <span class="badge bg-warning text-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Order Status">' . $order['transact_status'] . '</span>
                                          </td>';

                                if ($index < $rowspan - 1) {
                                    $html .= '</tr>';
                                } else {
                                    $html .= '</tr>';
                                }
                            }
                        }
                    }
                }
            }

            $html .= '</tbody>
                    </table>
                </div>';

            // Include script for initializing Bootstrap tooltips
            $html .= '<script>
                        var tooltipTriggerList = [].slice.call(document.querySelectorAll(\'[data-bs-toggle="tooltip"]\'))
                        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                          return new bootstrap.Tooltip(tooltipTriggerEl)
                        })
                      </script>';
            
            echo $html;
        } else {
            // Display an enhanced no orders message
            echo '<div class="alert alert-warning text-center" role="alert">
                    <strong>No ongoing orders found for this shipper today.</strong>
                  </div>';
        }

    } else {
        echo '<div class="alert alert-danger" role="alert">Failed to prepare statement.</div>';
    }
} else {
    echo '<div class="alert alert-danger" role="alert">No shipper_id provided.</div>';
}
?>

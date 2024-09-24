<?php
include '../includes/db_connect.php'; // Adjust the path to your db connection

// Check if the search term is set
if (isset($_GET['term'])) {
    $searchTerm = htmlspecialchars($_GET['term']); // Sanitize input to prevent SQL injection

    // Fetch customers whose first or last name matches the search term and have an order
    $sql = "SELECT DISTINCT c.cust_id, CONCAT(c.f_name, ' ', c.l_name) AS customer 
            FROM customers c
            JOIN order_tbl o ON c.cust_id = o.cust_id
            LEFT JOIN chat_messages cm ON c.cust_id = cm.cust_id
            WHERE (c.f_name LIKE '%$searchTerm%' OR c.l_name LIKE '%$searchTerm%')"; // Search by first or last name

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cust_id = htmlspecialchars($row['cust_id']); // Sanitize the cust_id
            $customerName = htmlspecialchars($row['customer']); // Sanitize the customer name
            echo "<div class='customer-item' data-cust-id='$cust_id' onclick=\"loadCustomerChat('$customerName', '$cust_id', this)\">
                    <i class='fa fa-user-circle'></i>
                    <span>$customerName</span> <!-- Display the customer name -->
                  </div>";
        }
    } else {
        echo "<div>No customers found.</div>";
    }

    $conn->close(); // Close the database connection
}
?>

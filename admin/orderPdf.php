<?php
require('../fpdf186/fpdf.php');
include '../includes/db_connect.php';

// Query to fetch order-related details
$query = "SELECT 
            o.order_id, 
            o.order_fullname AS customer_name, 
            o.order_phonenum, 
            CONCAT(o.order_purok, ' ', o.order_barangay, ', ', o.order_province) AS order_address,
            o.order_mop AS payment_method, 
            o.order_qty AS total_items, 
            o.order_total AS total_amount, 
            o.order_cash AS amount_paid, 
            o.order_change AS change_given, 
            s.status_name AS order_status
          FROM order_tbl o
          JOIN status_tbl s ON o.status_code = s.status_code
          ORDER BY o.order_id DESC";

$result = $conn->query($query);

// Create PDF
$pdf = new FPDF('L'); // 'L' for landscape orientation
$pdf->AddPage();

// Add Logo - Center the logo
$logo_width = 30;
$pdf->Image('../img/mtdd_logo.png', ($pdf->GetPageWidth() - $logo_width) / 2, 10, $logo_width);
$pdf->Ln(30); // Move below the logo

// Title
$pdf->SetFont('Arial', 'B', 20);
$pdf->Cell(0, 10, "Melo's Meatshop", 0, 1, 'C');
$pdf->SetFont('Arial', 'I', 16);
$pdf->Cell(0, 10, "Where Quality Meets Affordability", 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 5, "Contact: +63 938 895 2457", 0, 1, 'C');
$pdf->Cell(0, 5, "Email: meattodoor@gmail.com", 0, 1, 'C');
$pdf->Ln(10);

// Add Order Table Header
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(281, 10, "Order Transactions Details", 1, 1, 'C', true);

// Add table headers
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(27, 10, 'Order ID', 1, 0, 'C', true);
$pdf->Cell(27, 10, 'Fullname', 1, 0, 'C', true);
$pdf->Cell(96, 10, 'Address', 1, 0, 'C', true);
$pdf->Cell(23, 10, 'Phone', 1, 0, 'C', true);
$pdf->Cell(32, 10, 'Payment Method', 1, 0, 'C', true);
$pdf->Cell(13, 10, 'Items', 1, 0, 'C', true);
$pdf->Cell(15, 10, 'Total', 1, 0, 'C', true);
$pdf->Cell(15, 10, 'Paid', 1, 0, 'C', true);
$pdf->Cell(18, 10, 'Change', 1, 0, 'C', true);
$pdf->Cell(15, 10, 'Status', 1, 1, 'C', true);

// Fetch and display order data
$pdf->SetFont('Arial', '', 8);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(27, 10, $row['order_id'], 1);
        $pdf->Cell(27, 10, $row['customer_name'], 1);
        $pdf->Cell(96, 10, $row['order_address'], 1);
        $pdf->Cell(23, 10, $row['order_phonenum'], 1);
        $pdf->Cell(32, 10, $row['payment_method'], 1);
        $pdf->Cell(13, 10, $row['total_items'], 1);
        $pdf->Cell(15, 10, number_format($row['total_amount'], 2), 1);
        $pdf->Cell(15, 10, number_format($row['amount_paid'], 2), 1);
        $pdf->Cell(18, 10, number_format($row['change_given'], 2), 1);
        $pdf->Cell(15, 10, $row['order_status'], 1, 1);
    }
} else {
    $pdf->Cell(280, 10, 'No order data available', 1, 1, 'C');
}

// Output PDF to Browser for Download
$pdf->Output('D', 'Order_Details.pdf'); // 'D' triggers download, filename is 'Order_Details.pdf'
?>

<?php
require('../fpdf186/fpdf.php');
include '../includes/db_connect.php';

// Query to fetch all cashier-related details
$query = "SELECT p.pos_code, pp.prod_name, p.pos_qty, 
            p.pos_discount, p.total_amount, p.amount_received, p.pos_change, 
            CONCAT(e.emp_fname, ' ', e.emp_lname) as fullname, p.transac_date
          FROM pos_tbl p
          JOIN emp_tbl e ON p.pos_personnel = e.emp_id
          JOIN product_tbl pp ON p.prod_code = pp.prod_code
          WHERE e.emp_role = 'Cashier'
          ORDER BY p.pos_code DESC
          "; // Filter only for cashiers
$result = $conn->query($query);

// Create PDF
$pdf = new FPDF('L'); // 'L' for landscape orientation
$pdf->AddPage();

// Add Logo - Centering the logo
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

// Add Cashier Table Header
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(280, 10, "Cashier Transactions Details", 1, 1, 'C', true);

// Add table headers
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(35, 10, 'POS Code', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Product Name', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Quantity', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Discount', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Total Amount', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Amount Received', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Change', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Cashier Name', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Transaction Date', 1, 1, 'C', true);

// Fetch and Display Cashier Data
$pdf->SetFont('Arial', '', 10);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(35, 10, $row['pos_code'], 1);
        $pdf->Cell(35, 10, $row['prod_name'], 1);
        $pdf->Cell(20, 10, $row['pos_qty'], 1);
        $pdf->Cell(25, 10, number_format($row['pos_discount'], 2), 1);
        $pdf->Cell(30, 10, number_format($row['total_amount'], 2), 1);
        $pdf->Cell(40, 10, number_format($row['amount_received'], 2), 1);
        $pdf->Cell(20, 10, number_format($row['pos_change'], 2), 1);
        $pdf->Cell(35, 10, $row['fullname'], 1);
        $pdf->Cell(40, 10, $row['transac_date'], 1, 1);
    }
} else {
    $pdf->Cell(280, 10, 'No cashier data available', 1, 1, 'C');
}

// Output PDF to Browser for Download
$pdf->Output('D', 'Cashier_Details.pdf'); // 'D' triggers download, filename is 'Cashier_Details.pdf'
?>

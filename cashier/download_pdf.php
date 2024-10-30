<?php
require('../fpdf186/fpdf.php');

session_start();
include '../includes/db_connect.php';

$emp_id = $_SESSION['emp_id'];

// Call the stored function to get the cashier's info (name and image)
$query_emp = "SELECT sf_getEmpInfo('$emp_id') AS emp_info";
$result_emp = $conn->query($query_emp);
$emp = $result_emp->fetch_assoc();

// Split the returned result into name and image
list($cashier_name, $cashier_img) = explode('|', $emp['emp_info']);

// Create PDF with custom page size (wide format)
$custom_width = 400; // Custom width in mm
$custom_height = 200; // Custom height in mm (this is still landscape but wider)
$pdf = new FPDF('L', 'mm', array($custom_width, $custom_height)); // 'L' for landscape, custom size
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Add Logo - Centering the logo
$logo_width = 30; // Width of the logo
$pdf->Image('../img/mtdd_logo.png', ($pdf->GetPageWidth() - $logo_width) / 2, 10, $logo_width); // Center the logo
$pdf->Ln(30); // Move below the logo

// Title
$pdf->SetFont('Arial', 'B', 20);
$pdf->Cell(0, 10, "Melo's Meatshop", 0, 1, 'C');
$pdf->SetFont('Arial', 'I', 16);
$pdf->Cell(0, 10, "Where Quality Meets Affordability", 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 5, "Contact: +63 938 895 2457", 0, 1, 'C');
$pdf->Cell(0, 5, "Email: meattodoor@gmail.com", 0, 1, 'C');
$pdf->Ln(5);

// Add cashier name
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, "Cashier: " . $cashier_name, 0, 1, 'C');

$pdf->Ln(20); // Add some space after cashier details

// Define the headers
$headers = [
    'Transaction Code',
    'Product Code',
    'Product Name',
    'Quantity',
    'Discount',
    'Total Amount',
    'Amount Received',
    'Change',
    'Price',
    'Date'
];

// Initialize an array to hold the maximum widths
$max_widths = array_fill(0, count($headers), 0);

// First pass: measure maximum widths
$query = "SELECT p.*, pr.prod_name, pr.prod_price 
          FROM pos_tbl p 
          JOIN product_tbl pr ON p.prod_code = pr.prod_code 
          WHERE p.pos_personnel = '$emp_id' 
          ORDER BY p.transac_date DESC";

$result = $conn->query($query);

// Measure header widths
foreach ($headers as $key => $header) {
    $max_widths[$key] = $pdf->GetStringWidth($header) + 4; // Add some padding
}

// Measure data widths and update max_widths
while ($row = $result->fetch_assoc()) {
    $data = [
        $row['pos_code'],
        $row['prod_code'],
        $row['prod_name'],
        $row['pos_qty'],
        $row['pos_discount'],
        $row['total_amount'],
        $row['amount_received'],
        $row['pos_change'],
        $row['prod_price'],
        date('F j, Y h:i A', strtotime($row['transac_date']))
    ];
    
    foreach ($data as $key => $value) {
        $current_width = $pdf->GetStringWidth($value) + 4; // Add some padding
        if ($current_width > $max_widths[$key]) {
            $max_widths[$key] = $current_width;
        }
    }
}

// Set the font for the headers (size 8)
$pdf->SetFont('Arial', 'B', 8);

// Output the headers
foreach ($headers as $key => $header) {
    $pdf->Cell($max_widths[$key], 10, $header, 1);
}
$pdf->Ln();

// Reset the pointer for the data retrieval
$result->data_seek(0); // Reset result pointer

// Set the font for the data (size 8)
$pdf->SetFont('Arial', '', 8);

// Output the data
while ($row = $result->fetch_assoc()) {
    $pdf->Cell($max_widths[0], 10, $row['pos_code'], 1);
    $pdf->Cell($max_widths[1], 10, $row['prod_code'], 1);
    $pdf->Cell($max_widths[2], 10, $row['prod_name'], 1);
    $pdf->Cell($max_widths[3], 10, $row['pos_qty'], 1);
    $pdf->Cell($max_widths[4], 10, $row['pos_discount'], 1);
    $pdf->Cell($max_widths[5], 10, $row['total_amount'], 1);
    $pdf->Cell($max_widths[6], 10, $row['amount_received'], 1);
    $pdf->Cell($max_widths[7], 10, $row['pos_change'], 1);
    $pdf->Cell($max_widths[8], 10, $row['prod_price'], 1);
    $pdf->Cell($max_widths[9], 10, date('F j, Y h:i A', strtotime($row['transac_date'])), 1);
    $pdf->Ln();
}

$pdf->Output('D', 'logs_report.pdf'); // Output to download
?>

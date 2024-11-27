<?php
require('../fpdf186/fpdf.php');
include '../includes/db_connect.php';

// Query to fetch all product details
$query = "SELECT p.prod_code, c.category_name, p.prod_name, p.prod_price, p.prod_qoh, p.prod_discount 
FROM product_tbl p
JOIN category_tbl c ON p.category_code = c.category_code
ORDER BY p.prod_code DESC
";
$result = $conn->query($query);

// Create PDF
$pdf = new FPDF();
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

// Add Product Table Header
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(190, 10, "Product Details", 1, 1, 'C', true);

// Add table headers
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(38, 10, 'Product Code', 1, 0, 'C', true);
$pdf->Cell(36, 10, 'Category Name', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Product Name', 1, 0, 'C', true);
$pdf->Cell(21, 10, 'Price', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Quantity', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Discount', 1, 1, 'C', true);

// Fetch and Display Product Data
$pdf->SetFont('Arial', '', 10);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(38, 10, $row['prod_code'], 1);
        $pdf->Cell(36, 10, $row['category_name'], 1);
        $pdf->Cell(40, 10, $row['prod_name'], 1);
        $pdf->Cell(21, 10, number_format($row['prod_price'], 2), 1);
        $pdf->Cell(25, 10, $row['prod_qoh'], 1);
        $pdf->Cell(30, 10, $row['prod_discount'], 1, 1);
    }
} else {
    $pdf->Cell(190, 10, 'No product data available', 1, 1, 'C');
}

// Output PDF to Browser for Download
$pdf->Output('D', 'Product_Details.pdf'); // 'D' triggers download, filename is 'Product_Details.pdf'
?>

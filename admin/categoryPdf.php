<?php
require('../fpdf186/fpdf.php');
include '../includes/db_connect.php';

// Query to fetch all category details
$query = "SELECT category_code, category_name, category_desc, category_img
          FROM category_tbl 
          ORDER BY category_code DESC";
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

// Add Category Table Header
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(195, 10, "Category Details", 1, 1, 'C', true);

// Add table headers
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(40, 10, 'Category Code', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Category Name', 1, 0, 'C', true);
$pdf->Cell(60, 10, 'Description', 1, 0, 'C', true);
$pdf->Cell(55, 10, 'Category Image', 1, 1, 'C', true);

// Fetch and Display Category Data
$pdf->SetFont('Arial', '', 10);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(40, 20, $row['category_code'], 1, 0, 'C');
        $pdf->Cell(40, 20, $row['category_name'], 1, 0, 'C');
        $pdf->Cell(60, 20, $row['category_desc'], 1, 0, 'C');

        // Add the category image
        if (!empty($row['category_img']) && file_exists("../" . $row['category_img'])) {
            $pdf->Image("../" . $row['category_img'], $pdf->GetX(), $pdf->GetY(), 55, 20); // Adjust width and height as needed
        } else {
            $pdf->Cell(55, 20, 'No Image', 1, 1, 'C'); // Placeholder if the image is missing
            continue; // Skip to the next row
        }

        $pdf->Ln(20); // Adjust row height to match the image height
    }
} else {
    $pdf->Cell(190, 10, 'No category data available', 1, 1, 'C');
}

// Output PDF to Browser for Download
$pdf->Output('D', 'Category_Details.pdf'); // 'D' triggers download, filename is 'Category_Details.pdf'
?>

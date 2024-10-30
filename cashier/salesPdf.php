<?php
session_start();
require('../fpdf186/fpdf.php');

// Check if the user is logged in
if (!isset($_SESSION['emp_id'])) {
    header('Location: login.php');
    exit();
}

$emp_id = $_SESSION['emp_id'];


include '../includes/db_connect.php';


$sql = "SELECT emp_fname, emp_lname, emp_role, emp_email, emp_num, emp_address, emp_img FROM emp_tbl WHERE emp_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();


$query = "SELECT p.pos_personnel, p.prod_code, pr.prod_name, pr.prod_price, SUM(p.total_amount) AS total_sales
          FROM pos_tbl p
          JOIN product_tbl pr ON p.prod_code = pr.prod_code
          WHERE p.pos_personnel = '$emp_id'
          GROUP BY p.pos_personnel, p.prod_code, pr.prod_name, pr.prod_price
          ORDER BY total_sales DESC";
$result2 = $conn->query($query);

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();

// Add Logo - Centering the logo
$logo_width = 30;
$pdf->Image('../img/mtdd_logo.png', ($pdf->GetPageWidth() - $logo_width) / 2, 10, $logo_width);
$pdf->Ln(30); // Move below the logo

// Add Watermark Behind Text
$pdf->SetFont('Arial', 'B', 50);
$pdf->SetTextColor(200, 200, 200);
$pdf->Cell(0, $pdf->GetPageHeight() / 2, "Cashier Copy", 0, 0, 'C');

// Reset font color for the main content
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(5); // Add space for the watermark

// Title
$pdf->SetFont('Arial', 'B', 20);
$pdf->Cell(0, 10, "Melo's Meatshop", 0, 1, 'C');
$pdf->SetFont('Arial', 'I', 16);
$pdf->Cell(0, 10, "Where Quality Meets Affordability", 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 5, "Contact: +63 938 895 2457", 0, 1, 'C');
$pdf->Cell(0, 5, "Email: meattodoor@gmail.com", 0, 1, 'C');
$pdf->Ln(5);

// Employee Details
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 11, "Employee Details", 0, 1);
$pdf->SetFont('Arial', '', 12);

// Display Employee Name, Role, and Contact Information
$pdf->Cell(0, 5, "Name: " . htmlspecialchars($employee['emp_fname'] . ' ' . $employee['emp_lname']), 0, 0);

// Add Employee Image on the right side
if (!empty($employee['emp_img'])) {
    $img_path = '../' . $employee['emp_img'];
    $pdf->Image($img_path, 160, $pdf->GetY() - 10, 30, 30);
}

$pdf->Ln(5);
$pdf->Cell(0, 5, "Role: " . htmlspecialchars($employee['emp_role']), 0, 1);
$pdf->Cell(0, 5, "Employee Email: " . htmlspecialchars($employee['emp_email']), 0, 1);
$pdf->Cell(0, 5, "Employee Number: " . htmlspecialchars($employee['emp_num']), 0, 1);
$pdf->Ln(30);


$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(200, 220, 255);
$totalWidth = 180; 

$pdf->Cell($totalWidth, 11, "Product Sale Records", 1, 1, 'C', true); 

// Add table header
$pdf->SetTextColor(0, 0, 0); 
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(40, 10, 'Employee ID', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Product Code', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Product Name', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Price', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Total Sales', 1, 1, 'C', true);
$pdf->SetFont('Arial', '', 12); // Reset to normal font for table content



// Add table rows
$pdf->SetFont('Arial', '', 10);
if ($result2->num_rows > 0) {
    while ($row = $result2->fetch_assoc()) {
        $pdf->Cell(40, 10, $row['pos_personnel'], 1);
        $pdf->Cell(40, 10, $row['prod_code'], 1);
        $pdf->Cell(40, 10, $row['prod_name'], 1);
        $pdf->Cell(30, 10, $row['prod_price'], 1);
        $pdf->Cell(30, 10, $row['total_sales'], 1);
        $pdf->Ln();
    }
} else {
    $pdf->Cell(180, 10, 'No data available', 1, 0, 'C');
}

// Output PDF to browser
$pdf->Output('D', 'Product Sales Record.pdf');  // The 'D' parameter forces download, with the filename 'datatable_export.pdf'
?>

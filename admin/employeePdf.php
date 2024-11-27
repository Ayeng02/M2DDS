<?php
require('../fpdf186/fpdf.php');
include '../includes/db_connect.php';

// Query to fetch all employee details
$query = "SELECT emp_id, emp_fname, emp_lname, emp_email, emp_num, emp_address, emp_role
          FROM emp_tbl
          ORDER BY emp_id DESC";
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

// Add Employee Table Header
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(280, 10, "Employee Details", 1, 1, 'C', true);

// Add table headers
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(35, 10, 'Employee ID', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Full Name', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Email', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Contact No.', 1, 0, 'C', true);
$pdf->Cell(95, 10, 'Address', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Role', 1, 1, 'C', true);

// Fetch and Display Employee Data
$pdf->SetFont('Arial', '', 10);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $full_name = $row['emp_fname'] . ' ' . $row['emp_lname']; // Concatenate first and last name
        $pdf->Cell(35, 10, $row['emp_id'], 1);
        $pdf->Cell(40, 10, $full_name, 1);
        $pdf->Cell(50, 10, $row['emp_email'], 1);
        $pdf->Cell(30, 10, $row['emp_num'], 1);
        $pdf->Cell(95, 10, $row['emp_address'], 1);
        $pdf->Cell(30, 10, $row['emp_role'], 1, 1);
    }
} else {
    $pdf->Cell(190, 10, 'No employee data available', 1, 1, 'C');
}

// Output PDF to Browser for Download
$pdf->Output('D', 'Employee_Details.pdf'); // 'D' triggers download, filename is 'Employee_Details.pdf'
?>

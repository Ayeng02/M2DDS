<?php
require('../fpdf186/fpdf.php');
error_reporting(E_ALL & ~E_NOTICE);

session_start(); 
include '../includes/db_connect.php';

// Retrieve employee ID from URL parameter
$emp_id = $_GET['emp_id'] ?? null;

if ($emp_id) {
    $sql = "SELECT 
                et.emp_id AS `Employee ID`, 
                et.emp_img AS `Image`,       
                CONCAT(et.emp_fname, ' ', et.emp_lname) AS `Fullname`, 
                et.emp_role AS `Role`,       
                CONCAT(
                    DATE_FORMAT(DATE_SUB(NOW(), INTERVAL DAYOFWEEK(NOW()) - 1 DAY), '%M %e'), 
                    ' - ', 
                    DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 6 - DAYOFWEEK(NOW()) DAY), '%M %e, %Y')
                ) AS `Period`,
                COUNT(DISTINCT DATE(a.time_in)) AS `Days Worked`, 
                dr.daily_rate AS `Rate`, 
                (COUNT(DISTINCT DATE(a.time_in)) * dr.daily_rate) AS `Salary` 
            FROM 
                emp_tbl et
            LEFT JOIN 
                daily_rates dr ON et.emp_role = dr.role_name 
            LEFT JOIN 
                att_tbl a ON et.emp_id = a.emp_id 
            WHERE et.emp_id = ?
            AND YEARWEEK(a.time_in, 0) = YEARWEEK(CURRENT_DATE, 0)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();


    // Prepare the attendance query
    $query = "SELECT 
                    DATE_FORMAT(a.att_date, '%Y-%m-%d') AS `Date`, 
                    DATE_FORMAT(a.time_in, '%h:%i %p') AS `Time In`, 
                    DATE_FORMAT(a.time_out, '%h:%i %p') AS `Time Out`
                FROM 
                    att_tbl a 
                WHERE 
                    a.emp_id = ? 
                    AND YEARWEEK(a.att_date, 0) = YEARWEEK(CURRENT_DATE, 0) 
                ORDER BY 
                    a.att_date DESC";

    // Prepare and execute the attendance statement
    $stmt2 = $conn->prepare($query);
    $stmt2->bind_param("s", $emp_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    
   




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
$pdf->Cell(0, $pdf->GetPageHeight() / 2, "Invoice Copy", 0, 0, 'C');

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
$pdf->Cell(0, 11, "Employee Payroll Details", 0, 1);
$pdf->SetFont('Arial', '', 12);

// Display Employee Name, Role, and Contact Information
$pdf->Cell(0, 5, "ID: " . htmlspecialchars($employee['Employee ID']), 0, 0);

// Add Employee Image on the right side
if (!empty($employee['Image'])) {
    $img_path = '../' . $employee['Image'];
    $pdf->Image($img_path, 160, $pdf->GetY() - 10, 30, 30);
}

$pdf->Ln(5);
$pdf->Cell(0, 5, "Name: " . htmlspecialchars($employee['Fullname']), 0, 1);
$pdf->Cell(0, 5, "Role: " . htmlspecialchars($employee['Role']), 0, 1);
$pdf->Cell(0, 5, "Days Worked: " . htmlspecialchars($employee['Days Worked']), 0, 1);
$pdf->Cell(0, 5, "Daily Rates: P " . htmlspecialchars($employee['Rate']), 0, 1);
$pdf->Cell(0, 5, "Payroll Period: " . htmlspecialchars($employee['Period']), 0, 1);
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 5, "Total Salary: P " . htmlspecialchars($employee['Salary']), 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->Ln(10);


$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(200, 220, 255);
$totalWidth = 180; 

$pdf->Cell($totalWidth, 11, "Current Payroll Week Logs", 1, 1, 'C', true); 

// Add table header
$pdf->SetTextColor(0, 0, 0); 
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(60, 10, 'Date', 1, 0, 'C', true);
$pdf->Cell(60, 10, 'Time In', 1, 0, 'C', true);
$pdf->Cell(60, 10, 'Time out', 1, 1, 'C', true);
$pdf->SetFont('Arial', '', 12); // Reset to normal font for table content



  // Add attendance data rows
  $pdf->SetFont('Arial', '', 12);
  if ($result2->num_rows > 0) {
      while ($row = $result2->fetch_assoc()) {
          $pdf->Cell(60, 10, $row['Date'], 1, 0, 'C');
          $pdf->Cell(60, 10, $row['Time In'], 1, 0, 'C');
          $timeOut = !empty($row['Time Out']) ? $row['Time Out'] : 'N/A';
          $pdf->Cell(60, 10, $timeOut, 1, 0, 'C');
          $pdf->Ln();
      }
  } else {
      $pdf->Cell(120, 10, 'No time log records found.', 1, 0, 'C');
  }

  // Output PDF to browser
  $pdf->Output('D', 'Invoice_Record.pdf');  
}
?>

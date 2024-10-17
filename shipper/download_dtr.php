<?php
session_start();
require('../fpdf186/fpdf.php');

if (!isset($_SESSION['emp_id'])) {
    header('Location: login.php');
    exit();
}

$emp_id = $_SESSION['emp_id'];

// Include database connection
include '../includes/db_connect.php';

if (isset($_POST['download_week']) || isset($_POST['download_month'])) {
    $is_week = isset($_POST['download_week']);
    $week_start_date = $_POST['week_start_date'];
    $week_end_date = $_POST['week_end_date'];
    $selected_month = $_POST['selected_month'];

    // Fetch employee details for header
    $sql = "SELECT emp_fname, emp_lname, emp_role, emp_email, emp_num, emp_address, emp_img FROM emp_tbl WHERE emp_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();

    // Fetch attendance records
    if ($is_week) {
        $attendance_sql = "SELECT time_in, time_out, att_date FROM att_tbl WHERE emp_id = ? AND att_date BETWEEN ? AND ?";
        $stmt = $conn->prepare($attendance_sql);
        $stmt->bind_param("sss", $emp_id, $week_start_date, $week_end_date);
    } else {
        // For whole month, modify query accordingly
        $first_day_of_month = date('Y-m-01', strtotime($selected_month));
        $last_day_of_month = date('Y-m-t', strtotime($selected_month));
        $attendance_sql = "SELECT time_in, time_out, att_date FROM att_tbl WHERE emp_id = ? AND att_date BETWEEN ? AND ?";
        $stmt = $conn->prepare($attendance_sql);
        $stmt->bind_param("sss", $emp_id, $first_day_of_month, $last_day_of_month);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $attendance = $result->fetch_all(MYSQLI_ASSOC);

    // Create PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Add Logo - Centering the logo
    $logo_width = 30; // Width of the logo
    $pdf->Image('../img/mtdd_logo.png', ($pdf->GetPageWidth() - $logo_width) / 2, 10, $logo_width); // Center the logo
    $pdf->Ln(30); // Move below the logo

    // Add Watermark Behind Text
    $pdf->SetFont('Arial', 'B', 50);
    $pdf->SetTextColor(200, 200, 200); // Light gray color for the watermark
    $pdf->Cell(0, $pdf->GetPageHeight() / 2, "Shipper Copy", 0, 0, 'C');

    // Reset font color for the main content
    $pdf->SetTextColor(0, 0, 0); // Black color for the text
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
    $pdf->Cell(0, 10, "Employee Details", 0, 1);
    $pdf->SetFont('Arial', '', 12);
    
    // Employee Name and Role
    $pdf->Cell(0, 5, "Name: " . htmlspecialchars($employee['emp_fname'] . ' ' . $employee['emp_lname']), 0, 0);
    
    // Add Employee Image on the right side
    if (!empty($employee['emp_img'])) {
        $img_path = '../' . $employee['emp_img']; // Path to the employee image
        $pdf->Image($img_path, 160, $pdf->GetY() - 10, 30, 30); // Adjust the position and size (20x20)
    }
    
    $pdf->Ln(5);
    $pdf->Cell(0, 5, "Role: " . htmlspecialchars($employee['emp_role']), 0, 1);
    $pdf->Cell(0, 5, "Employee Email: " . htmlspecialchars($employee['emp_email']), 0, 1);
    $pdf->Cell(0, 5, "Employee Number: " . htmlspecialchars($employee['emp_num']), 0, 1);
    $pdf->Ln(30);
    
    // Table Header
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(200, 220, 255); // Light blue fill
    $pdf->Cell(40, 10, "Date", 1, 0, 'C', true);
    $pdf->Cell(40, 10, "Time In", 1, 0, 'C', true);
    $pdf->Cell(40, 10, "Time Out", 1, 1, 'C', true);
    
    // Table Rows
    $pdf->SetFont('Arial', '', 12);
    foreach ($attendance as $record) {
        $pdf->Cell(40, 10, date('Y-m-d', strtotime($record['att_date'])), 1);
        $pdf->Cell(40, 10, is_null($record['time_in']) ? "None" : date('h:i A', strtotime($record['time_in'])), 1);
        $pdf->Cell(40, 10, is_null($record['time_out']) ? "None" : date('h:i A', strtotime($record['time_out'])), 1);
        $pdf->Ln();
    }

    // Footer
    $pdf->Ln(30);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, "Generated on " . date('Y-m-d H:i:s'), 0, 1, 'C');

    // Output the PDF
    $pdf->Output('D', 'DTR_' . ($is_week ? 'Week' : 'Month') . '_' . date('Y-m-d') . '.pdf');
    exit();
}
?>

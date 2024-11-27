<?php
require '../phpspreadsheet/vendor/autoload.php'; // Load PhpSpreadsheet
include '../includes/db_connect.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Query to fetch all employee details
$query = "SELECT emp_id, emp_fname, emp_lname, emp_email, emp_num, emp_address, emp_role
          FROM emp_tbl 
          ORDER BY emp_id DESC";
$result = $conn->query($query);

// Create a new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Add Logo - Centering the logo
$logo = '../img/mtdd_logo.png';
if (file_exists($logo)) {
    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $drawing->setPath($logo);
    $drawing->setCoordinates('A1');
    $drawing->setHeight(50);
    $drawing->setWorksheet($sheet);
}

// Add Title and Contact Information
$sheet->setCellValue('A3', "Melo's Meatshop");
$sheet->mergeCells('A3:F3');
$sheet->getStyle('A3')->getFont()->setBold(true)->setSize(20);
$sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A4', "Where Quality Meets Affordability");
$sheet->mergeCells('A4:F4');
$sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(16);
$sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A5', "Contact: +63 938 895 2457");
$sheet->mergeCells('A5:F5');
$sheet->getStyle('A5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A6', "Email: meattodoor@gmail.com");
$sheet->mergeCells('A6:F6');
$sheet->getStyle('A6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

// Add Employee Table Header
$headers = ['Employee ID', 'Full Name', 'Email', 'Contact No.', 'Address', 'Role'];
$columnIndex = 'A';

$sheet->setCellValue('A8', 'Employee Details');
$sheet->mergeCells('A8:F8');
$sheet->getStyle('A8')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

foreach ($headers as $header) {
    $sheet->setCellValue($columnIndex . '10', $header);
    $sheet->getStyle($columnIndex . '10')->getFont()->setBold(true);
    $sheet->getStyle($columnIndex . '10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getColumnDimension($columnIndex)->setAutoSize(true);
    $columnIndex++;
}

// Populate Table Rows
$rowIndex = 11; // Data starts at row 11
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $full_name = $row['emp_fname'] . ' ' . $row['emp_lname']; // Concatenate first and last name
        $sheet->setCellValue('A' . $rowIndex, $row['emp_id']);
        $sheet->setCellValue('B' . $rowIndex, $full_name);
        $sheet->setCellValue('C' . $rowIndex, $row['emp_email']);
        $sheet->setCellValue('D' . $rowIndex, $row['emp_num']);
        $sheet->setCellValue('E' . $rowIndex, $row['emp_address']);
        $sheet->setCellValue('F' . $rowIndex, $row['emp_role']);
        $rowIndex++;
    }
} else {
    $sheet->setCellValue('A11', 'No employee data available');
    $sheet->mergeCells('A11:F11');
    $sheet->getStyle('A11')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
}

// Apply Border Style to Table
$styleArray = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ],
];
$sheet->getStyle('A10:F' . ($rowIndex - 1))->applyFromArray($styleArray);

// Output Excel File for Download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Employee_Details.xlsx"');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>

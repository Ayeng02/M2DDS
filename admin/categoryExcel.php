<?php
require '../phpspreadsheet/vendor/autoload.php'; // Load PhpSpreadsheet
include '../includes/db_connect.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Query to fetch all category details
$query = "SELECT category_code, category_name, category_desc
          FROM category_tbl 
          ORDER BY category_code DESC";
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
$sheet->mergeCells('A3:C3');
$sheet->getStyle('A3')->getFont()->setBold(true)->setSize(20);
$sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A4', "Where Quality Meets Affordability");
$sheet->mergeCells('A4:C4');
$sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(16);
$sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A5', "Contact: +63 938 895 2457");
$sheet->mergeCells('A5:C5');
$sheet->getStyle('A5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A6', "Email: meattodoor@gmail.com");
$sheet->mergeCells('A6:C6');
$sheet->getStyle('A6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

// Add Table Header
$headers = ['Category Code', 'Category Name', 'Category Description'];
$columnIndex = 'A';

$sheet->setCellValue('A8', 'Category Details');
$sheet->mergeCells('A8:C8');
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
        $sheet->setCellValue('A' . $rowIndex, $row['category_code']);
        $sheet->setCellValue('B' . $rowIndex, $row['category_name']);
        $sheet->setCellValue('C' . $rowIndex, $row['category_desc']);
        $rowIndex++;
    }
} else {
    $sheet->setCellValue('A11', 'No category data available');
    $sheet->mergeCells('A11:C11');
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
$sheet->getStyle('A10:C' . ($rowIndex - 1))->applyFromArray($styleArray);

// Output Excel File for Download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Category_Details.xlsx"');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>

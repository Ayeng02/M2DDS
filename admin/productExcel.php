<?php
require '../phpspreadsheet/vendor/autoload.php'; // Load PhpSpreadsheet
include '../includes/db_connect.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Query to fetch all product details
$query = "SELECT p.prod_code, c.category_name, p.prod_name, p.prod_price, p.prod_qoh, p.prod_discount 
          FROM product_tbl p
          JOIN category_tbl c ON p.category_code = c.category_code
          ORDER BY p.prod_code DESC";
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

// Add Table Header
$headers = ['Product Code', 'Category Name', 'Product Name', 'Price', 'Quantity', 'Discount'];
$columnIndex = 'A';

$sheet->setCellValue('A8', 'Product Details');
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
        $sheet->setCellValue('A' . $rowIndex, $row['prod_code']);
        $sheet->setCellValue('B' . $rowIndex, $row['category_name']);
        $sheet->setCellValue('C' . $rowIndex, $row['prod_name']);
        $sheet->setCellValue('D' . $rowIndex, number_format($row['prod_price'], 2));
        $sheet->setCellValue('E' . $rowIndex, $row['prod_qoh']);
        $sheet->setCellValue('F' . $rowIndex, $row['prod_discount']);
        $rowIndex++;
    }
} else {
    $sheet->setCellValue('A11', 'No product data available');
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
header('Content-Disposition: attachment; filename="Product_Details.xlsx"');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>

<?php
require '../phpspreadsheet/vendor/autoload.php'; // Load PhpSpreadsheet
include '../includes/db_connect.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Query to fetch cashier transaction details
$query = "SELECT p.pos_code, pp.prod_name, p.pos_qty, 
            p.pos_discount, p.total_amount, p.amount_received, p.pos_change, 
            CONCAT(e.emp_fname, ' ', e.emp_lname) as fullname, p.transac_date
          FROM pos_tbl p
          JOIN emp_tbl e ON p.pos_personnel = e.emp_id
          JOIN product_tbl pp ON p.prod_code = pp.prod_code
          WHERE e.emp_role = 'Cashier'
          ORDER BY p.pos_code DESC
          ";
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
$sheet->setCellValue('A1', "Melo's Meatshop");
$sheet->mergeCells('A1:H1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(20);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A2', "Where Quality Meets Affordability");
$sheet->mergeCells('A2:H2');
$sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(16);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A3', "Contact: +63 938 895 2457");
$sheet->mergeCells('A3:H3');
$sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A4', "Email: meattodoor@gmail.com");
$sheet->mergeCells('A4:H4');
$sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

// Add Table Header
$headers = ['POS Code', 'Product Name', 'Quantity', 'Discount', 'Total Amount', 'Amount Received', 'Change', 'Cashier Name', 'Transaction Date'];
$columnIndex = 'A';

$sheet->setCellValue('A6', 'Cashier Transactions Details');
$sheet->mergeCells('A6:H6');
$sheet->getStyle('A6')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

foreach ($headers as $header) {
    $sheet->setCellValue($columnIndex . '8', $header);
    $sheet->getStyle($columnIndex . '8')->getFont()->setBold(true);
    $sheet->getStyle($columnIndex . '8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getColumnDimension($columnIndex)->setAutoSize(true);
    $columnIndex++;
}

// Populate Table Rows
$rowIndex = 9; // Data starts at row 9
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $rowIndex, $row['pos_code']);
        $sheet->setCellValue('B' . $rowIndex, $row['prod_name']);
        $sheet->setCellValue('C' . $rowIndex, $row['pos_qty']);
        $sheet->setCellValue('D' . $rowIndex, number_format($row['pos_discount'], 2));
        $sheet->setCellValue('E' . $rowIndex, number_format($row['total_amount'], 2));
        $sheet->setCellValue('F' . $rowIndex, number_format($row['amount_received'], 2));
        $sheet->setCellValue('G' . $rowIndex, number_format($row['pos_change'], 2));
        $sheet->setCellValue('H' . $rowIndex, $row['fullname']);
        $sheet->setCellValue('I' . $rowIndex, $row['transac_date']);
        $rowIndex++;
    }
} else {
    $sheet->setCellValue('A9', 'No cashier data available');
    $sheet->mergeCells('A9:I9');
    $sheet->getStyle('A9')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
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
$sheet->getStyle('A8:I' . ($rowIndex - 1))->applyFromArray($styleArray);

// Output Excel File for Download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Cashier_Transactions.xlsx"');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>

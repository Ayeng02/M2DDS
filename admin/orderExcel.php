<?php
require '../phpspreadsheet/vendor/autoload.php'; // Load PhpSpreadsheet
include '../includes/db_connect.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Query to fetch all order details
$query = "SELECT 
            o.order_id, 
            o.order_fullname AS customer_name, 
            o.order_phonenum, 
            CONCAT(o.order_purok, ' ', o.order_barangay, ', ', o.order_province) AS order_address,
            o.order_mop AS payment_method, 
            o.order_qty AS total_items, 
            o.order_total AS total_amount, 
            o.order_cash AS amount_paid, 
            o.order_change AS change_given, 
            s.status_name AS order_status
          FROM order_tbl o
          JOIN status_tbl s ON o.status_code = s.status_code
          ORDER BY o.order_id DESC";

$result = $conn->query($query);

// Create a new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Add Logo - Centering the logo
$logo = '../img/mtdd_logo.png';
if (file_exists($logo)) {
    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $drawing->setPath($logo);
    $drawing->setCoordinates('D1');
    $drawing->setHeight(50);
    $drawing->setWorksheet($sheet);
}

// Add Title and Contact Information
$sheet->setCellValue('A1', "Melo's Meatshop");
$sheet->mergeCells('A1:J1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(20);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A2', "Where Quality Meets Affordability");
$sheet->mergeCells('A2:J2');
$sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(16);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A3', "Contact: +63 938 895 2457");
$sheet->mergeCells('A3:J3');
$sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A4', "Email: meattodoor@gmail.com");
$sheet->mergeCells('A4:J4');
$sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

// Add Table Header
$headers = [
    'Order ID', 'Customer Name', 'Phone Number', 'Address', 
    'Payment Method', 'Total Items', 'Total Amount', 
    'Amount Paid', 'Change Given', 'Order Status'
];
$columnIndex = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($columnIndex . '6', $header);
    $sheet->getStyle($columnIndex . '6')->getFont()->setBold(true);
    $sheet->getStyle($columnIndex . '6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getColumnDimension($columnIndex)->setAutoSize(true);
    $columnIndex++;
}

// Populate Table Rows
$rowIndex = 7; // Data starts at row 7
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $rowIndex, $row['order_id']);
        $sheet->setCellValue('B' . $rowIndex, $row['customer_name']);
        $sheet->setCellValue('C' . $rowIndex, $row['order_phonenum']);
        $sheet->setCellValue('D' . $rowIndex, $row['order_address']);
        $sheet->setCellValue('E' . $rowIndex, $row['payment_method']);
        $sheet->setCellValue('F' . $rowIndex, $row['total_items']);
        $sheet->setCellValue('G' . $rowIndex, number_format($row['total_amount'], 2));
        $sheet->setCellValue('H' . $rowIndex, number_format($row['amount_paid'], 2));
        $sheet->setCellValue('I' . $rowIndex, number_format($row['change_given'], 2));
        $sheet->setCellValue('J' . $rowIndex, $row['order_status']);
        $rowIndex++;
    }
} else {
    $sheet->setCellValue('A7', 'No order data available');
    $sheet->mergeCells('A7:J7');
    $sheet->getStyle('A7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
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
$sheet->getStyle('A6:J' . ($rowIndex - 1))->applyFromArray($styleArray);

// Output Excel File for Download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Order_Details.xlsx"');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>

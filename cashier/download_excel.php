<?php
session_start();
require '../includes/db_connect.php';

// Load PhpSpreadsheet classes
require '../phpspreadsheet/vendor/autoload.php';  // Ensure path to autoload.php is correct
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

$emp_id = $_SESSION['emp_id'];

// Call the stored function to get the cashier's info (name and image)
$query_emp = "SELECT sf_getEmpInfo('$emp_id') AS emp_info";
$result_emp = $conn->query($query_emp);
$emp = $result_emp->fetch_assoc();

// Split the returned result into name and image (in case image is needed later)
list($cashier_name, $cashier_img) = explode('|', $emp['emp_info']);

// Create new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set spreadsheet title and metadata
$sheet->setTitle('Logs Report');



// Insert logo
$drawing = new Drawing();
$drawing->setName('Logo');
$drawing->setDescription('Logo');
$drawing->setPath('../img/mtdd_logo.png'); // Path to your logo
$drawing->setHeight(70);  // Adjust logo height
$drawing->setCoordinates('A1'); // Position the logo at the top
$drawing->setWorksheet($sheet);

// Business information
$sheet->setCellValue('A3', "Melo's Meatshop");
$sheet->getStyle('A3')->getFont()->setSize(20)->setBold(true);
$sheet->getStyle('A3')->getFont()->getColor()->setARGB('A72828');
$sheet->setCellValue('A4', "Where Quality Meets Affordability");
$sheet->setCellValue('A5', 'Contact: +63 938 895 2457');
$sheet->setCellValue('A6', 'Email: meattodoor@gmail.com');

// Aligning business information to center
$sheet->getStyle('A3:A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->mergeCells('A3:J3'); // Merge cells for business name
$sheet->mergeCells('A4:J4');
$sheet->mergeCells('A5:J5');
$sheet->mergeCells('A6:J6');

// Add cashier name below the business details
// Set cashier name and merge cells
$sheet->setCellValue('A8', 'Cashier: ' . $cashier_name);
$sheet->mergeCells('A8:J8');

// Center alignment for the merged cells
$sheet->getStyle('A8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A8')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

// Set font size, boldness, and color
$sheet->getStyle('A8')->getFont()->setSize(15)->setBold(true);
$sheet->getStyle('A8')->getFont()->getColor()->setARGB('FFA72828');

// Set padding effect by adjusting row height
$sheet->getRowDimension(8)->setRowHeight(30); // Adjust height for padding effect



// Table header
$sheet->setCellValue('A9', 'Transaction Code');
$sheet->setCellValue('B9', 'Product Code');
$sheet->setCellValue('C9', 'Product Name');
$sheet->setCellValue('D9', 'Quantity');
$sheet->setCellValue('E9', 'Discount');
$sheet->setCellValue('F9', 'Total Amount');
$sheet->setCellValue('G9', 'Amount Received');
$sheet->setCellValue('H9', 'Change');
$sheet->setCellValue('I9', 'Price');
$sheet->setCellValue('J9', 'Date');

// Style header (bold and background color)
$sheet->getStyle('A9:J9')->getFont()->setBold(true);
$sheet->getStyle('A9:J9')->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setARGB('FF8225'); // Background color for headers

// Fetch data from the database
$query = "SELECT p.*, pr.prod_name, pr.prod_price 
          FROM pos_tbl p 
          JOIN product_tbl pr ON p.prod_code = pr.prod_code 
          WHERE p.pos_personnel = '$emp_id' 
          ORDER BY p.transac_date DESC";

$result = $conn->query($query);
$rowNum = 10;  // Starting row for data (adjusted for the header and additional details)

// Populate data rows
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowNum, $row['pos_code']);
    $sheet->setCellValue('B' . $rowNum, $row['prod_code']);
    $sheet->setCellValue('C' . $rowNum, $row['prod_name']);
    $sheet->setCellValue('D' . $rowNum, $row['pos_qty']);
    $sheet->setCellValue('E' . $rowNum, $row['pos_discount']);
    $sheet->setCellValue('F' . $rowNum, $row['total_amount']);
    $sheet->setCellValue('G' . $rowNum, $row['amount_received']);
    $sheet->setCellValue('H' . $rowNum, $row['pos_change']);
    $sheet->setCellValue('I' . $rowNum, $row['prod_price']);
    $sheet->setCellValue('J' . $rowNum, date('F j, Y h:i A', strtotime($row['transac_date'])));
    $rowNum++;
}

// Auto-size columns
foreach (range('A', 'J') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set HTTP headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="logs_report.xlsx"');
header('Cache-Control: max-age=0');

// Write spreadsheet to output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>

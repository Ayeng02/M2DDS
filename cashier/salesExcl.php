<?php
session_start();
require '../phpspreadsheet/vendor/autoload.php'; // Load PhpSpreadsheet
require '../includes/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['emp_id'])) {
    header('Location: login.php');
    exit();
}

$emp_id = $_SESSION['emp_id'];

// Get employee details
$sql = "SELECT emp_fname, emp_lname, emp_role, emp_email, emp_num, emp_address, emp_img FROM emp_tbl WHERE emp_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

// Get sales data
$query = "SELECT p.pos_personnel, p.prod_code, pr.prod_name, pr.prod_price, SUM(p.total_amount) AS total_sales
          FROM pos_tbl p
          JOIN product_tbl pr ON p.prod_code = pr.prod_code
          WHERE p.pos_personnel = '$emp_id'
          GROUP BY p.pos_personnel, p.prod_code, pr.prod_name, pr.prod_price
          ORDER BY total_sales DESC";
$result2 = $conn->query($query);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set Logo
$logo = '../img/mtdd_logo.png';
if (file_exists($logo)) {
    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $drawing->setPath($logo);
    $drawing->setCoordinates('A1');
    $drawing->setHeight(50);
    $drawing->setWorksheet($sheet);
}

// Add Title and Company Info
$sheet->setCellValue('A3', "Melo's Meatshop");
$sheet->mergeCells('A3:E3');
$sheet->getStyle('A3')->getFont()->setBold(true)->setSize(20);
$sheet->setCellValue('A4', "Where Quality Meets Affordability");
$sheet->mergeCells('A4:E4');
$sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(16);
$sheet->setCellValue('A5', "Contact: +63 938 895 2457");
$sheet->setCellValue('A6', "Email: meattodoor@gmail.com");

// Employee Details
$sheet->setCellValue('A8', 'Employee Details');
$sheet->getStyle('A8')->getFont()->setBold(true);
$sheet->setCellValue('A9', 'Name: ' . $employee['emp_fname'] . ' ' . $employee['emp_lname']);

// Check if the employee image exists
if (!empty($employee['emp_img'])) {
    $img_path = '../' . $employee['emp_img']; // Adjust path as necessary

    if (file_exists($img_path)) {
        // Create a Drawing object
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setPath($img_path); // Set the image path
        $drawing->setCoordinates('E8'); // Adjust the cell position where the image should be placed
        $drawing->setHeight(100); // Set the height of the image
        $drawing->setWorksheet($sheet); // Attach the drawing to the worksheet
    }
}

$sheet->setCellValue('A10', 'Role: ' . $employee['emp_role']);
$sheet->setCellValue('A11', 'Employee Email: ' . $employee['emp_email']);
$sheet->setCellValue('A12', 'Employee Number: ' . $employee['emp_num']);

// Add a blank row for spacing between Employee Details and the table
$sheet->mergeCells('A13:E13'); // Merging cells across the same width as the table headers
$sheet->getRowDimension(13)->setRowHeight(15); // Adds gap

// Add "Product Sale Records" title with border and background color
$sheet->setCellValue('A14', 'Product Sale Records');
$sheet->mergeCells('A14:E14');
$sheet->getStyle('A14')->getFont()->setBold(true);
$sheet->getStyle('A14')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

// Apply a border to the merged cell and set background color
$sheet->getStyle('A14:E14')->applyFromArray([
    'borders' => [
        'outline' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'], // Black color for the border
        ],
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFCCE5FF'] // Light blue color
    ]
]);

// Set border style for headers and data rows
$borderStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'], // Black color for the border
        ],
    ],
];

// Set column headers for the table
$sheet->setCellValue('A15', 'Employee ID')
    ->setCellValue('B15', 'Product Code')
    ->setCellValue('C15', 'Product Name')
    ->setCellValue('D15', 'Price')
    ->setCellValue('E15', 'Total Sales');

// Apply styles to headers
$sheet->getStyle('A15:E15')->getFont()->setBold(true);
$sheet->getStyle('A15:E15')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    ->getStartColor()->setARGB('FFCCE5FF'); // Same light blue color for table headers
$sheet->getStyle('A15:E15')->applyFromArray($borderStyle); // Apply border to header cells

// Populate table rows with data, applying the same border style to each row
$rowNum = 16;
if ($result2->num_rows > 0) {
    while ($row = $result2->fetch_assoc()) {
        $sheet->setCellValue('A' . $rowNum, $row['pos_personnel']);
        $sheet->setCellValue('B' . $rowNum, $row['prod_code']);
        $sheet->setCellValue('C' . $rowNum, $row['prod_name']);
        $sheet->setCellValue('D' . $rowNum, $row['prod_price']);
        $sheet->setCellValue('E' . $rowNum, $row['total_sales']);
        
        // Apply border style to each data row
        $sheet->getStyle("A{$rowNum}:E{$rowNum}")->applyFromArray($borderStyle);
        
        $rowNum++;
    }
} else {
    $sheet->setCellValue('A' . $rowNum, 'No data available');
    $sheet->mergeCells("A{$rowNum}:E{$rowNum}");
    $sheet->getStyle("A{$rowNum}:E{$rowNum}")->applyFromArray($borderStyle);
}

// Adjust column widths for readability
foreach (range('A', 'E') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Download Excel file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Product Sales Record.xlsx"');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>

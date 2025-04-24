<?php
ob_start(); // Avoid output before PDF generation

require __DIR__ . '/../vendor/autoload.php'; // Ensure correct path
include 'config.php'; // Your DB connection

$pdf = new \TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('HRMS');
$pdf->SetTitle('Staff List');
$pdf->SetHeaderData('', 0, 'CHARLEX INTERNATIONAL CORPORATION', "Staff List - " . date('F j, Y'));
$pdf->setHeaderFont(['helvetica', '', 10]);
$pdf->setFooterFont(['helvetica', '', 8]);
$pdf->SetMargins(15, 27, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->SetFont('helvetica', '', 10);
$pdf->AddPage();

// Department Filter
$departmentFilter = $_GET['department'] ?? 'Show all';

$query = "SELECT emp_id, first_name, middle_name, last_name, phone_number, designation, email_id, department FROM tblemployees";
$params = [];
$types = '';

if ($departmentFilter !== 'Show all') {
    $query .= " WHERE department = ?";
    $params[] = $departmentFilter;
    $types .= 's';
}

$query .= " ORDER BY date_created DESC";

$stmt = mysqli_prepare($conn, $query);
if ($types) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Header
$html = '<h2 style="text-align:center;">Staff List</h2>';
if ($departmentFilter !== 'Show all') {
    $html .= "<p style='text-align:center;'>Department: <strong>$departmentFilter</strong></p>";
}

// Table
$html .= '<table border="1" cellpadding="4" cellspacing="0">
            <thead>
                <tr style="background-color:#f2f2f2;">
                    <th>Employee ID</th>
                    <th>Full Name</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Designation</th>
                </tr>
            </thead>
            <tbody>';

while ($row = mysqli_fetch_assoc($result)) {
    $fullName = htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
    $html .= "<tr>
                <td>{$row['emp_id']}</td>
                <td>$fullName</td>
                <td>{$row['phone_number']}</td>
                <td>{$row['email_id']}</td>
                <td>{$row['department']}</td>
                <td>{$row['designation']}</td>
              </tr>";
}

$html .= '</tbody></table>';

ob_end_clean(); // Clear output buffer before PDF output

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Staff_List.pdf', 'I');
exit;
?>

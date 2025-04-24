<?php
ob_start(); // Start output buffering to prevent whitespace issues

require __DIR__ . '/../vendor/autoload.php'; // Adjust if TCPDF is elsewhere
include 'config.php';

$pdf = new \TCPDF(); // Use backslash to reference the global namespace class


$fromDate = $_GET['from_date'] ?? null;
$toDate = $_GET['to_date'] ?? null;
$leaveStatus = $_GET['leave_status'] ?? 'Show all';

$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('HRMS');
$pdf->SetTitle('Leave Request Report');
$pdf->SetHeaderData('', 0, 'CHARLEX INTERNATIONAL CORPORATION', "Leave Request Report - " . date('F j, Y'));
$pdf->setHeaderFont(['helvetica', '', 10]);
$pdf->setFooterFont(['helvetica', '', 8]);
$pdf->SetMargins(15, 27, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->SetFont('helvetica', '', 10);
$pdf->AddPage();

// SQL query
$query = "SELECT l.from_date, l.to_date, l.requested_days, l.remarks,
                 l.leave_status, e.first_name, e.middle_name, e.last_name
          FROM tblleave l
          JOIN tblemployees e ON l.empid = e.emp_id
          WHERE l.from_date >= ? AND l.to_date <= ?";

$params = [$fromDate, $toDate];
$types = 'ss';

if ($leaveStatus !== 'Show all') {
    $query .= " AND l.leave_status = ?";
    $params[] = $leaveStatus;
    $types .= 'i';
}

$stmt = mysqli_prepare($conn, $query);
if ($types) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Build PDF HTML
$html = '<h2 style="text-align:center;">Leave Request Report</h2>';
if ($fromDate && $toDate) {
    $html .= "<p style='text-align:center;'>From: <strong>$fromDate</strong> To: <strong>$toDate</strong></p>";
}

$html .= '<table border="1" cellpadding="4" cellspacing="0">
            <thead>
                <tr style="background-color:#f2f2f2;">
                    <th>Employee</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Days</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>';

$statusMap = [
    0 => 'Pending',
    1 => 'Approved',
    2 => 'Cancelled',
    3 => 'Recalled',
    4 => 'Rejected'
];

while ($row = mysqli_fetch_assoc($result)) {
    $name = htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
    $from = htmlspecialchars($row['from_date']);
    $to = htmlspecialchars($row['to_date']);
    $days = htmlspecialchars($row['requested_days']);
    $status = $statusMap[$row['leave_status']] ?? 'Unknown';
    $remarks = htmlspecialchars($row['remarks']);

    $html .= "<tr>
                <td>$name</td>
                <td>$from</td>
                <td>$to</td>
                <td>$days</td>
                <td>$status</td>
                <td>$remarks</td>
              </tr>";
}

$html .= '</tbody></table>';

ob_end_clean(); // Clean buffer before sending PDF output
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Leave_Report.pdf', 'I');
exit;

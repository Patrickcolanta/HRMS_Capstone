<?php
ob_start(); // Avoid "already sent" issues

require __DIR__ . '/../vendor/autoload.php'; // Adjust path if needed
include 'config.php';

$pdf = new \TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('HRMS');
$pdf->SetTitle('Attendance Report');
$pdf->SetHeaderData('', 0, 'CHARLEX INTERNATIONAL CORPORATION', "Attendance Report - " . date('F j, Y'));
$pdf->setHeaderFont(['helvetica', '', 10]);
$pdf->setFooterFont(['helvetica', '', 8]);
$pdf->SetMargins(15, 27, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->SetFont('helvetica', '', 10);
$pdf->AddPage();


// Get filters
$fromDate = $_GET['from_date'] ?? null;
$toDate = $_GET['to_date'] ?? null;

$query = "SELECT a.date, a.staff_id, 
                 e.first_name, e.middle_name, e.last_name,
                 a.time_in, a.time_out 
          FROM tblattendance a
          JOIN tblemployees e ON a.staff_id = e.staff_id
          WHERE a.is_archived = 0";

$params = [];
$types = '';

if ($fromDate && $toDate) {
    $query .= " AND a.date BETWEEN ? AND ?";
    $params = [$fromDate, $toDate];
    $types = 'ss';
}

$stmt = mysqli_prepare($conn, $query);
if ($types) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Build HTML
$html = '<h2 style="text-align:center;">Attendance Report</h2>';
if ($fromDate && $toDate) {
    $html .= "<p style='text-align:center;'>From: <strong>$fromDate</strong> To: <strong>$toDate</strong></p>";
}

$html .= '<table border="1" cellpadding="4" cellspacing="0">
            <thead>
                <tr style="background-color:#f2f2f2;">
                    <th>Date</th>
                    <th>Staff ID</th>
                    <th>Full Name</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Total Hours</th>
                </tr>
            </thead>
            <tbody>';

while ($data = mysqli_fetch_assoc($result)) {
    $name = htmlspecialchars($data['first_name'] . ' ' . $data['middle_name'] . ' ' . $data['last_name']);
    $date = htmlspecialchars($data['date']);
    $timeIn = $data['time_in'];
    $timeOut = $data['time_out'];
    $totalHours = '-';

    if ($timeIn && $timeOut) {
        $in = new DateTime($timeIn);
        $out = new DateTime($timeOut);
        $interval = $in->diff($out);

        $hours = $interval->h;
        $minutes = $interval->i;

        $totalHours = sprintf('%02d:%02d hrs', $hours + ($interval->d * 24), $minutes);
    }

    $html .= "<tr>
                <td>$date</td>
                <td>{$data['staff_id']}</td>
                <td>$name</td>
                <td>" . date('h:i A', strtotime($timeIn)) . "</td>
                <td>" . ($timeOut ? date('h:i A', strtotime($timeOut)) : '-') . "</td>
                <td>$totalHours</td>
              </tr>";
}

$html .= '</tbody></table>';

ob_end_clean(); // Clear output buffer before outputting PDF

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Attendance_Report.pdf', 'I');
exit;
?>

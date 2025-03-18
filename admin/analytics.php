<?php
include('../includes/config.php');

$data = [];
$months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

// Fetch all departments
$deptQuery = $conn->prepare("SELECT id, name FROM tbldepartments");
$deptQuery->execute();
$deptResult = $deptQuery->get_result();

$departments = [];
while ($row = $deptResult->fetch_assoc()) {
    $departments[$row['id']] = $row['name'];
}

// Fetch leave counts by department and month
foreach ($departments as $deptId => $deptName) {
    $monthlyData = [];

    for ($i = 1; $i <= 12; $i++) {
        $stmt = $conn->prepare("
            SELECT 
                (SELECT COUNT(*) FROM tblleave l
                 JOIN tblemployees e ON l.empid = e.id
                 WHERE MONTH(l.from_date) = ? AND e.department = ? AND l.leave_status = 0) AS pending,
                (SELECT COUNT(*) FROM tblleave l
                 JOIN tblemployees e ON l.empid = e.id
                 WHERE MONTH(l.from_date) = ? AND e.department = ? AND l.leave_status = 1) AS approved,
                (SELECT COUNT(*) FROM tblleave l
                 JOIN tblemployees e ON l.empid = e.id
                 WHERE MONTH(l.from_date) = ? AND e.department = ? AND l.leave_status = 3) AS recalled,
                (SELECT COUNT(*) FROM tblleave l
                 JOIN tblemployees e ON l.empid = e.id
                 WHERE MONTH(l.from_date) = ? AND e.department = ? AND l.leave_status = 4) AS rejected
        ");
        $stmt->bind_param("iiiiiiii", $i, $deptId, $i, $deptId, $i, $deptId, $i, $deptId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        $monthlyData[] = [
            "month" => $months[$i - 1],
            "pending" => (int) $result['pending'],
            "approved" => (int) $result['approved'],
            "recalled" => (int) $result['recalled'],
            "rejected" => (int) $result['rejected']
        ];
    }

    $data[$deptName] = $monthlyData;
}

echo json_encode($data);
?>

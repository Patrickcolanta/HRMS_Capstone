<?php
include("config.php");

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($sql);
$notificationCount = $result->num_rows;

$response = [
    "count" => $notificationCount,
    "notifications" => []
];

while ($row = $result->fetch_assoc()) {
    $response["notifications"][] = [
        "employee_name" => htmlspecialchars($row['employee_name']),
        "leave_type" => htmlspecialchars($row['leave_type']),
        "from_date" => htmlspecialchars($row['from_date']),
        "to_date" => htmlspecialchars($row['to_date']),
        "status" => $row['status'] == 1 ? "Leave Approved" : "Leave Recalled",
        "time" => date("h:i A, d M Y", strtotime($row['created_at']))
    ];
}

echo json_encode($response);
$conn->close();
?>

<?php
include('../includes/config.php'); // Database connection

header("Content-Type: application/json");

// Handle both JSON and FormData requests
$data = $_POST ?: json_decode(file_get_contents("php://input"), true);

$applicantId = isset($data['id']) ? intval($data['id']) : 0;
$newStatus = isset($data['status']) ? trim($data['status']) : "";

// ✅ Allowed statuses (match frontend dropdown values)
$allowedStatuses = ['Pending', 'Initial Interview', 'Final Interview', 'Pass', 'Fail'];

// ✅ Debug Logging
file_put_contents("debug_log.txt", "Received ID: $applicantId, Status: $newStatus\n", FILE_APPEND);

// ✅ Validate Input
if ($applicantId <= 0 || empty($newStatus) || !in_array($newStatus, $allowedStatuses)) {
    echo json_encode(["success" => false, "message" => "Invalid applicant ID or status."]);
    exit();
}

// ✅ Check if applicant exists
$stmt = $conn->prepare("SELECT id FROM job_applications WHERE id = ?");
$stmt->bind_param("i", $applicantId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Applicant not found."]);
    exit();
}
$stmt->close();

// ✅ Update status
$stmt = $conn->prepare("UPDATE job_applications SET status = ? WHERE id = ?");
$stmt->bind_param("si", $newStatus, $applicantId);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Status updated successfully.", "new_status" => $newStatus]);
} else {
    echo json_encode(["success" => false, "message" => "Database update failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
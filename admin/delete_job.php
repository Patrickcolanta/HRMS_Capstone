<?php
include('../includes/config.php');
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit();
}

if (!isset($_POST['job_id']) || empty($_POST['job_id'])) {
    echo json_encode(["status" => "error", "message" => "Job ID is required."]);
    exit();
}

$job_id = intval($_POST['job_id']);

// Check if the job exists
$checkQuery = "SELECT * FROM job_listings WHERE id = ?";
$stmtCheck = mysqli_prepare($conn, $checkQuery);
mysqli_stmt_bind_param($stmtCheck, "i", $job_id);
mysqli_stmt_execute($stmtCheck);
$result = mysqli_stmt_get_result($stmtCheck);

if (mysqli_num_rows($result) === 0) {
    echo json_encode(["status" => "error", "message" => "Job not found."]);
    exit();
}

// Delete the job
$sql = "DELETE FROM job_listings WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $job_id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["status" => "success", "message" => "Job deleted successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to delete job."]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

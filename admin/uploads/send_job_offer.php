<?php
session_start();
include('../includes/config.php'); // Database connection

header('Content-Type: application/json');

// Ensure only authorized users (Admin/Manager) can access
if (!isset($_SESSION['slogin']) || !isset($_SESSION['srole']) || !in_array($_SESSION['srole'], ['Manager', 'Admin'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

// Get form data
$applicantId = $_POST['applicant_id'] ?? '';
$jobTitle = $_POST['job_title'] ?? '';
$salaryOffer = $_POST['salary_offer'] ?? '';
$startDate = $_POST['start_date'] ?? '';
$jobDescription = $_POST['job_description'] ?? '';

// Validate inputs
if (!$applicantId || !$jobTitle || !$salaryOffer || !$startDate || !$jobDescription) {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit();
}

// Insert job offer into the database
$stmt = $conn->prepare("INSERT INTO job_offers (applicant_id, job_title, salary_offer, start_date, job_description) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("isdss", $applicantId, $jobTitle, $salaryOffer, $startDate, $jobDescription);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Job offer sent successfully"]);
} else {
    error_log("Database Error: " . $stmt->error);
    echo json_encode(["status" => "error", "message" => "Failed to send job offer"]);
}

$stmt->close();
$conn->close();
?>

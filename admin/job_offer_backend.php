<?php
include('../includes/config.php');

header('Content-Type: application/json'); // Ensure JSON response

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $applicantId = intval($_GET['id']);

    // Check if the applicant exists and passed the interview
    $checkStmt = $conn->prepare("SELECT id FROM job_applications WHERE id = ? AND status = 'Passed'");
    $checkStmt->bind_param("i", $applicantId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Applicant not eligible for endorsement."]);
        exit();
    }
    $checkStmt->close();

    // Update the status to "Endorsed for Job Offer"
    $stmt = $conn->prepare("UPDATE job_applications SET status = 'Endorsed for Job Offer' WHERE id = ?");
    $stmt->bind_param("i", $applicantId);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Applicant successfully endorsed."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to endorse applicant."]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
?>

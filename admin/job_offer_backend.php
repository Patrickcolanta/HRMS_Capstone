<?php
include('../includes/config.php');

header('Content-Type: application/json'); // Ensure JSON response

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $applicantId = intval($_GET['id']);

    // Check if the applicant exists and passed the interview
    $checkStmt = $conn->prepare("SELECT id, first_name, last_name, email, phone FROM job_applications WHERE id = ? AND status = 'Passed'");
    $checkStmt->bind_param("i", $applicantId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Applicant not eligible for endorsement."]);
        exit();
    }

    $applicant = $checkResult->fetch_assoc();
    $checkStmt->close();

    // Update the status to "Endorsed for Job Offer" in job_applications
    $updateStmt = $conn->prepare("UPDATE job_applications SET hiring_status = 'Endorsed for Job Offer' WHERE id = ?");
    $updateStmt->bind_param("i", $applicantId);
    
    if (!$updateStmt->execute()) {
        echo json_encode(["success" => false, "message" => "Failed to update applicant status."]);
        exit();
    }
    $updateStmt->close();

    // Insert into job_offers if not already existing
    $insertStmt = $conn->prepare("INSERT INTO job_offers (application_id, hiring_status, offer_sent, message) 
                                  VALUES (?, 'Pending', 0, 'Job offer pending') 
                                  ON DUPLICATE KEY UPDATE status = 'Pending'");
    $insertStmt->bind_param("i", $applicantId);

    if ($insertStmt->execute()) {
        echo json_encode(["success" => true, "message" => "Applicant successfully endorsed and added to job offers."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to insert applicant into job offers."]);
    }

    $insertStmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
?>

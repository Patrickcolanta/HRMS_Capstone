<?php
include('../includes/config.php');

header('Content-Type: application/json'); // Set JSON response header

// Check if it's a valid GET request with an ID
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $applicantId = intval($_GET['id']);

    if ($applicantId <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Invalid applicant ID."]);
        exit();
    }

    // ✅ Check if the applicant exists
    $checkStmt = $conn->prepare("SELECT id FROM job_applications WHERE id = ?");
    $checkStmt->bind_param("i", $applicantId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Applicant not found."]);
        exit();
    }
    $checkStmt->close();

    // ✅ Delete the applicant
    $stmt = $conn->prepare("DELETE FROM job_applications WHERE id = ?");
    $stmt->bind_param("i", $applicantId);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(["success" => true, "message" => "Applicant successfully deleted."]);
    } else {
        http_response_code(500);
        error_log("Delete Error: " . $stmt->error); // Log error for debugging
        echo json_encode(["success" => false, "message" => "Failed to delete applicant."]);
    }

    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}

$conn->close();
?>

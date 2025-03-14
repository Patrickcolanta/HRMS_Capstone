<?php
include('../includes/config.php');

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $applicantId = intval($_GET['id']);

    // Prepare and execute delete query
    $stmt = $conn->prepare("DELETE FROM job_applications WHERE id = ?");
    $stmt->bind_param("i", $applicantId);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete applicant."]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
?>

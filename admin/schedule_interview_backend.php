<?php
session_start();
include('../includes/config.php'); // Database connection

if (!isset($_SESSION['slogin']) || !isset($_SESSION['srole'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $requiredFields = ['application_id', 'interview_date', 'interview_time', 'interview_mode', 'interview_location', 'interviewer'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(["status" => "error", "message" => "Missing required fields"]);
            exit();
        }
    }

    // Secure input
    $application_id = intval($_POST['application_id']);
    $interview_date = $_POST['interview_date'];
    $interview_time = $_POST['interview_time'];
    $interview_mode = $_POST['interview_mode'];
    $interview_location = $_POST['interview_location'];
    $interviewer = $_POST['interviewer'];

    // Fetch applicant details
    $stmt = $conn->prepare("SELECT id, status FROM job_applications WHERE id = ?");
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Application ID not found"]);
        exit();
    }

    $applicant = $result->fetch_assoc();
    $status = $applicant['status'];

    if (!in_array($status, ['Initial Interview', 'Final Interview'])) {
        echo json_encode(["status" => "error", "message" => "Only applicants in Initial or Final Interview stages can be scheduled"]);
        exit();
    }

    if (isset($_POST['edit_mode']) && $_POST['edit_mode'] == 'true') {
        // Update interview details
        $updateStmt = $conn->prepare("UPDATE interviews SET interview_date = ?, interview_time = ?, interview_mode = ?, interview_location = ?, interviewer = ? WHERE application_id = ?");
        $updateStmt->bind_param("sssssi", $interview_date, $interview_time, $interview_mode, $interview_location, $interviewer, $application_id);
        
        if ($updateStmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Interview updated successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database update failed"]);
        }
    } else {
        // Insert new interview details
        $insertStmt = $conn->prepare("INSERT INTO interviews (application_id, interview_date, interview_time, interview_mode, interview_location, interviewer) VALUES (?, ?, ?, ?, ?, ?)");
        $insertStmt->bind_param("isssss", $application_id, $interview_date, $interview_time, $interview_mode, $interview_location, $interviewer);

        if ($insertStmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Interview scheduled successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database insert failed"]);
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
?>

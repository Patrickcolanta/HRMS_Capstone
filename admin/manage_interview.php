<?php
session_start();
include('../includes/config.php'); // Database connection

header('Content-Type: application/json');

// Ensure only authorized users (Admin/Manager) can access
if (!isset($_SESSION['slogin']) || !isset($_SESSION['srole']) || ($_SESSION['srole'] !== 'Manager' && $_SESSION['srole'] !== 'Admin')) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

$requestMode = $_POST['mode'] ?? ''; // Fetching request mode

if ($requestMode === "schedule" || $requestMode === "edit") {
    $applicationId = $_POST['application_id'] ?? '';
    $date = $_POST['interview_date'] ?? '';
    $time = $_POST['interview_time'] ?? '';
    $interviewMode = $_POST['interview_mode'] ?? '';
    $location = $_POST['interview_location'] ?? '';
    $interviewer = $_POST['interviewer'] ?? '';

    if (!$applicationId || !$date || !$time || !$interviewMode || !$location || !$interviewer) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit();
    }

    // Check if an interview already exists
    $checkStmt = $conn->prepare("SELECT COUNT(*) as total FROM interviews WHERE application_id = ?");
    $checkStmt->bind_param("i", $applicationId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $count = $result->fetch_assoc()['total'] ?? 0;
    $checkStmt->close();

    if ($count > 0) {
        // Update existing interview
        $stmt = $conn->prepare("UPDATE interviews 
                                SET interview_date = ?, interview_time = ?, interview_mode = ?, interview_location = ?, interviewer = ?, status = 'Scheduled' 
                                WHERE application_id = ?");
        $stmt->bind_param("sssssi", $date, $time, $interviewMode, $location, $interviewer, $applicationId);
    } else {
        // Insert new interview
        $stmt = $conn->prepare("INSERT INTO interviews (application_id, interview_date, interview_time, interview_mode, interview_location, interviewer, status) 
                                VALUES (?, ?, ?, ?, ?, ?, 'Scheduled')");
        $stmt->bind_param("isssss", $applicationId, $date, $time, $interviewMode, $location, $interviewer);
    }

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Interview successfully " . ($requestMode === "schedule" ? "scheduled" : "updated")]);
    } else {
        error_log("Database Error: " . $stmt->error);
        echo json_encode(["status" => "error", "message" => "Database error occurred. Please try again."]);
    }
    $stmt->close();

} elseif ($requestMode === "update_result") { // Updating interview result and status
    $applicationId = $_POST['application_id'] ?? '';
    $result = $_POST['interview_result'] ?? '';

    if (!$applicationId || !$result) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit();
    }

    // Update the interview result and set status to "Completed"
    $stmt = $conn->prepare("UPDATE interviews SET result = ?, status = 'Completed' WHERE application_id = ?");
    $stmt->bind_param("si", $result, $applicationId);

    if ($stmt->execute()) {
        // If the result is "Passed", update the hired_date in job_applications
        if ($result === "Passed") {
            $hiredDate = date("Y-m-d H:i:s"); // Get the current timestamp
            $stmt2 = $conn->prepare("UPDATE job_applications SET hired_date = ? WHERE id = ?");
            $stmt2->bind_param("si", $hiredDate, $applicationId);

            if ($stmt2->execute()) {
                echo json_encode(["status" => "success", "message" => "Interview result updated, status set to Completed, and hired date recorded"]);
            } else {
                error_log("Database Error (Updating hired_date): " . $stmt2->error);
                echo json_encode(["status" => "error", "message" => "Interview result updated, but failed to update hired date."]);
            }
            $stmt2->close();
        } else {
            echo json_encode(["status" => "success", "message" => "Interview result updated and status set to Completed"]);
        }
    } else {
        error_log("Database Error: " . $stmt->error);
        echo json_encode(["status" => "error", "message" => "Database error occurred. Please try again."]);
    }

    $stmt->close();
}

$conn->close();
exit();
?>

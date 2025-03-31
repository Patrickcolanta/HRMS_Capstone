<?php
session_start();
include('../includes/config.php'); // Database connection

header('Content-Type: application/json');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

// Ensure only authorized users (Admin/Manager) can access
if (!isset($_SESSION['slogin']) || !isset($_SESSION['srole']) || ($_SESSION['srole'] !== 'HR' && $_SESSION['srole'] !== 'Admin')) {
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

    // Fetch applicant's email
    $applicantStmt = $conn->prepare("SELECT first_name, last_name, email FROM job_applications WHERE id = ?");
    $applicantStmt->bind_param("i", $applicationId);
    $applicantStmt->execute();
    $applicantResult = $applicantStmt->get_result();
    $applicant = $applicantResult->fetch_assoc();
    $applicantStmt->close();

    if (!$applicant) {
        echo json_encode(["status" => "error", "message" => "Applicant not found."]);
        exit();
    }

    $fullName = $applicant['first_name'] . ' ' . $applicant['last_name'];
    $toEmail = $applicant['email'];

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
        // Send email notification via PHPMailer
        $mail = new PHPMailer(true);

        try {
            
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'colantapatrick0@gmail.com'; // Your email
            $mail->Password = 'njst eqde sygo hxkz'; // Your App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Secure connection
            $mail->Port = 587;

            // Sender and recipient
            $mail->setFrom('colantapatrick0@gmail.com', 'HRMS System');
            $mail->addAddress($toEmail);

            // Email Content
            $mail->isHTML(true);
            $mail->Subject = "Interview Schedule Notification";
            $mail->Body    = "
                <h3>Dear $fullName,</h3>
                <p>We are pleased to inform you that your interview has been scheduled.</p>
                <ul>
                    <li><strong>Date:</strong> $date</li>
                    <li><strong>Time:</strong> $time</li>
                    <li><strong>Mode:</strong> $interviewMode</li>
                    <li><strong>Location:</strong> $location</li>
                    <li><strong>Interviewer:</strong> $interviewer</li>
                </ul>
                <p>Kindly confirm your availability.</p>
                <p>Best Regards,</p>
                <p><strong>HR Department</strong></p>
            ";

            // Send the email
            $mail->send();
            echo json_encode(["status" => "success", "message" => "Interview successfully " . ($requestMode === "schedule" ? "scheduled" : "updated") . " and email notification sent"]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Interview scheduled, but email notification failed. " . $mail->ErrorInfo]);
        }
    } else {
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

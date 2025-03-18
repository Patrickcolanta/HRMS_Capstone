<?php
session_start();
include('../includes/config.php'); // Database connection
include('../includes/credentials.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

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
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, status FROM job_applications WHERE id = ?");
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Application ID not found"]);
        exit();
    }

    $applicant = $result->fetch_assoc();
    $status = $applicant['status'];
    $email = $applicant['email'];
    $applicant_name = $applicant['first_name'] . ' ' . $applicant['last_name'];

    if (!in_array($status, ['Initial Interview', 'Final Interview'])) {
        echo json_encode(["status" => "error", "message" => "Only applicants in Initial or Final Interview stages can be scheduled"]);
        exit();
    }

    if (isset($_POST['edit_mode']) && $_POST['edit_mode'] == 'true') {
        // Update interview details
        $updateStmt = $conn->prepare("UPDATE interviews SET interview_date = ?, interview_time = ?, interview_mode = ?, interview_location = ?, interviewer = ? WHERE application_id = ?");
        $updateStmt->bind_param("sssssi", $interview_date, $interview_time, $interview_mode, $interview_location, $interviewer, $application_id);
        
        if ($updateStmt->execute()) {
            sendInterviewEmail($email, $applicant_name, $interview_date, $interview_time, $interview_mode, $interview_location, $interviewer);
            echo json_encode(["status" => "success", "message" => "Interview updated successfully and email sent"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database update failed"]);
        }
    } else {
        // Insert new interview details
        $insertStmt = $conn->prepare("INSERT INTO interviews (application_id, interview_date, interview_time, interview_mode, interview_location, interviewer) VALUES (?, ?, ?, ?, ?, ?)");
        $insertStmt->bind_param("isssss", $application_id, $interview_date, $interview_time, $interview_mode, $interview_location, $interviewer);

        if ($insertStmt->execute()) {
            sendInterviewEmail($email, $applicant_name, $interview_date, $interview_time, $interview_mode, $interview_location, $interviewer);
            echo json_encode(["status" => "success", "message" => "Interview scheduled successfully and email sent"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database insert failed"]);
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}

// Function to send an interview confirmation email
function sendInterviewEmail($to, $name, $date, $time, $mode, $location, $interviewer) {
    $mail = new PHPMailer(true);

    try {
        // Enable SMTP Debugging (Set to 0 for production)
        $mail->SMTPDebug = 2;

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
        $mail->addAddress($to); // Use `$to` instead of `$email`
        
        // Email Content
        $mail->isHTML(true);
        $mail->Subject = "Interview Schedule Notification";
        $mail->Body    = "
            <h3>Dear $name,</h3>
            <p>We are pleased to inform you that your interview has been scheduled.</p>
            <ul>
                <li><strong>Date:</strong> $date</li>
                <li><strong>Time:</strong> $time</li>
                <li><strong>Mode:</strong> $mode</li>
                <li><strong>Location:</strong> $location</li>
                <li><strong>Interviewer:</strong> $interviewer</li>
            </ul>
            <p>Kindly confirm your availability.</p>
            <p>Best Regards,</p>
            <p><strong>HR Department</strong></p>
        ";

        // Send email
        if ($mail->send()) {
            error_log("Email sent successfully to $to");
            return true;
        } else {
            error_log("Email failed: " . $mail->ErrorInfo);
            return false;
        }
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

?>

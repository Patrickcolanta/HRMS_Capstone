<?php
session_start();
include('../includes/config.php');
require '../vendor/autoload.php'; // Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

$mode = $_POST['mode'] ?? '';
$application_id = isset($_POST['application_id']) ? intval($_POST['application_id']) : 0;

if ($application_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Application ID.']);
    exit();
}

// Handle sending email
if ($mode === 'send_email') {
    $recipient_email = trim($_POST['email'] ?? '');
    $email_body = trim($_POST['message'] ?? '');

    if (empty($recipient_email) || empty($email_body)) {
        echo json_encode(['status' => 'error', 'message' => 'Email and message are required.']);
        exit();
    }

    // **1. Insert email log into database**
    $query = "INSERT INTO job_offer_emails (application_id, recipient_email, email_body, sent_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $application_id, $recipient_email, $email_body);
    $stmt->execute();

    // **2. Send Email via PHPMailer**
    $mail = new PHPMailer(true);
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'colantapatrick0@gmail.com'; // Your email
        $mail->Password = 'njst eqde sygo hxkz'; // Your App Password (not regular password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email Details
        $mail->setFrom('colantapatrick0@gmail.com', 'HRMS System');        $mail->addAddress($recipient_email);
        $mail->isHTML(true);
        $mail->Subject = 'Job Offer Update';
        $mail->Body = "<p>Dear Applicant,</p>
                       <p>$email_body</p>
                       <p>Best Regards,<br>HRMS Team</p>";

        // Send email
        $mail->send();

        echo json_encode(['status' => 'success', 'message' => 'Email sent successfully.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Email sending failed: ' . $mail->ErrorInfo]);
    }
    exit();
}

// Handle updating job offer status
if ($mode === 'update_status') {
    $job_status = $_POST['status'] ?? '';

    if (!in_array($job_status, ['Accepted', 'Rejected'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid job status.']);
        exit();
    }

    $query = "UPDATE job_offers SET status = ?, updated_at = NOW() WHERE application_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $job_status, $application_id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Job offer status updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update job offer status.']);
    }
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid operation.']);
exit();
?>

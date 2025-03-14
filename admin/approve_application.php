<?php
session_start();
header("Content-Type: application/json");
include('../includes/config.php');

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

// Email credentials (stored securely in config file)
define('EMAIL', 'colantapatrick0@gmail.com'); // Your Gmail
define('PASSWORD', 'njst eqde sygo hxkz'); // Your App Password

// 🛑 Check if the user is logged in
if (!isset($_SESSION['slogin']) || !isset($_SESSION['srole'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access!"]);
    exit();
}

// 🛑 Check if the user has Manager/Admin role
$userRole = $_SESSION['srole'];
if ($userRole !== 'Manager' && $userRole !== 'Admin') {
    echo json_encode(["status" => "error", "message" => "Permission denied!"]);
    exit();
}

// 🛑 Validate input
if (!isset($_POST['application_id']) || !is_numeric($_POST['application_id'])) {
    echo json_encode(["status" => "error", "message" => "Invalid application ID!"]);
    exit();
}

$app_id = intval($_POST['application_id']);

// 🛑 Check if the application exists and get its current status & applicant email
$sql_check = "SELECT status, applicant_name, email FROM job_applications WHERE id = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "i", $app_id);
mysqli_stmt_execute($stmt_check);
mysqli_stmt_store_result($stmt_check);

if (mysqli_stmt_num_rows($stmt_check) === 0) {
    mysqli_stmt_close($stmt_check);
    mysqli_close($conn);
    echo json_encode(["status" => "error", "message" => "Application not found."]);
    exit();
}

// Fetch current status and email
mysqli_stmt_bind_result($stmt_check, $current_status, $applicant_name, $applicant_email);
mysqli_stmt_fetch($stmt_check);
mysqli_stmt_close($stmt_check);

// ✅ Prevent re-approving applications
if ($current_status === 'Approved') {
    mysqli_close($conn);
    echo json_encode(["status" => "error", "message" => "This application is already approved."]);
    exit();
}

// ✅ Update application status to "Approved"
$sql_update = "UPDATE job_applications SET status = ? WHERE id = ?";
$stmt_update = mysqli_prepare($conn, $sql_update);
$approved_status = 'Approved';
mysqli_stmt_bind_param($stmt_update, "si", $approved_status, $app_id);
mysqli_stmt_execute($stmt_update);

if (mysqli_stmt_affected_rows($stmt_update) > 0) {
    // Email Content
    $emailSubject = "Application Approved - Next Steps";
    $emailBody = "
        <h3>Dear $applicant_name,</h3>
        <p>We are excited to inform you that your job application has been <strong>approved</strong>!</p>
        <p>Our team will reach out to you soon regarding the next steps.</p>
        <p>If you have any questions, please feel free to contact us.</p>
        <p>Congratulations, and we look forward to working with you!</p>
        <p>Best regards,<br>HR Team</p>
    ";

    // Send Email
    $mail = new PHPMailer(true);
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = EMAIL;
        $mail->Password = PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Sender & Recipient
        $mail->setFrom(EMAIL, 'HR Department');
        $mail->addAddress($applicant_email, $applicant_name);

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = $emailSubject;
        $mail->Body = $emailBody;

        // Send Email
        if ($mail->send()) {
            echo json_encode(["status" => "success", "message" => "Application approved & email sent"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Application approved, but email failed"]);
        }

    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        echo json_encode(["status" => "error", "message" => "Application approved, but email sending failed"]);
    }
} else {
    error_log("SQL Error: " . mysqli_error($conn)); // Log error for debugging
    echo json_encode(["status" => "error", "message" => "Failed to approve application."]);
}

// Close statement and connection
mysqli_stmt_close($stmt_update);
mysqli_close($conn);
exit();
?>

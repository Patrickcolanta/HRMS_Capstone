<?php
include('../includes/config.php');

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

// Define email credentials (store securely in a config file)
define('EMAIL', 'colantapatrick0@gmail.com'); // Your Gmail
define('PASSWORD', 'njst eqde sygo hxkz'); // Your App Password (Use App Password, NOT your real Gmail password)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $application_id = intval($_POST['application_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $interview_date = !empty($_POST['interview_date']) ? "'" . mysqli_real_escape_string($conn, $_POST['interview_date']) . "'" : "NULL";

    // Fetch applicant details
    $applicantQuery = "SELECT applicant_name, email FROM job_applications WHERE id = $application_id";
    $applicantResult = mysqli_query($conn, $applicantQuery);

    if ($applicantResult && mysqli_num_rows($applicantResult) > 0) {
        $applicant = mysqli_fetch_assoc($applicantResult);
        $applicant_name = $applicant['applicant_name'];
        $applicant_email = $applicant['email'];

        // Update the application status and interview date
        $updateQuery = "UPDATE job_applications SET status = '$status', interview_date = $interview_date WHERE id = $application_id";
        if (mysqli_query($conn, $updateQuery)) {

            // Prepare Email Content Based on Status
            $emailSubject = "";
            $emailBody = "";

            if ($status == "Interview Scheduled") {
                $emailSubject = "Interview Invitation - Next Steps";
                $emailBody = "
                    <h3>Dear $applicant_name,</h3>
                    <p>We are pleased to inform you that your application status has been updated to: <strong>$status</strong>.</p>
                    <p>Your interview is scheduled for: <strong>{$_POST['interview_date']}</strong>.</p>
                    <p>Please prepare accordingly and let us know if you have any questions.</p>
                    <p>Best regards,<br>HR Team</p>
                ";
            } elseif ($status == "Offer Sent") {
                $emailSubject = "Job Offer - Congratulations!";
                $emailBody = "
                    <h3>Dear $applicant_name,</h3>
                    <p>Congratulations! We are excited to offer you a position at our company.</p>
                    <p>Please review the offer details and respond at your earliest convenience.</p>
                    <p>Looking forward to welcoming you to our team!</p>
                    <p>Best regards,<br>HR Team</p>
                ";
            } elseif ($status == "Rejected") {
                $emailSubject = "Application Update - Thank You";
                $emailBody = "
                    <h3>Dear $applicant_name,</h3>
                    <p>We appreciate your interest in joining our company.</p>
                    <p>After careful consideration, we regret to inform you that your application has not been selected for this position.</p>
                    <p>We encourage you to apply for future opportunities that match your skills.</p>
                    <p>Best regards,<br>HR Team</p>
                ";
            }

            // Send Email if Needed
            if (!empty($emailSubject) && !empty($emailBody)) {
                $mail = new PHPMailer(true);
                try {
                    // SMTP Configuration
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
                    $mail->SMTPAuth = true;
                    $mail->Username = EMAIL;
                    $mail->Password = PASSWORD;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587; // 465 for SSL, 587 for TLS

                    // Sender & Recipient
                    $mail->setFrom(EMAIL, 'HR Department');
                    $mail->addAddress($applicant_email, $applicant_name);

                    // Email Content
                    $mail->isHTML(true);
                    $mail->Subject = $emailSubject;
                    $mail->Body = $emailBody;

                    // Send Email
                    if ($mail->send()) {
                        echo json_encode(["status" => "success", "message" => "Application updated & email sent"]);
                    } else {
                        echo json_encode(["status" => "error", "message" => "Application updated, but email failed"]);
                    }

                } catch (Exception $e) {
                    error_log("PHPMailer Error: " . $mail->ErrorInfo);
                    echo json_encode(["status" => "error", "message" => "Application updated, but email sending failed"]);
                }
            } else {
                echo json_encode(["status" => "success", "message" => "Application updated (no email needed)"]);
            }

        } else {
            echo json_encode(["status" => "error", "message" => "Database update failed"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Application not found"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
?>

<?php
session_start();
include('includes/config.php');
include('includes/credentials.php');
require 'vendor/autoload.php'; // Required if using Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json'); // Ensure JSON response

// Ensure the user session exists
// if (!isset($_SESSION['email'])) {
//     echo json_encode(['status' => 'error', 'message' => 'Session expired. Please log in again.']);
//     exit;
// }

// $email = $_SESSION['email'];

if (isset($_POST['email'])) {
    $email = $_POST['email']; // Get email from AJAX request
} else {
    echo json_encode(['status' => 'error', 'message' => 'Email not provided!']);
    exit;
}



// Prevent OTP spam (60-second cooldown)
if (isset($_SESSION['last_otp_request']) && (time() - $_SESSION['last_otp_request']) < 60) {
    echo json_encode(['status' => 'error', 'message' => 'Please wait before requesting another OTP.']);
    exit;
}

// Generate a new OTP
$otp = generateOTP(); // Use your existing function
$expiry = time() + (5 * 60); // 5 minutes from now

// Store OTP and expiry in session
$_SESSION['otp'] = $otp;
$_SESSION['otp_expiry'] = $expiry;
$_SESSION['last_otp_request'] = time();

// Send OTP via PHPMailer
if (sendOTP($email, $otp)) {
    echo json_encode(["status" => "success", "message" => "A new OTP has been sent to your email."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to send OTP. Please try again later."]);
}

// Function to generate OTP
function generateOTP() {
    return rand(100000, 999999); // 6-digit OTP
}

// Function to send OTP via PHPMailer
function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Use your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'colantapatrick0@gmail.com'; // Your email
        $mail->Password = 'njst eqde sygo hxkz'; // Your email password or app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Secure connection
        $mail->Port = 587;

        // Sender and recipient
        $mail->setFrom('colantapatrick0@gmail.com', 'HRMS System');
        $mail->addAddress($email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body = "<p>Dear User,</p>
                       <p>Your OTP code is: <strong>$otp</strong></p>
                       <p>This OTP is valid for <strong>5 minutes</strong>. Do not share it with anyone.</p>
                       <p>Regards,<br>HRMS Team</p>";

        // Send email
        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>

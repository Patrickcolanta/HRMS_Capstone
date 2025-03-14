<?php
session_start();
include('includes/config.php');

header('Content-Type: application/json'); // Ensure JSON response

// Ensure the user session exists
if (!isset($_SESSION['otp_email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired. Please log in again.']);
    exit;
}

$email = $_SESSION['otp_email'];

// Prevent OTP spam (60-second cooldown)
if (isset($_SESSION['last_otp_request']) && (time() - $_SESSION['last_otp_request']) < 60) {
    echo json_encode(['status' => 'error', 'message' => 'Please wait before requesting another OTP.']);
    exit;
}

// Generate a new OTP token
$otp = strval(rand(100000, 999999)); // Convert OTP to string to prevent type issues
$expiry = time() + (5 * 60); // 5 minutes from now

// Store OTP and expiry in session
$_SESSION['otp'] = $otp;
$_SESSION['otp_expiry'] = $expiry;
$_SESSION['last_otp_request'] = time();

// Send OTP via email
$subject = "Your New OTP Code";
$message = "Your OTP code is: $otp. It expires in 5 minutes.";
$headers = "From: no-reply@yourdomain.com\r\n";
$headers .= "Reply-To: no-reply@yourdomain.com\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

if (mail($email, $subject, $message, $headers)) {
    echo json_encode(["status" => "success", "message" => "A new OTP has been sent to your email."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to send OTP. Please try again later."]);
}
?>

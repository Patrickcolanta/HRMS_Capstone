<?php
session_start();
include('includes/config.php'); // Database connection
include('includes/credentials.php'); // SMTP credentials

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// ✅ Check if request is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    // ✅ Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
        exit();
    }

    // ✅ Check if email exists in database
    $stmt = $conn->prepare("SELECT emp_id FROM tblemployees WHERE email_id = ?");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'SQL Error: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email not found']);
        exit();
    }

    $user = $result->fetch_assoc();
    $user_id = $user['emp_id']; // ✅ Match correct column name

    // ✅ Generate a secure token
    $token = bin2hex(random_bytes(50));
    $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // ✅ Insert or update token in the database
    $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) 
                            VALUES (?, ?, ?) 
                            ON DUPLICATE KEY UPDATE token = ?, expires_at = ?");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Token Insert Error: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param("issss", $user_id, $token, $expiry, $token, $expiry);
    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Token Insert Error: ' . $stmt->error]);
        exit();
    }

    // ✅ Create reset link
    $reset_link = "http://localhost/dashboard/CAPSTONE/HRMS/reset_password.php?" . 'token=' . $token;

    // ✅ Send email using PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Change to your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = EMAIL;
        $mail->Password = PASS;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('no-reply@yourdomain.com', 'HRMS System');
        $mail->addAddress($email);

        $mail->Subject = "Password Reset Request";
        $mail->Body = "Hello,\n\nClick the following link to reset your password:\n$reset_link\n\nThis link will expire in 1 hour.\n\nHRMS System";

        $mail->send();
        echo json_encode(['status' => 'success', 'message' => 'Password reset email sent! Check your inbox.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Mailer Error: ' . $mail->ErrorInfo]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>

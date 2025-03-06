<?php
session_start();
include('includes/config.php');
require 'vendor/autoload.php'; // Required if using Composer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enable error logging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.log');
error_reporting(E_ALL);

$max_attempts = 5;
$lockout_time = 15 * 60;
$otp_validity = 5 * 60; // OTP expires in 5 minutes

// Function to generate OTP
function generateOTP() {
    return rand(100000, 999999); // 6-digit OTP
}

function logAudit($email) {
    global $conn;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $action = 'Successful Login';
    $stmt = $conn->prepare("INSERT INTO audit_logs (email_id, action, timestamp, ip_address, user_agent) VALUES (?, ?, NOW(), ?, ?)");
    $stmt->bind_param("ssss", $email, $action, $ip_address, $user_agent);
    $stmt->execute();
    $stmt->close();
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

// Function to handle user login
// Function to handle user login
function login($email, $password) {
    global $conn, $max_attempts, $lockout_time, $otp_validity;

    // Secure Query
    $stmt = $conn->prepare("SELECT * FROM tblemployees WHERE email_id = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Check for Lockout
        if (!empty($user['lockout_time']) && $user['lockout_time'] !== '0000-00-00 00:00:00') {
            $lockout_duration = time() - strtotime($user['lockout_time']);
            if ($lockout_duration < $lockout_time) {
                $remaining_time = ceil(($lockout_time - $lockout_duration) / 60);
                return ['status' => 'error', 'message' => "Your account is locked. Try again in $remaining_time minute(s) or contact admin."];
            } else {
                // Reset lockout when duration expires
                $stmt = $conn->prepare("UPDATE tblemployees SET failed_attempts = 0, lockout_time = NULL WHERE email_id = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
            }
        }

        // ✅ Use password_verify() instead of md5()
        if (password_verify($password, $user['password'])) {
            // Reset failed attempts
            $stmt = $conn->prepare("UPDATE tblemployees SET failed_attempts = 0, lockout_time = NULL WHERE email_id = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();

            // Generate OTP & Set Expiry
            $otp = generateOTP();
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_email'] = $email;
            $_SESSION['otp_expiry'] = time() + $otp_validity;

            // Store department in session
            $stmt = $conn->prepare("SELECT department FROM tblemployees WHERE email_id = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            $departments = [];
            while ($row = $result->fetch_assoc()) {
                $departments[] = $row['department'];
            }
            $_SESSION['department'] = $departments;

            $stmt->close();

            // Send OTP using PHPMailer
            if (!sendOTP($email, $otp)) {
                return ['status' => 'error', 'message' => 'Failed to send OTP. Please try again.'];
            }

            logAudit($email);

            return [
                'status' => 'otp_required',
                'message' => 'OTP sent to your email. Please enter it to continue.',
                'email' => $email
            ];
        } else {
            // Handle Failed Attempt
            $failed_attempts = $user['failed_attempts'] + 1;

            if ($failed_attempts >= $max_attempts) {
                $stmt = $conn->prepare("UPDATE tblemployees SET failed_attempts = ?, lockout_time = NOW() WHERE email_id = ?");
                $stmt->bind_param("is", $failed_attempts, $email);
            } else {
                $stmt = $conn->prepare("UPDATE tblemployees SET failed_attempts = ? WHERE email_id = ?");
                $stmt->bind_param("is", $failed_attempts, $email);
            }
            $stmt->execute();

            $remaining_attempts = max(0, $max_attempts - $failed_attempts);
            return ['status' => 'error', 'message' => 'Invalid login details. You have ' . $remaining_attempts . ' attempts left.'];
        }
    }

    return ['status' => 'error', 'message' => 'Invalid login details.'];
}



// Handle login request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    try {
        $loginResponse = login($email, $password);
        header('Content-Type: application/json');
        echo json_encode($loginResponse);
    } catch (Exception $e) {
        error_log("Exception: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Internal Server Error']);
    }
    exit;
}
?>

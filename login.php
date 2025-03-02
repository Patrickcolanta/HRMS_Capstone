<?php
session_start();
include('includes/config.php');
require_once 'alerts.php';

// Enable error logging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.log'); // Ensure log file is writable
error_reporting(E_ALL);

// Security Settings
$max_attempts = 5;
$lockout_time = 15 * 60;

// Function to handle user login
function login($email, $password) {
    global $conn, $max_attempts, $lockout_time;

    $password = md5($password);
    

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
                $stmt = $conn->prepare("UPDATE tblemployees SET failed_attempts = 0, lockout_time = NULL WHERE email_id = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
            }
        }

        // Password Check
        if ($password === $user['password']) {
            // Reset failed attempts
            $stmt = $conn->prepare("UPDATE tblemployees SET failed_attempts = 0, lockout_time = NULL WHERE email_id = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            return checkAndSetSession($user);
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
            $attempt_msg = ($remaining_attempts > 0) ? " You have $remaining_attempts attempts left." : " Your account is now locked.";

            return ['status' => 'error', 'message' => 'Invalid login details.' . $attempt_msg];
        }
    }

    return ['status' => 'error', 'message' => 'Invalid login details.'];
}

// Function to set session variables
function checkAndSetSession($userRecord) {
    if ($userRecord['lock_unlock'] == "true") {
        return ['status' => 'error', 'message' => 'Your account is locked. Please contact admin.'];
    }

    $_SESSION['slogin'] = $userRecord['emp_id'];
    $_SESSION['srole'] = $userRecord['role'];
    $_SESSION['semail'] = $userRecord['email_id'];
    $_SESSION['sstaff_id'] = $userRecord['staff_id'];
    $_SESSION['sfirstname'] = $userRecord['first_name'];
    $_SESSION['slastname'] = $userRecord['last_name'];
    $_SESSION['smiddlename'] = $userRecord['middle_name'];
    $_SESSION['scontact'] = $userRecord['phone_number'];
    $_SESSION['sdesignation'] = $userRecord['designation'];
    $_SESSION['is_supervisor'] = $userRecord['is_supervisor'];
    $_SESSION['simageurl'] = $userRecord['image_path'];
    $_SESSION['last_activity'] = time();

    $passwordReset = $userRecord['password_reset'];

    switch ($userRecord['role']) {
        case 'Admin':
        case 'Manager':
        case 'Staff':
            $_SESSION['department'] = $userRecord['department'];
            return [
                'status' => 'success',
                'message' => 'Successfully logged in',
                'role' => strtolower($userRecord['role']),
                'password_reset' => $passwordReset
            ];
        default:
            return ['status' => 'error', 'message' => 'Invalid user type'];
    }
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

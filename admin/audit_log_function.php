<?php
session_start();
include(__DIR__ . '/../includes/config.php');

header('Content-Type: application/json');

if (!isset($_SESSION['srole']) || $_SESSION['srole'] !== 'Admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

// Handle different requests
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["archive_log"])) {
        archiveLog($conn);
    } elseif (isset($_POST["restore_log"])) {
        restoreLog($conn);
    } elseif (isset($_POST["failed_login"])) {
        logFailedLogin($conn, $_POST["email"]);
    } elseif (isset($_POST["account_locked"])) {
        logAccountLockout($conn, $_POST["email"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit();
}

// ✅ Function to log user actions (successful login, logouts, other actions)
function logAction($conn, $email, $action) {
    if (!$conn) {
        error_log("Database connection is missing in logAction function.");
        return;
    }

    // Fetch employee details (emp_id, staff_id, role)
    $stmt = $conn->prepare("SELECT emp_id, staff_id, role FROM tblemployees WHERE email_id = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        error_log("User not found for logging action: " . $email);
        return;
    }

    $emp_id = $user['emp_id'];
    $staff_id = $user['staff_id'];
    $role = $user['role'];

    $ip_address = getUserIP();
    $user_agent = getUserAgent();
    $log_hash = hash('sha256', $email . $action . $ip_address . time());

    // Insert into audit_logs
    $stmt = $conn->prepare("INSERT INTO audit_logs (emp_id, staff_id, email_id, action, status, severity_level, timestamp, ip_address, user_agent, log_hash) 
                            VALUES (?, ?, ?, ?, 'Success', 1, NOW(), ?, ?, ?)");
    $stmt->bind_param("issssss", $emp_id, $staff_id, $email, $action, $ip_address, $user_agent, $log_hash);

    if (!$stmt->execute()) {
        error_log("Failed to log action: " . $stmt->error);
    }
    
    $stmt->close();
}

// ✅ Function to log failed login attempts
function logFailedLogin($conn, $email) {
    logAction($conn, $email, "Failed login attempt");
    echo json_encode(["status" => "success", "message" => "Failed login recorded"]);
    exit();
}

// ✅ Function to log when an account gets locked due to too many failed attempts
function logAccountLockout($conn, $email) {
    logAction($conn, $email, "Account locked due to multiple failed attempts");
    echo json_encode(["status" => "success", "message" => "Account lockout recorded"]);
    exit();
}

// ✅ Function to archive logs
function archiveLog($conn) {
    if (!isset($_POST["log_id"]) || !is_numeric($_POST["log_id"])) {
        echo json_encode(["status" => "error", "message" => "Invalid log ID"]);
        exit();
    }

    $log_id = intval($_POST["log_id"]);
    $stmt = $conn->prepare("UPDATE audit_logs SET is_archived = 1 WHERE id = ?");
    
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("i", $log_id);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Log archived successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to archive log."]);
    }

    $stmt->close();
    exit();
}

// ✅ Function to restore logs
function restoreLog($conn) {
    if (!isset($_POST["log_id"]) || !is_numeric($_POST["log_id"])) {
        echo json_encode(["status" => "error", "message" => "Invalid log ID"]);
        exit();
    }

    $log_id = intval($_POST["log_id"]);
    $stmt = $conn->prepare("UPDATE audit_logs SET is_archived = 0 WHERE id = ?");
    
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("i", $log_id);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Log restored successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to restore log."]);
    }

    $stmt->close();
    exit();
}

// ✅ Function to get real IP address
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP);
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return filter_var(trim($ip_list[0]), FILTER_VALIDATE_IP);
    } else {
        return filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
    }
}

// ✅ Function to get User-Agent
function getUserAgent() {
    return isset($_SERVER['HTTP_USER_AGENT']) ? htmlspecialchars($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, 'UTF-8') : 'Unknown';
}
?>

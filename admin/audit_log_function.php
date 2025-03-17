<?php
session_start();
include(__DIR__ . '/../includes/config.php');

header('Content-Type: application/json');

if (!isset($_SESSION['srole']) || $_SESSION['srole'] !== 'Admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

// Check request method
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["archive_log"])) {
        archiveLog($conn);
    } elseif (isset($_POST["restore_log"])) {
        restoreLog($conn);
    } elseif (isset($_POST["failed_login"])) {
        logFailedLogin($conn, $_POST["email"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit();
}

// Function to archive logs
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

// Function to restore logs
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

// Function to log user actions
function logAction($conn, $email, $action) {
    if (!$conn) {
        error_log("Database connection is missing in logAction function.");
        return;
    }

    $ip_address = getUserIP(); 
    $user_agent = getUserAgent(); 

    $stmt = $conn->prepare("INSERT INTO audit_logs (email_id, action, ip_address, user_agent, timestamp) VALUES (?, ?, ?, ?, NOW())");
    if (!$stmt) {
        error_log("Failed to prepare statement in logAction: " . $conn->error);
        return;
    }

    $stmt->bind_param("ssss", $email, $action, $ip_address, $user_agent);
    
    if (!$stmt->execute()) {
        error_log("Failed to log action: " . $stmt->error);
    }
    
    $stmt->close();
}

// Function to log failed login attempts
function logFailedLogin($conn, $email) {
    logAction($conn, $email, "Failed login attempt");
    echo json_encode(["status" => "success", "message" => "Failed login recorded"]);
    exit();
}

// Function to get real IP address
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

// Function to get User-Agent
function getUserAgent() {
    return isset($_SERVER['HTTP_USER_AGENT']) ? htmlspecialchars($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, 'UTF-8') : 'Unknown';
}
?>

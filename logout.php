<?php
session_start();
include('includes/config.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$email = isset($_SESSION['semail']) ? $_SESSION['semail'] : null;

if ($email) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
    $action = 'Successful Logout';

    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

  
    $stmt = $conn->prepare("INSERT INTO audit_logs (email_id, action, timestamp, ip_address, user_agent) VALUES (?, ?, NOW(), ?, ?)");
    
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssss", $email, $action, $ip_address, $user_agent);

    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    } else {
        echo "Audit log inserted successfully!";
    }

    $stmt->close();
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login page
header("Location: index.php");
exit;
?>

<?php
session_start();
include('includes/config.php'); // Database connection

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['token'], $_POST['new_password'], $_POST['confirm_password'])) {
    $token = trim($_POST['token']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // ✅ Check if passwords match
    if ($new_password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
        exit();
    }

    // ✅ Validate password strength
    if (strlen($new_password) < 16 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password) || !preg_match('/[@$!%*?&]/', $new_password)) {
        echo json_encode(['status' => 'error', 'message' => 'Password must be at least 16 characters long, include a number, an uppercase letter, and a special character.']);
        exit();
    }

    // ✅ Check if token exists and is valid
    $stmt = $conn->prepare("SELECT user_id, expires_at FROM password_resets WHERE token = ?");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'SQL Error: ' . $conn->error]);
        exit();
    }
    
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token.']);
        exit();
    }
    
    $row = $result->fetch_assoc();
    $user_id = $row['user_id'];
    $expiry_time = strtotime($row['expires_at']);
    
    if ($expiry_time < time()) {
        echo json_encode(['status' => 'error', 'message' => 'Token has expired.']);
        exit();
    }
    
    // ✅ Hash the new password securely
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    // ✅ Update the user's password
    $stmt = $conn->prepare("UPDATE tblemployees SET password = ? WHERE emp_id = ?");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'SQL Error: ' . $conn->error]);
        exit();
    }
    
    $stmt->bind_param("si", $hashed_password, $user_id);
    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Password update failed: ' . $stmt->error]);
        exit();
    }

    // ✅ Delete used token from database
    $stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();

    echo json_encode(['status' => 'success', 'message' => 'Password reset successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>

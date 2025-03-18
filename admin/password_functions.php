<?php
date_default_timezone_set('Asia/Manila');
include('../includes/config.php');
include('../includes/session.php');

function changePassword($email, $oldPassword, $newPassword) {
    global $conn;

    if (empty($oldPassword) || empty($newPassword)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all fields']);
        exit;
    }

    // Fetch current hashed password
    $stmt = $conn->prepare("SELECT password FROM tblemployees WHERE email_id = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $currentPasswordHash = $row['password'];

        // Verify the old password
        if (!password_verify($oldPassword, $currentPasswordHash)) {
            echo json_encode(['status' => 'error', 'message' => 'Old password is incorrect']);
            exit;
        }

        // Check new password complexity
        if (!preg_match('/^(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{10,}$/', $newPassword)) {
            echo json_encode(['status' => 'error', 'message' => 'Password must be at least 10 characters long, contain at least one uppercase letter, and one special character.']);
            exit;
        }

        // Hash the new password
        $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Check if the new password is different from the old password
        if (password_verify($newPassword, $currentPasswordHash)) {
            echo json_encode(['status' => 'error', 'message' => 'New password cannot be the same as the old password']);
            exit;
        }

        // Update the password in the database
        $stmt = $conn->prepare("UPDATE tblemployees SET password = ? WHERE email_id = ?");
        $stmt->bind_param("ss", $hashedNewPassword, $email);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Password changed successfully']);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update password']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Email not found']);
        exit;
    }
}

if ($_POST['action'] === 'change_password' && isset($_SESSION['semail'])) {
    changePassword($_SESSION['semail'], $_POST['old_password'], $_POST['new_password']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}
?>

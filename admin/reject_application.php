<?php
session_start();
include('../includes/config.php'); // Ensure this file sets up $conn

header('Content-Type: application/json');

if (!isset($_SESSION['slogin']) || !isset($_SESSION['srole'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$userRole = $_SESSION['srole'];
if ($userRole !== 'Manager' && $userRole !== 'Admin') {
    echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'], $_POST['reason'])) {
    $application_id = mysqli_real_escape_string($conn, $_POST['application_id']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    
    $updateSql = "UPDATE applications SET status = 'Rejected', rejection_reason = '$reason' WHERE id = '$application_id'";
    if (mysqli_query($conn, $updateSql)) {
        echo json_encode(['status' => 'success', 'message' => 'Application rejected successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to reject application']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}

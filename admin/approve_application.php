<?php
session_start();
include('../includes/config.php'); // Database connection

// Check if the user is logged in
if (!isset($_SESSION['slogin']) || !isset($_SESSION['srole'])) {
    header('Location: ../index.php');
    exit();
}

// Check if the user has the role of Manager or Admin
$userRole = $_SESSION['srole'];
if ($userRole !== 'Manager' && $userRole !== 'Admin') {
    echo "<script>alert('Unauthorized access!'); window.location.href='applications.php';</script>";
    exit();
}

// Check if an application ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Invalid application ID!'); window.location.href='applications.php';</script>";
    exit();
}

$app_id = intval($_GET['id']); // Sanitize input

// Update the application status to "Approved"
$sql = "UPDATE applications SET status = 'Approved' WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $app_id);

if (mysqli_stmt_execute($stmt)) {
    echo "<script>alert('Application approved successfully!'); window.location.href='applications.php';</script>";
} else {
    echo "<script>alert('Error approving application!'); window.location.href='applications.php';</script>";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

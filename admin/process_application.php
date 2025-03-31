<?php
session_start();
include 'config.php';

// 🛑 Check if the user is logged in and authorized
if (!isset($_SESSION['slogin']) || !isset($_SESSION['srole'])) {
    header("Location: index.php");
    exit();
}

// 🛑 Only Managers and Admins can approve/reject applications
if ($_SESSION['srole'] !== 'HR' && $_SESSION['srole'] !== 'Admin') {
    die("Unauthorized access!");
}

// ✅ Validate and sanitize `id`
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid application ID!");
}
$id = intval($_GET['id']);  // Convert ID to an integer (prevents SQL injection)

// ✅ Validate `action`
if (!isset($_GET['action']) || !in_array($_GET['action'], ['approve', 'reject'])) {
    die("Invalid action!");
}

$status = ($_GET['action'] == 'approve') ? 'Approved' : 'Rejected';

// ✅ Use prepared statement to prevent SQL injection
$sql = "UPDATE job_applications SET status = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "si", $status, $id);

if (mysqli_stmt_execute($stmt)) {
    // Redirect back with success message
    header("Location: applications.php?message=Application $status successfully!");
    exit();
} else {
    die("Error updating application: " . mysqli_error($conn));
}

// Close the statement
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

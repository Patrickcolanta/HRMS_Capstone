<?php
session_start(); // Ensure session is started
include('../includes/config.php'); // Ensure database connection

// Check if the user is logged in and has the right role
if (!isset($_SESSION['slogin']) || !isset($_SESSION['srole']) || 
    ($_SESSION['srole'] !== 'Manager' && $_SESSION['srole'] !== 'Admin')) {
    header('Location: ../index.php');
    exit();
}

// Ensure a job ID is provided
if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: job_listings.php");
    exit();
}

$job_id = intval($_GET['id']); // Sanitize job ID

// Ensure table name matches your database structure
$query = "DELETE FROM job_listings WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $job_id);
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Job deleted successfully!'); window.location='job_listings.php';</script>";
    } else {
        echo "<script>alert('Error deleting job.'); window.location='job_listings.php';</script>";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "<script>alert('Database error: Unable to prepare statement.'); window.location='job_listings.php';</script>";
}

mysqli_close($conn);
?>

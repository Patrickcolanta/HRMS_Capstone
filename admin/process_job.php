<?php
session_start();
include('../includes/config.php'); // Database connection

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_title = mysqli_real_escape_string($conn, $_POST['job_title']);
    $job_description = mysqli_real_escape_string($conn, $_POST['job_description']);
    $department_id = intval($_POST['department_id']); // Ensure it's an integer
    $job_type = mysqli_real_escape_string($conn, $_POST['job_type']);

    // Validate inputs
    if (empty($job_title) || empty($job_description) || empty($department_id) || empty($job_type)) {
        echo "<script>alert('All fields are required!'); window.location.href='job_listings.php';</script>";
        exit();
    }

    // Insert into job_listings table
    $sql = "INSERT INTO job_listings (job_title, job_description, department_id, job_type) 
            VALUES ('$job_title', '$job_description', '$department_id', '$job_type')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
            alert('Job successfully posted!');
            window.location.href='job_listings.php';
        </script>";
    } else {
        echo "<script>
            alert('Error: " . mysqli_error($conn) . "');
            window.location.href='job_listings.php';
        </script>";
    }
}
?>

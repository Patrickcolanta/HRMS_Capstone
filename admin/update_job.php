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
    header('Location: ../index.php');
    exit();
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_id = intval($_POST['job_id']);
    $job_title = trim($_POST['job_title']);
    $job_description = trim($_POST['job_description']);
    $department_id = intval($_POST['department_id']);
    $job_type = trim($_POST['job_type']);

    // Validate inputs
    if (empty($job_id) || empty($job_title) || empty($job_description) || empty($department_id) || empty($job_type)) {
        echo "<script>
            alert('All fields are required!');
            window.location.href='edit_job.php?id=$job_id';
        </script>";
        exit();
    }

    // Prepare and bind statement to prevent SQL injection
    $stmt = $conn->prepare("UPDATE job_listings 
                            SET job_title = ?, job_description = ?, department_id = ?, job_type = ? 
                            WHERE id = ?");
    $stmt->bind_param("ssisi", $job_title, $job_description, $department_id, $job_type, $job_id);

    // Execute update and check for errors
    if ($stmt->execute()) {
        echo "<script>
            alert('Job updated successfully!');
            window.location.href='job_listings.php';
        </script>";
    } else {
        echo "<script>
            alert('Error updating job: " . addslashes($stmt->error) . "');
            window.location.href='edit_job.php?id=$job_id';
        </script>";
    }

    $stmt->close();
}
?>

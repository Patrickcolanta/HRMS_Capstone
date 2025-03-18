<?php
include('../includes/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header("Content-Type: application/json"); // Ensure JSON response
    
    // Validate required fields
    if (empty($_POST['job_title']) || empty($_POST['job_type']) || empty($_POST['vacancy']) || empty($_POST['location']) || empty($_POST['description'])) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }

    $job_title = mysqli_real_escape_string($conn, $_POST['job_title']);
    $job_type = mysqli_real_escape_string($conn, $_POST['job_type']);
    $vacancy = intval($_POST['vacancy']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $status = "Active"; // Default status

    $sql = "INSERT INTO job_listings (job_title, job_type, location, vacancy, description, status, created_at) 
            VALUES ('$job_title', '$job_type', '$location', '$vacancy', '$description', '$status', NOW())";

    if (mysqli_query($conn, $sql)) {
        echo json_encode(["status" => "success", "message" => "Job posted successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . mysqli_error($conn)]);
    }

    mysqli_close($conn);
}
?>
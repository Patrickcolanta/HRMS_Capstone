<?php
include('../includes/config.php');
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_id = intval($_POST['job_id']);
    $job_title = trim($_POST['job_title']);
    $job_type = trim($_POST['job_type']);
    $vacancy = intval($_POST['vacancy']);
    $salary = trim($_POST['salary']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $status = trim($_POST['status']); // Get status from POST request

    // Validate required fields
    if (empty($job_title) || empty($job_type) || empty($vacancy) || empty($description) || empty($status)) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit();
    }

    // Ensure status is either 'Active' or 'Inactive' for security
    if ($status !== "Active" && $status !== "Inactive") {
        echo json_encode(["status" => "error", "message" => "Invalid status value."]);
        exit();
    }

    // Ensure salary is a valid number (float)
    if (!is_numeric($salary)) {
        echo json_encode(["status" => "error", "message" => "Salary must be a valid number."]);
        exit();
    }

    // Use prepared statement to prevent SQL injection
    $sql = "UPDATE job_listings SET job_title = ?, job_type = ?, vacancy = ?, salary = ?, location = ?, description = ?, status = ? WHERE id = ?";
    
    // Bind the parameters (note the types for each field)
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssissssi", $job_title, $job_type, $vacancy, $salary, $location, $description, $status, $job_id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["status" => "success", "message" => "Job updated successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error updating job: " . mysqli_error($conn)]);
    }

    mysqli_stmt_close($stmt);
}
?>

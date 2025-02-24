<?php
include('../includes/header.php'); 
include('../includes/config.php'); 

// Session validation
if (!isset($_SESSION['slogin']) || ($_SESSION['srole'] !== 'Manager' && $_SESSION['srole'] !== 'Admin')) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $query = "INSERT INTO jobs (title, department, location, status) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssss", $title, $department, $location, $status);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Job added successfully!'); window.location='job_listing.php';</script>";
    } else {
        echo "<script>alert('Error adding job.');</script>";
    }
}
?>

<div class="container">
    <h2>Add New Job</h2>
    <form method="POST">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Department</label>
            <input type="text" name="department" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Location</label>
            <input type="text" name="location" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="Open">Open</option>
                <option value="Closed">Closed</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Job</button>
        <a href="job_listing.php" class="btn btn-secondary">Back</a>
    </form>
</div>

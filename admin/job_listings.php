<?php 
session_start();
include('../includes/header.php'); 
include('../includes/config.php'); // Ensure this file sets up $conn

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

// Handle department addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_department'])) {
    $department_name = mysqli_real_escape_string($conn, $_POST['department_name']);
    
    if (!empty($department_name)) {
        $deptQuery = "INSERT INTO department (name) VALUES ('$department_name')";
        if (mysqli_query($conn, $deptQuery)) {
            echo "<script>
                Swal.fire({
                    title: 'Success!',
                    text: 'The department has been added successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'job_listings.php';
                });
                </script>";
        } else {
            echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to add department: " . mysqli_error($conn) . "',
                    icon: 'error',
                    confirmButtonText: 'Try Again'
                });
                </script>";
        }
    }
}


// Fetch job listings from the database
$sql = "SELECT j.id, j.job_title, d.name AS department, j.job_type, j.created_at 
        FROM job_listings j 
        LEFT JOIN department d ON j.department_id = d.id
        ORDER BY j.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<body>
   <!-- Pre-loader start -->
   <?php include('../includes/loader.php')?>
    <!-- Pre-loader end -->
<div id="pcoded" class="pcoded">
    <div class="pcoded-overlay-box"></div>
    <div class="pcoded-container navbar-wrapper">

        <?php include('../includes/topbar.php'); ?>

        <div class="pcoded-main-container">
            <div class="pcoded-wrapper">
                <?php $page_name = "job_listings"; ?>
                
                <?php include('../includes/sidebar.php'); ?>

                <div class="pcoded-content">
                    <div class="pcoded-inner-content">
                        <div class="main-body">
                            <div class="page-wrapper">
                                <div class="page-header">
                                    <div class="row align-items-end">
                                        <div class="col-lg-8">
                                            <div class="page-header-title">
                                                <div class="d-inline">
                                                    <h4>Job Listings</h4>
                                                    <span>Manage available job positions</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Page-body start -->
                                <div class="page-body">
                                    <div class="row">
                                        <div class="col-sm-12">

                                            <!-- Add Department Form -->
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Add New Department</h5>
                                                </div>
                                                <div class="card-block">
                                                    <form method="post">
                                                        <div class="form-group">
                                                            <label>Department Name</label>
                                                            <input type="text" name="department_name" class="form-control" required>
                                                        </div>
                                                        <button type="submit" name="add_department" class="btn btn-success">Add Department</button>
                                                    </form>
                                                </div>
                                            </div>

                                            <!-- Job Listing Form -->
                                            <div class="card">
                                                <div class="card-block">
                                                    <h4 class="text-center">Create New Job Listing</h4>
                                                    <form method="post" action="process_job.php">
                                                        <div class="j-content">
                                                            <div class="j-unit">
                                                                <label>Job Title</label>
                                                                <input type="text" name="job_title" class="form-control" required>
                                                            </div>

                                                            <div class="j-unit">
                                                                <label>Job Description</label>
                                                                <textarea name="job_description" class="form-control" required></textarea>
                                                            </div>

                                                            <div class="j-unit">
                                                                <label>Department</label>
                                                                <select name="department_id" class="form-control" required>
                                                                    <option value="" disabled selected>Select Department</option>
                                                                    <?php
                                                                    $deptQuery = "SELECT * FROM department";
                                                                    $deptResult = mysqli_query($conn, $deptQuery);
                                                                    while ($dept = mysqli_fetch_assoc($deptResult)) {
                                                                        echo '<option value="'.$dept['id'].'">'.$dept['name'].'</option>';
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>

                                                            <div class="j-unit">
                                                                <label>Job Type</label>
                                                                <select name="job_type" class="form-control" required>
                                                                    <option value="Full-Time">Full-Time</option>
                                                                    <option value="Part-Time">Part-Time</option>
                                                                    <option value="Contract">Contract</option>
                                                                </select>
                                                            </div>

                                                            <div class="j-footer">
                                                                <button type="submit" class="btn btn-primary">Post Job</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                            <!-- Job Listings Table -->
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Available Jobs</h5>
                                                </div>
                                                <div class="card-block">
                                                    <div class="table-responsive">
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Job Title</th>
                                                                    <th>Department</th>
                                                                    <th>Job Type</th>
                                                                    <th>Date Posted</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php
                                                                if ($result && mysqli_num_rows($result) > 0) {
                                                                    $count = 1;
                                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                                        echo "<tr>
                                                                            <td>{$count}</td>
                                                                            <td>{$row['job_title']}</td>
                                                                            <td>{$row['department']}</td>
                                                                            <td>{$row['job_type']}</td>
                                                                            <td>{$row['created_at']}</td>
                                                                            <td>
                                                                                <a href='edit_job.php?id={$row['id']}' class='btn btn-warning btn-sm'>Edit</a>
                                                                                <button class='btn btn-danger btn-sm delete-btn' data-id='{$row['id']}'>Delete</button>
                                                                            </td>
                                                                        </tr>";
                                                                        $count++;
                                                                    }
                                                                } else {
                                                                    echo "<tr><td colspan='6' class='text-center'>No job listings found</td></tr>";
                                                                }
                                                                ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Job Listings Table End -->
                                        </div>
                                    </div>
                                </div>
                                <!-- Page-body end -->
                            </div>
                        </div>
                        <!-- Main-body end -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

   <?php include('../includes/scripts.php')?>

<!-- SweetAlert2 Delete Confirmation -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".delete-btn").forEach(button => {
        button.addEventListener("click", function() {
            let jobId = this.getAttribute("data-id");

            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to undo this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("delete_job.php?id=" + jobId, { method: "GET" })
                    .then(() => location.reload());
                }
            });
        });
    });
});
</script>

</body>
</html>

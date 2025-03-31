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
if ($userRole !== 'HR' && $userRole !== 'Admin') {
    header('Location: ../index.php');
    exit();
}

// Get job ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Invalid Job ID!'); window.location.href='job_listings.php';</script>";
    exit();
}

$job_id = intval($_GET['id']); // Ensure it's an integer

// Fetch job details
$sql = "SELECT * FROM job_listings WHERE id = $job_id";
$result = mysqli_query($conn, $sql);
$job = mysqli_fetch_assoc($result);

if (!$job) {
    echo "<script>alert('Job not found!'); window.location.href='job_listings.php';</script>";
    exit();
}

// Fetch departments
$deptQuery = "SELECT * FROM department";
$deptResult = mysqli_query($conn, $deptQuery);
?>

<body>
<div id="pcoded" class="pcoded">
    <div class="pcoded-overlay-box"></div>
    <div class="pcoded-container navbar-wrapper">

        <?php include('../includes/topbar.php'); ?>

        <div class="pcoded-main-container">
            <div class="pcoded-wrapper">
                <?php $page_name = "edit_job"; ?>
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
                                                    <h4>Edit Job Listing</h4>
                                                    <span>Modify job details</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="page-body">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="card">
                                                <div class="card-block">
                                                    <div class="j-wrapper j-wrapper-640">
                                                        <h4 class="text-center">Edit Job Details</h4>
                                                        <form method="post" action="update_job.php" class="j-pro">
                                                            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">

                                                            <div class="j-content">
                                                                <div class="j-unit">
                                                                    <div class="j-input">
                                                                        <label class="j-hint">Job Title</label>
                                                                        <input type="text" name="job_title" value="<?= $job['job_title'] ?>" required>
                                                                    </div>
                                                                </div>

                                                                <div class="j-unit">
                                                                    <div class="j-input">
                                                                        <label class="j-hint">Job Description</label>
                                                                        <textarea name="job_description" required><?= $job['job_description'] ?></textarea>
                                                                    </div>
                                                                </div>

                                                                <div class="j-unit">
                                                                    <div class="j-input">
                                                                        <label class="j-hint">Department</label>
                                                                        <select name="department_id" required>
                                                                            <option value="" disabled>Select Department</option>
                                                                            <?php while ($dept = mysqli_fetch_assoc($deptResult)) : ?>
                                                                                <option value="<?= $dept['id'] ?>" <?= ($job['department_id'] == $dept['id']) ? 'selected' : '' ?>>
                                                                                    <?= $dept['name'] ?>
                                                                                </option>
                                                                            <?php endwhile; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="j-unit">
                                                                    <div class="j-input">
                                                                        <label class="j-hint">Job Type</label>
                                                                        <select name="job_type" required>
                                                                            <option value="Full-Time" <?= ($job['job_type'] == 'Full-Time') ? 'selected' : '' ?>>Full-Time</option>
                                                                            <option value="Part-Time" <?= ($job['job_type'] == 'Part-Time') ? 'selected' : '' ?>>Part-Time</option>
                                                                            <option value="Contract" <?= ($job['job_type'] == 'Contract') ? 'selected' : '' ?>>Contract</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="j-footer">
                                                                    <button type="submit" class="btn btn-primary">Update Job</button>
                                                                    <a href="job_listings.php" class="btn btn-default">Cancel</a>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div> <!-- Page-body end -->
                            </div>
                        </div> <!-- Main-body end -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<?php 
include('../includes/header.php'); 
include('../includes/config.php'); 

// Start session and check user authentication
if (!isset($_SESSION['slogin']) || !isset($_SESSION['srole'])) {
    header('Location: ../index.php');
    exit();
}

// Restrict access to Managers and Admins
$userRole = $_SESSION['srole'];
if ($userRole !== 'Manager' && $userRole !== 'Admin') {
    header('Location: ../index.php');
    exit();
}

// Fetch approved job applications
$query = "SELECT a.applicant_name, j.title, a.status 
          FROM applications a 
          JOIN jobs j ON a.job_id = j.id 
          WHERE a.status = 'Approved'";
$result = mysqli_query($conn, $query);
?>


<body>
<div id="pcoded" class="pcoded">
    <div class="pcoded-overlay-box"></div>
    <div class="pcoded-container navbar-wrapper">

        <?php include('../includes/topbar.php'); ?>

        <div class="pcoded-main-container">
            <div class="pcoded-wrapper">
                <?php $page_name = "hiring_process"; ?>
                <?php include('../includes/sidebar.php'); ?>

                <div class="pcoded-content">
                    <div class="pcoded-inner-content">
                        <!-- Main-body start -->
                        <div class="main-body">
                            <div class="page-wrapper">
                                <!-- Page-header start -->
                                <div class="page-header">
                                    <div class="row align-items-end">
                                        <div class="col-lg-8">
                                            <div class="page-header-title">
                                                <div class="d-inline">
                                                    <h4>Hiring Process</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Page-header end -->

                                <!-- Page-body start -->
                                <div class="page-body">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Approved Applications</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Applicant</th>
                                                                    <th>Job Title</th>
                                                                    <th>Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                                                    <tr>
                                                                        <td><?php echo htmlspecialchars($row['applicant_name']); ?></td>
                                                                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                                                                        <td>
                                                                            <span class="badge badge-success"><?php echo $row['status']; ?></span>
                                                                        </td>
                                                                    </tr>
                                                                <?php endwhile; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Page-body end -->
                                <?php include('../includes/scripts.php')?>
                            </div>
                        </div>
                        <!-- Main-body end -->
                    </div>
                </div>
                <!-- Content End -->
            </div>
        </div>
    </div>
</div>
</body>
</html>

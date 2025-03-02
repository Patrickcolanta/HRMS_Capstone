<?php 
// Start session only if not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('../includes/header.php'); 
include('../includes/config.php'); 

// Check user authentication
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

// Fetch job applications
$query = "SELECT a.id, a.applicant_name, j.title, a.status 
          FROM applications a 
          JOIN jobs j ON a.job_id = j.id 
          WHERE a.status IN ('Approved', 'Interview Scheduled', 'Hired', 'Rejected')";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Failed: " . mysqli_error($conn)); // Debugging: Check for query errors
}
?>

<body>
<div id="pcoded" class="pcoded">
    <div class="pcoded-container navbar-wrapper">
        <?php include('../includes/topbar.php'); ?>
        <div class="pcoded-main-container">
            <div class="pcoded-wrapper">
                <?php $page_name = "hiring_process"; ?>
                <?php include('../includes/sidebar.php'); ?>
                <div class="pcoded-content">
                    <div class="pcoded-inner-content">
                        <div class="main-body">
                            <div class="page-wrapper">
                                <div class="page-header">
                                    <div class="row align-items-end">
                                        <div class="col-lg-8">
                                            <div class="page-header-title">
                                                <h4>Hiring Process</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>

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
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                                                    <tr id="row-<?php echo $row['id']; ?>">
                                                                        <td><?php echo htmlspecialchars($row['applicant_name']); ?></td>
                                                                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                                                                        <td id="status-<?php echo $row['id']; ?>">
                                                                            <span class="badge badge-info"><?php echo htmlspecialchars($row['status']); ?></span>
                                                                        </td>
                                                                        <td>
                                                                            <select class="form-control status-select" id="status-select-<?php echo $row['id']; ?>">
                                                                                <option value="Approved" <?php if ($row['status'] == "Approved") echo "selected"; ?>>Approved</option>
                                                                                <option value="Interview Scheduled" <?php if ($row['status'] == "Interview Scheduled") echo "selected"; ?>>Interview Scheduled</option>
                                                                                <option value="Hired" <?php if ($row['status'] == "Hired") echo "selected"; ?>>Hired</option>
                                                                                <option value="Rejected" <?php if ($row['status'] == "Rejected") echo "selected"; ?>>Rejected</option>
                                                                            </select>
                                                                            <button class="btn btn-primary btn-sm update-status-btn" data-id="<?php echo $row['id']; ?>">Update</button>
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
                                <?php include('../includes/scripts.php') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".update-status-btn").forEach(button => {
        button.addEventListener("click", function () {
            let applicationId = this.getAttribute("data-id");
            let newStatus = document.getElementById(`status-select-${applicationId}`).value;

            fetch('update_application_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `application_id=${applicationId}&new_status=${encodeURIComponent(newStatus)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire("Success", "Status updated successfully!", "success");
                    document.getElementById(`status-${applicationId}`).innerHTML = `<span class='badge badge-info'>${data.new_status}</span>`;
                } else {
                    Swal.fire("Error", data.message, "error");
                }
            })
            .catch(error => {
                Swal.fire("Error", "Something went wrong!", "error");
            });
        });
    });
});
</script>

</body>
</html>

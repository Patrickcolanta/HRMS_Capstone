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

// Fetch applications from the database
$sql = "SELECT a.id, a.applicant_name, a.email, a.phone, a.address, a.cover_letter, 
               j.job_title, a.resume, a.status, a.applied_at
        FROM applications a
        JOIN job_listings j ON a.job_id = j.id
        ORDER BY a.applied_at DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Applications</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div id="pcoded" class="pcoded">
    <div class="pcoded-container navbar-wrapper">

        <?php include('../includes/topbar.php'); ?>

        <div class="pcoded-main-container">
            <div class="pcoded-wrapper">
                <?php $page_name = "applications"; ?>
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
                                                    <h4>Job Applications</h4>
                                                    <span>Review job applicants</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="page-body">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>List of Applicants</h5>
                                                </div>
                                                <div class="card-block">
                                                    <div class="table-responsive">
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Applicant Name</th>
                                                                    <th>Email</th>
                                                                    <th>Phone</th>
                                                                    <th>Address</th>
                                                                    <th>Job Title</th>
                                                                    <th>Cover Letter</th>
                                                                    <th>Resume</th>
                                                                    <th>Status</th>
                                                                    <th>Applied At</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
    <?php
    if ($result && mysqli_num_rows($result) > 0) {
        $count = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            $applicant_name = htmlspecialchars($row['applicant_name']);
            $email = htmlspecialchars($row['email']);
            $phone = htmlspecialchars($row['phone']);
            $address = htmlspecialchars($row['address']);
            $cover_letter = htmlspecialchars($row['cover_letter']);
            $job_title = htmlspecialchars($row['job_title']);
            $status = htmlspecialchars($row['status']);
            $applied_at = htmlspecialchars($row['applied_at']);

            // Corrected directory
            $resumeFile = 'uploads/' . htmlspecialchars($row['resume']);

            // Button to open the resume
            if (!empty($row['resume']) && file_exists($resumeFile)) {
                $resumeLink = "<button onclick=\"window.open('$resumeFile', '_blank')\" class='btn btn-info btn-sm'>View Resume</button>";
            } else {
                $resumeLink = "<span class='text-danger'>File not found</span>";
            }

            echo "<tr id='row-{$row['id']}'>
                <td>{$count}</td>
                <td>{$applicant_name}</td>
                <td>{$email}</td>
                <td>{$phone}</td>
                <td>{$address}</td>
                <td>{$job_title}</td>
                <td>{$cover_letter}</td>
                <td>{$resumeLink}</td>
                <td id='status-{$row['id']}'>{$status}</td>
                <td>{$applied_at}</td>
                <td>
                    <a href='approve_application.php?id={$row['id']}' class='btn btn-success btn-sm approve-btn' data-id='{$row['id']}'>Approve</a>
                    <button class='btn btn-danger btn-sm reject-btn' data-id='{$row['id']}'>Reject</button>
                </td>
            </tr>";
            $count++;
        }
    } else {
        echo "<tr><td colspan='11' class='text-center'>No applications found</td></tr>";
    }
    ?>
</tbody>

                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('../includes/scripts.php')?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".reject-btn").forEach(button => {
        button.addEventListener("click", function() {
            let applicationId = this.getAttribute("data-id");
            Swal.fire({
                title: "Reject Application",
                input: "textarea",
                inputLabel: "Rejection Reason",
                inputPlaceholder: "Enter reason here...",
                showCancelButton: true,
                confirmButtonText: "Reject",
                preConfirm: (reason) => {
                    return fetch('reject_application.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `application_id=${applicationId}&reason=${encodeURIComponent(reason)}`
                    }).then(response => response.json());
                }
            }).then(result => {
                if (result.value && result.value.status === 'success') {
                    Swal.fire("Success", result.value.message, "success");
                    
                    // Remove the row from the table
                    document.getElementById(`row-${applicationId}`).remove();
                } else {
                    Swal.fire("Error", result.value.message, "error");
                }
            });
        });
    });

    // Approve button handling
    document.querySelectorAll(".approve-btn").forEach(button => {
        button.addEventListener("click", function(event) {
            event.preventDefault();
            let applicationId = this.getAttribute("data-id");

            fetch(`approve_application.php?id=${applicationId}`, {
                method: "GET",
            })
            .then(response => response.text())
            .then(result => {
                if (result.includes("success")) {
                    Swal.fire("Success", "Application approved successfully!", "success");
                    
                    // Remove the row from the table
                    document.getElementById(`row-${applicationId}`).remove();
                } else {
                    Swal.fire("Error", "Failed to approve application.", "error");
                }
            });
        });
    });
});
</script>


</body>
</html>

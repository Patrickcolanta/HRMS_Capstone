<?php
session_start();
include('../includes/header.php');
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

// Fetch approved applications
$sql = "SELECT a.id, a.applicant_name, a.email, a.phone, a.address, a.status, j.job_title, a.applied_at
        FROM job_applications a 
        JOIN job_listings j ON a.job_id = j.id 
        WHERE a.status = 'Approved'
        ORDER BY a.applied_at DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hiring Process</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
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
                                                <div class="d-inline">
                                                    <h4>Hiring Process</h4>
                                                    <span>Manage approved applicants</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="page-body">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="card">
                                                <div class="card-header d-flex justify-content-between">
                                                    <h5>List of Approved Candidates</h5>
                                                    <div>
                                                        <label for="filterStatus"><strong>Filter by Status:</strong></label>
                                                        <select id="filterStatus" class="form-select">
                                                            <option value="all">All</option>
                                                            <option value="Pending Interview">Pending Interview</option>
                                                            <option value="Interview Scheduled">Interview Scheduled</option>
                                                            <option value="Offer Sent">Offer Sent</option>
                                                            <option value="Hired">Hired</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="card-block">
                                                    <div class="table-responsive">
                                                        <table class="table table-striped" id="applicantsTable">
                                                            <thead class="table-dark">
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Applicant Name</th>
                                                                    <th>Email</th>
                                                                    <th>Phone</th>
                                                                    <th>Job Title</th>
                                                                    <th>Status</th>
                                                                    <th>Interview Date</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                            <?php
                                                            if (mysqli_num_rows($result) > 0) {
                                                                $count = 1;
                                                                while ($row = mysqli_fetch_assoc($result)) {
                                                                    echo "<tr data-status='{$row['status']}' id='row-{$row['id']}'>
                                                                        <td>{$count}</td>
                                                                        <td>" . htmlspecialchars($row['applicant_name']) . "</td>
                                                                        <td>" . htmlspecialchars($row['email']) . "</td>
                                                                        <td>" . htmlspecialchars($row['phone']) . "</td>
                                                                        <td>" . htmlspecialchars($row['job_title']) . "</td>
                                                                        <td>
                                                                            <select class='form-select status-select' data-id='{$row['id']}'>
                                                                                <option value='Pending Interview' " . ($row['status'] == 'Pending Interview' ? 'selected' : '') . ">Pending Interview</option>
                                                                                <option value='Interview Scheduled' " . ($row['status'] == 'Interview Scheduled' ? 'selected' : '') . ">Interview Scheduled</option>
                                                                                <option value='Offer Sent' " . ($row['status'] == 'Offer Sent' ? 'selected' : '') . ">Offer Sent</option>
                                                                                <option value='Hired' " . ($row['status'] == 'Hired' ? 'selected' : '') . ">Hired</option>
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                        
                                                                        </td>
                                                                        <td>
                                                                            <button class='btn btn-primary btn-sm update-status' data-id='{$row['id']}'>Update</button>
                                                                        </td>
                                                                    </tr>";
                                                                    $count++;
                                                                }
                                                            } else {
                                                                echo "<tr><td colspan='8' class='text-center text-muted'>No approved applications</td></tr>";
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

<?php include('../includes/scripts.php'); ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Status Update Logic
    document.querySelectorAll(".update-status").forEach(button => {
        button.addEventListener("click", function() {
            let applicationId = this.getAttribute("data-id");
            let newStatus = document.querySelector(`.status-select[data-id='${applicationId}']`).value;
            let interviewDate = document.querySelector(`.interview-date[data-id='${applicationId}']`).value;

            fetch('update_hiring_process.php', {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `application_id=${applicationId}&status=${encodeURIComponent(newStatus)}&interview_date=${encodeURIComponent(interviewDate)}`
})
.then(response => response.json())
.then(data => {
    if (data.status === "success") {
        // ✅ Update the row attribute dynamically
        let row = document.getElementById(`row-${applicationId}`);
        row.setAttribute("data-status", newStatus); // Update status attribute

        Swal.fire({
            title: "Success!",
            text: "Hiring status updated successfully!",
            icon: "success",
            confirmButtonText: "OK"
        }).then(() => location.reload()); // Reload page to reflect changes
    } else {
        Swal.fire("Error", data.message, "error");
    }
})
.catch(error => {
    Swal.fire("Error", "Something went wrong!", "error");
    console.error("Fetch error:", error);
});

        });
    });

    // Filter Status Logic
    document.getElementById("filterStatus").addEventListener("change", function() {
        let selectedStatus = this.value;
        let rows = document.querySelectorAll("#applicantsTable tbody tr");

        rows.forEach(row => {
            let status = row.getAttribute("data-status");
            if (selectedStatus === "all" || status === selectedStatus) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
});
</script>

</body>
</html>

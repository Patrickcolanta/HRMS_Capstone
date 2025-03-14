<?php include('../includes/header.php')?>

<?php 
include('../includes/config.php');

if (!isset($_SESSION['slogin']) || !isset($_SESSION['srole'])) {
    header('Location: ../index.php');
    exit();
}

$userRole = $_SESSION['srole'];
if ($userRole !== 'Manager' && $userRole !== 'Admin') {
    header('Location: ../index.php');
    exit();
}

// ✅ Update status to 'Inactive' if vacancy is 0
$updateStatusSql = "UPDATE job_listings 
                    SET status = 'Inactive' 
                    WHERE vacancy = 0 AND status != 'Inactive'";
mysqli_query($conn, $updateStatusSql);

$sql = "SELECT id, job_title, job_type,salary,location, vacancy, status, description, created_at FROM job_listings ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div id="pcoded" class="pcoded">
    <div class="pcoded-container navbar-wrapper">
        <?php include('../includes/topbar.php'); ?>
        <div class="pcoded-main-container">
            <div class="pcoded-wrapper">
                <?php include('../includes/sidebar.php'); ?>
                <div class="pcoded-content">
                    <div class="pcoded-inner-content">
                        <div class="main-body">
                            <div class="page-wrapper">
                                <div class="page-header">
                                    <div class="row align-items-end">
                                        <div class="col-lg-8">
                                            <h4>Job Listings</h4>
                                        </div>
                                        <div class="col-lg-4 d-flex justify-content-end">
                                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#postJobModal">Post Job</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="page-body">
                                    <div class="card">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5>Available Jobs</h5>
                                            <input type="text" id="jobSearch" class="form-control w-25" placeholder="Search job title...">
                                        </div>
                                        <div class="card-block table-responsive">
                                            <table class="table table-striped" id="jobTable">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Job Title</th>
                                                        <th>Job Type</th>
                                                        <th>Vacancy</th>
                                                        <th>Location</th>
                                                        <th>Salary</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                                        <tr class="job-row">
                                                            <td><?= $row['id'] ?></td>
                                                            <td class="job-title"> <?= htmlspecialchars($row['job_title']) ?> </td>
                                                            <td><?= htmlspecialchars($row['job_type']) ?></td>
                                                            <td><?= htmlspecialchars($row['vacancy']) ?></td>
                                                            <td><?= htmlspecialchars($row['location']) ?></td>
                                                            <td><?= htmlspecialchars($row['salary']) ?></td>
                                                            <td><span class='badge bg-<?= $row['status'] == "Active" ? "success" : "danger" ?>'><?= htmlspecialchars($row['status']) ?></span></td>
                                                            <td>
                                                                <button class='btn btn-info view-btn' data-id='<?= $row['id'] ?>' data-bs-toggle="modal" data-bs-target="#viewJobModal">View</button>
                                                                <button class='btn btn-warning edit-btn' data-id='<?= $row['id'] ?>' data-bs-toggle="modal" data-bs-target="#editJobModal">Edit</button>
                                                                <button class='btn btn-danger delete-btn' data-id='<?= $row['id'] ?>'>Delete</button>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
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


<!-- View Job Modal -->
<div class="modal fade" id="viewJobModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5>View Job</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewJobContent"></div>
        </div>
    </div>
</div>

<!-- Edit Job Modal -->
<div class="modal fade" id="editJobModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5>Edit Job</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editJobForm">
                    <input type="hidden" name="job_id" id="editJobId">
                    
                    <div class="mb-3">
                        <label class="form-label">Job Title</label>
                        <input type="text" name="job_title" id="editJobTitle" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Job Type</label>
                        <select name="job_type" id="editJobType" class="form-select" required>
                            <option value="Full-Time">Full-Time</option>
                            <option value="Part-Time">Part-Time</option>
                            <option value="Contract">Contract</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Vacancy</label>
                        <input type="number" name="vacancy" id="editVacancy" class="form-control" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Salary</label>
                        <input type="number" name="salary" id="editSalary" class="form-control" min="0" step="0.01" placeholder="Enter salary" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" id="editLocation" class="form-control" placeholder="Enter job location" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Job Description</label>
                        <textarea name="description" id="editDescription" class="form-control" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div>
                            <input type="radio" name="status" id="editStatusActive" value="Active">
                            <label for="editStatusActive">Active</label>

                            <input type="radio" name="status" id="editStatusInactive" value="Inactive">
                            <label for="editStatusInactive">Inactive</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Update Job</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Post Job Modal -->
<div class="modal fade" id="postJobModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5>Post Job</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="jobForm">
                    <div class="mb-3">
                        <label class="form-label">Job Title</label>
                        <input type="text" name="job_title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Job Type</label>
                        <select name="job_type" class="form-select" required>
                            <option value="Full-Time">Full-Time</option>
                            <option value="Part-Time">Part-Time</option>
                            <option value="Contract">Contract</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Vacancy</label>
                        <input type="number" name="vacancy" class="form-control" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Salary</label>
                        <input type="number" name="salary" class="form-control" min="0" step="0.01" placeholder="Enter salary" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" placeholder="Enter job location" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Job Description</label>
                        <textarea name="description" class="form-control" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div>
                            <input type="radio" name="status" value="Active" checked>
                            <label>Active</label>

                            <input type="radio" name="status" value="Inactive">
                            <label>Inactive</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Post Job</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Job Confirmation Modal -->
<div class="modal fade" id="deleteJobModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5>Confirm Deletion</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this job?</p>
                <input type="hidden" id="deleteJobId">
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger" id="confirmDeleteJob">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById("jobSearch").addEventListener("keyup", function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#jobTable .job-row");

    rows.forEach(row => {
        let jobTitle = row.querySelector(".job-title").textContent.toLowerCase();
        row.style.display = jobTitle.includes(filter) ? "" : "none";
    });
});
</script>

<script>
    
    document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".delete-btn").forEach(button => {
        button.addEventListener("click", function() {
            let jobId = this.getAttribute("data-id");
            document.getElementById("deleteJobId").value = jobId;
            let deleteModal = new bootstrap.Modal(document.getElementById("deleteJobModal"));
            deleteModal.show();
        });
    });

    document.getElementById("confirmDeleteJob").addEventListener("click", function() {
        let jobId = document.getElementById("deleteJobId").value;

        fetch("delete_job.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `job_id=${encodeURIComponent(jobId)}`
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.status === "success") {
                location.reload();
            }
        })
        .catch(error => {
            console.error("Error deleting job:", error);
            alert("Failed to delete job. Please try again.");
        });
    });
});

    
document.addEventListener("DOMContentLoaded", function() {
    // View Job Modal
    document.querySelectorAll(".view-btn").forEach(button => {
        button.addEventListener("click", function() {
            let jobId = this.getAttribute("data-id");
            document.getElementById("viewJobContent").innerHTML = "Loading...";
            fetch("view_job.php?id=" + jobId)
            .then(response => response.text())
            .then(data => {
                document.getElementById("viewJobContent").innerHTML = data;
            })
            .catch(error => console.error("Error fetching job details:", error));
        });
    });

    document.querySelectorAll(".edit-btn").forEach(button => {
    button.addEventListener("click", function() {
        let jobId = this.getAttribute("data-id");
        fetch("get_job.php?id=" + jobId)
        .then(response => response.json())
        .then(data => {
            document.getElementById("editJobId").value = data.id;
            document.getElementById("editJobTitle").value = data.job_title;
            document.getElementById("editJobType").value = data.job_type;
            document.getElementById("editVacancy").value = data.vacancy;
            document.getElementById("editSalary").value = data.salary;
            document.getElementById("editLocation").value = data.location;
            document.getElementById("editDescription").value = data.description;

            // Set radio button for status
            if (data.status === "Active") {
                document.getElementById("editStatusActive").checked = true;
            } else if (data.status === "Inactive") {
                document.getElementById("editStatusInactive").checked = true;
            }
        })
        .catch(error => console.error("Error fetching job data:", error));
    });
});



    // Handle Job Update Submission
    document.getElementById("editJobForm").addEventListener("submit", function(event) {
        event.preventDefault();
        let formData = new FormData(this);

        fetch("update_job.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.status === "success") {
                location.reload();
            }
        })
        .catch(error => console.error("Error updating job:", error));
    });

    // Handle Job Posting Submission
    document.getElementById("jobForm").addEventListener("submit", function(event) {
        event.preventDefault();
        let formData = new FormData(this);

        fetch("post_job.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.status === "success") {
                location.reload();
            }
        })
        .catch(error => console.error("Error posting job:", error));
    });
});

</script>
</body>
</html>




<?php include('../includes/scripts.php'); ?>

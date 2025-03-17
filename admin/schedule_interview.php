<?php
session_start();
include('../includes/header.php');
include('../includes/config.php'); // Database connection

// Check if the user is logged in and has the required role
if (!isset($_SESSION['slogin']) || !isset($_SESSION['srole']) || !in_array($_SESSION['srole'], ['Manager', 'Admin'])) {
    header('Location: ../index.php');
    exit();
}

$userRole = $_SESSION['srole'];

// Fetch job applications with interview details only if the status is Initial Interview or Final Interview
$sql = "SELECT ja.id, ja.first_name, ja.last_name, ja.status, ja.email, 
               COALESCE(i.interview_date, 'Not Scheduled') AS interview_date, 
               COALESCE(i.interview_time, 'Not Scheduled') AS interview_time,
               COALESCE(i.interviewer, 'Not Assigned') AS interviewer, 
               COALESCE(i.interview_location, 'Not Assigned') AS interview_location,
               COALESCE(i.status, 'Not Scheduled') AS interview_status, 
               COALESCE(i.result, 'Pending') AS interview_result
        FROM job_applications ja
        LEFT JOIN interviews i ON ja.id = i.application_id
        WHERE ja.status IN ('Initial Interview', 'Final Interview')";
        
// Apply filtering if a result filter is set
if (isset($_GET['filter_result']) && in_array($_GET['filter_result'], ['Passed', 'Failed', 'Pending'])) {
    $filter = $_GET['filter_result'];
    $sql .= " AND i.result = '$filter'";
}

$sql .= " ORDER BY ja.applied_at ASC";

$result = mysqli_query($conn, $sql);

// Fetch unique statuses for filtering
$statusQuery = "SELECT DISTINCT status FROM job_applications WHERE status IN ('Initial Interview', 'Final Interview')";
$statusResult = mysqli_query($conn, $statusQuery);
$statuses = [];
while ($row = mysqli_fetch_assoc($statusResult)) {
    $statuses[] = $row['status'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Interview</title>
</head>
<body>
<?php include('../includes/loader.php'); ?>
<div id="pcoded" class="pcoded">
    <div class="pcoded-container navbar-wrapper">
        <?php include('../includes/topbar.php'); ?>
        <div class="pcoded-main-container">
            <div class="pcoded-wrapper">
                <?php $page_name = "schedule_interview"; ?>
                <?php include('../includes/sidebar.php'); ?>
                <div class="pcoded-content">
                    <div class="pcoded-inner-content">
                        <div class="main-body">
                            <div class="page-wrapper">
                                <div class="page-header">
                                    <h4>Schedule Interview</h4>
                                    <span>Select a candidate and set an interview date.</span>
                                </div>
                                <div class="page-body">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Applicants for Interview</h5>
                                        </div>
                                        <div class="card-block">
                                             <!-- Filter Dropdown -->
    <form method="GET" class="mb-3">
        <label for="filter_result">Filter by Result:</label>
        <select name="filter_result" id="filter_result" class="form-control" onchange="this.form.submit()">
            <option value="">All</option>
            <option value="Passed" <?= isset($_GET['filter_result']) && $_GET['filter_result'] == 'Passed' ? 'selected' : '' ?>>Passed</option>
            <option value="Failed" <?= isset($_GET['filter_result']) && $_GET['filter_result'] == 'Failed' ? 'selected' : '' ?>>Failed</option>
            <option value="Pending" <?= isset($_GET['filter_result']) && $_GET['filter_result'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
        </select>
    </form>
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Applicant Name</th>
                                                            <th>Email</th>
                                                            <th>Interview Type</th>
                                                            <th>Interview Date</th>
                                                            <th>Interview Time</th>
                                                            <th>Interviewer</th>
                                                            <th>Interview Location</th>
                                                            <th>Interview Status</th>
                                                            <th>Interview Result</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($row['id']) ?></td>
                                                                <td><?= htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ?></td>
                                                                <td><?= htmlspecialchars($row['email']) ?></td>
                                                                <td><?= htmlspecialchars($row['status']) ?></td>
                                                                <td><?= htmlspecialchars($row['interview_date']) ?></td>
                                                                <td><?= htmlspecialchars($row['interview_time']) ?></td>
                                                                <td><?= htmlspecialchars($row['interviewer']) ?></td>
                                                                <td><?= htmlspecialchars($row['interview_location']) ?></td>
                                                                <td><?= htmlspecialchars($row['interview_status']) ?></td>
                                                                <td><?= htmlspecialchars($row['interview_result']) ?></td>
                                                                <td>
                                                                    <button class='btn btn-primary btn-sm schedule-btn' 
                                                                        data-id='<?= $row['id'] ?>'>
                                                                        Schedule
                                                                    </button>
                                                                    
                                                                    <button class='btn btn-warning btn-sm edit-btn' 
                                                                        data-id='<?= $row['id'] ?>' 
                                                                        data-date='<?= $row['interview_date'] ?>' 
                                                                        data-time='<?= $row['interview_time'] ?>' 
                                                                        data-mode='<?= $row['interview_status'] ?>' 
                                                                        data-location='<?= $row['interview_location'] ?>' 
                                                                        data-interviewer='<?= $row['interviewer'] ?>'>
                                                                        Edit
                                                                    </button>

                                                                    <button class='btn btn-success btn-sm update-result-btn' 
                                                                        data-id='<?= $row['id'] ?>'>
                                                                        Update Result
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                

                                <?php include('../includes/scripts.php'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

                                
                              <!-- Schedule Interview Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm">

                
                    <input type="hidden" id="application_id" name="application_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Interview Date</label>
                        <input type="date" id="interview_date" name="interview_date" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Interview Time</label>
                        <input type="time" id="interview_time" name="interview_time" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Interview Mode</label>
                        <select id="interview_mode" name="interview_mode" class="form-control" required>
                            <option value="Online">Online</option>
                            <option value="In-Person">In-Person</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Interview Location</label>
                        <input type="text" id="interview_location" name="interview_location" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Interviewer</label>
                        <input type="text" id="interviewer" name="interviewer" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Schedule</button>
                </form>
            </div>
        </div>
    </div>
</div>


 <!-- Edit Interview Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="edit_application_id" name="application_id">
                    
                    <div class="mb-3">
                        <label for="edit_interview_date" class="form-label">Interview Date</label>
                        <input type="date" class="form-control" id="edit_interview_date" name="interview_date">
                    </div>

                    <div class="mb-3">
                        <label for="edit_interview_time" class="form-label">Interview Time</label>
                        <input type="time" class="form-control" id="edit_interview_time" name="interview_time">
                    </div>

                    <div class="mb-3">
                        <label for="edit_interview_mode" class="form-label">Mode</label>
                        <select class="form-control" id="edit_interview_mode" name="interview_mode">
                            <option value="Online">Online</option>
                            <option value="In-Person">In-Person</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_interview_location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="edit_interview_location" name="interview_location">
                    </div>

                    <div class="mb-3">
                        <label for="edit_interviewer" class="form-label">Interviewer</label>
                        <input type="text" class="form-control" id="edit_interviewer" name="interviewer">
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>



                                <?php include('../includes/scripts.php'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<script>

document.getElementById("filter_result").addEventListener("change", function () {
    window.location.href = "?filter_result=" + this.value;
});


document.addEventListener("DOMContentLoaded", function () {
    const scheduleModal = new bootstrap.Modal(document.getElementById("scheduleModal"));
    const editModal = new bootstrap.Modal(document.getElementById("editModal"));

    // Event delegation for Edit, Schedule, and Update Result buttons
    document.addEventListener("click", function (event) {
        if (event.target.classList.contains("edit-btn")) {
            openEditModal(event.target);
        } else if (event.target.classList.contains("schedule-btn")) {
            openScheduleModal(event.target);
        } else if (event.target.classList.contains("update-result-btn")) {
            updateInterviewResult(event.target);
        }
    });

    function openEditModal(button) {
        document.getElementById("edit_application_id").value = button.dataset.id;
        document.getElementById("edit_interview_date").value = button.dataset.date || "";
        document.getElementById("edit_interview_time").value = button.dataset.time || "";
        document.getElementById("edit_interview_mode").value = button.dataset.mode || "";
        document.getElementById("edit_interview_location").value = button.dataset.location || "";
        document.getElementById("edit_interviewer").value = button.dataset.interviewer || "";

        editModal.show();
    }

    function openScheduleModal(button) {
        document.getElementById("application_id").value = button.dataset.id;
        scheduleModal.show();
    }

    function updateInterviewResult(button) {
    let applicationId = button.dataset.id;

    Swal.fire({
        title: "Update Interview Result",
        text: "Mark candidate as Passed or Failed?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Passed",
        cancelButtonText: "Failed"
    }).then((result) => {
        if (result.isConfirmed) {
            saveInterviewResult(applicationId, "Passed");
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            saveInterviewResult(applicationId, "Failed");
        }
    });
}

function saveInterviewResult(applicationId, result) {
    fetch("manage_interview.php", {
        method: "POST",
        body: new URLSearchParams({
            mode: "update_result",
            application_id: applicationId,
            interview_result: result
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            Swal.fire("Updated!", data.message, "success");
            setTimeout(() => location.reload(), 1500);
        } else {
            Swal.fire("Error!", data.message, "error");
        }
    })
    .catch(error => {
        console.error("Request failed:", error);
        Swal.fire("Error!", "An unexpected error occurred.", "error");
    });
}

    function handleFormSubmit(form, mode, modalId) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            
            let formData = new FormData(form);
            formData.append("mode", mode); // Append mode for backend differentiation

            fetch("manage_interview.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    // Hide the modal first
                    const modalInstance = bootstrap.Modal.getInstance(document.getElementById(modalId));
                    if (modalInstance) modalInstance.hide();

                    // Wait for modal to close, then show success notification
                    setTimeout(() => {
                        Swal.fire("Success!", data.message, "success");
                        setTimeout(() => location.reload(), 1500);
                    }, 500); // Delay for modal close animation
                } else {
                    Swal.fire("Error!", data.message, "error");
                }
            })
            .catch(error => {
                console.error("Request failed:", error);
                Swal.fire("Error!", "An unexpected error occurred.", "error");
            });
        });
    }

    // Attach event listeners to forms and pass the respective modal ID
    handleFormSubmit(document.getElementById("editForm"), "edit", "editModal");
    handleFormSubmit(document.getElementById("scheduleForm"), "schedule", "scheduleModal");
});



</script>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>

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

// Fetch job applications
$sql = "SELECT ja.id, ja.first_name, ja.last_name, ja.status, ja.email, ja.phone, 
               COALESCE(i.interview_date, 'Not Scheduled') AS interview_date, 
               COALESCE(i.interview_time, 'Not Scheduled') AS interview_time,
               COALESCE(i.interviewer, 'Not Assigned') AS interviewer
        FROM job_applications ja
        LEFT JOIN interviews i ON ja.id = i.application_id
        ORDER BY ja.applied_at ASC";
$result = mysqli_query($conn, $sql);

// Fetch unique statuses for the filter dropdown
$statusQuery = "SELECT DISTINCT status FROM job_applications";
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
<?php include('../includes/loader.php') ?>
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
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead class="table-dark">
                                                        <tr>
                                                        <th>#</th>
                                                            <th>Applicant Name</th>
                                                            <th>Email</th>
                                                            <th>Status</th>
                                                            <th>Interview Date</th>
                                                            <th>Interview Time</th>
                                                            <th>Interviewer</th>
                                                            <th>Phone</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                       <?php
                                                        if (mysqli_num_rows($result) > 0) {
                                                            $count = 1;
                                                            while ($row = mysqli_fetch_assoc($result)) {
                                                                echo "<tr class='applicant-row' data-status='" . htmlspecialchars($row['status']) . "'>
                                                                    <td>" . htmlspecialchars($row['id']) . "</td>
                                                                    <td>" . htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) . "</td>
                                                                    <td>" . htmlspecialchars($row['email']) . "</td>
                                                                    <td>" . htmlspecialchars($row['status']) . "</td>
                                                                    <td>" . htmlspecialchars($row['interview_date']) . "</td>
                                                                    <td>" . htmlspecialchars($row['interview_time']) . "</td>
                                                                    <td>" . htmlspecialchars($row['interviewer']) . "</td>
                                                                    <td>" . htmlspecialchars($row['phone']) . "</td>
                                                                   <td>
                                                                    <button class='btn btn-primary btn-sm schedule-btn' 
                                                                        data-id='{$row['id']}' 
                                                                        data-name='{$row['first_name']} {$row['last_name']}'>
                                                                        Schedule
                                                                    </button>
                                                                    <button class='btn btn-warning btn-sm edit-btn' 
                                                                        data-id='{$row['id']}' 
                                                                        data-name='{$row['first_name']} {$row['last_name']}'>
                                                                        Edit
                                                                    </button>
                                                                </td>

                                                                </tr>";
                                                                $count++;
                                                            }
                                                        } else {
                                                            echo "<tr><td colspan='6' class='text-center text-muted'>No applicants found</td></tr>";
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
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
                                                        <label for="interview_date" class="form-label">Interview Date</label>
                                                        <input type="date" id="interview_date" name="interview_date" class="form-control" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="interview_time" class="form-label">Interview Time</label>
                                                        <input type="time" id="interview_time" name="interview_time" class="form-control" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="interviewer" class="form-label">Interviewer</label>
                                                        <input type="text" id="interviewer" name="interviewer" class="form-control" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="interview_mode" class="form-label">Interview Mode</label>
                                                        <select id="interview_mode" name="interview_mode" class="form-select" required>
                                                            <option value="">-- Select Mode --</option>
                                                            <option value="Online">Online</option>
                                                            <option value="In-Person">In-Person</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="interview_location" class="form-label">Interview Location</label>
                                                        <input type="text" id="interview_location" name="interview_location" class="form-control" required>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Schedule</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="edit_application_id" name="application_id">

                    <div class="mb-3">
                        <label for="edit_interview_date" class="form-label">Interview Date</label>
                        <input type="date" id="edit_interview_date" name="interview_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_interview_time" class="form-label">Interview Time</label>
                        <input type="time" id="edit_interview_time" name="interview_time" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_interviewer" class="form-label">Interviewer</label>
                        <input type="text" id="edit_interviewer" name="interviewer" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_interview_mode" class="form-label">Interview Mode</label>
                        <select id="edit_interview_mode" name="interview_mode" class="form-select" required>
                            <option value="">-- Select Mode --</option>
                            <option value="Online">Online</option>
                            <option value="In-Person">In-Person</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_interview_location" class="form-label">Interview Location</label>
                        <input type="text" id="edit_interview_location" name="interview_location" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Schedule Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="edit_application_id" name="application_id">

                    <div class="mb-3">
                        <label for="edit_interview_date" class="form-label">Interview Date</label>
                        <input type="date" id="edit_interview_date" name="interview_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_interview_time" class="form-label">Interview Time</label>
                        <input type="time" id="edit_interview_time" name="interview_time" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_interviewer" class="form-label">Interviewer</label>
                        <input type="text" id="edit_interviewer" name="interviewer" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_interview_mode" class="form-label">Interview Mode</label>
                        <select id="edit_interview_mode" name="interview_mode" class="form-select" required>
                            <option value="">-- Select Mode --</option>
                            <option value="Online">Online</option>
                            <option value="In-Person">In-Person</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_interview_location" class="form-label">Interview Location</label>
                        <input type="text" id="edit_interview_location" name="interview_location" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>


                                <?php include('../includes/scripts.php'); ?>
                                <script>
                                   document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".edit-btn").forEach(button => {
        button.addEventListener("click", function () {
            document.getElementById("edit_application_id").value = this.getAttribute("data-id");
            document.getElementById("edit_interview_date").value = this.getAttribute("data-date");
            document.getElementById("edit_interview_time").value = this.getAttribute("data-time");
            document.getElementById("edit_interview_mode").value = this.getAttribute("data-mode");
            document.getElementById("edit_interview_location").value = this.getAttribute("data-location");
            document.getElementById("edit_interviewer").value = this.getAttribute("data-interviewer");

            new bootstrap.Modal(document.getElementById("editModal")).show();
        });
    });

    document.getElementById("editForm").addEventListener("submit", function (e) {
        e.preventDefault();
        
        let formData = new FormData(this);

        fetch("update_interview_backend.php", {
            method: "POST",
            body: formData
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
        .catch(error => Swal.fire("Error!", "An error occurred.", "error"));
    });
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

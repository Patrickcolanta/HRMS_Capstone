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
$sql = "SELECT id, first_name, last_name, status FROM job_applications ORDER BY applied_at ASC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications</title>
</head>
<body>
<?php include('../includes/loader.php') ?>

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
                                                <h4>Job Applications</h4>
                                            
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="page-body">
                                    <div class="card">
                                        <div class="card-header">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5>List of Applicants</h5>
                                            <input type="text" id="applicantSearch" class="form-control w-25" placeholder="Search Applicant...">
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Applicant ID</th>
                                                            <th>Name</th>
                                                            <th>Status</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        if (mysqli_num_rows($result) > 0) {
                                                            $count = 1;
                                                            while ($row = mysqli_fetch_assoc($result)) {
                                                                echo "<tr id='row-{$row['id']}'>
                                                                    <td>{$count}</td>
                                                                    <td>{$row['id']}</td>
                                                                    <td>" . htmlspecialchars($row['first_name']) . "</td>
                                                                    <td id='status-{$row['id']}'>" . htmlspecialchars($row['status']) . "</td>
                                                                    <td>
                                                                        <button class='btn btn-info btn-sm view-btn' data-id='{$row['id']}'>View</button>
                                                                        <button class='btn btn-warning btn-sm edit-btn' data-id='{$row['id']}'>Edit</button>
                                                                        <button class='btn btn-danger btn-sm delete-btn' data-id='{$row['id']}'>Delete</button>
                                                                    </td>
                                                                </tr>";
                                                                $count++;
                                                            }
                                                        } else {
                                                            echo "<tr><td colspan='5' class='text-center text-muted'>No applications found</td></tr>";
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- View Applicant Modal -->
                                    <div class="modal fade" id="viewModal" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Applicant Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body" id="viewModalBody">
                                                    <!-- Details will be loaded dynamically -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Status Modal -->
                                    <div class="modal fade" id="editModal" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Applicant Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" id="editApplicantId">
                                                    <select id="statusSelect" class="form-select">
                                                        <option value="Pending">Pending</option>
                                                        <option value="Initial Interview">Initial Interview</option>
                                                        <option value="Final Interview">Final Interview</option>
                                                        <option value="Pass">Pass</option>
                                                        <option value="Fail">Fail</option>
                                                    </select>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-success" id="saveStatusBtn">Save</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php include('../includes/scripts.php'); ?>


                                    <script>
document.getElementById("applicantSearch").addEventListener("keyup", function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("tbody tr"); // Select all table rows inside <tbody>

    rows.forEach(row => {
        let applicantID = row.cells[1].textContent.toLowerCase(); // Get Applicant ID
        let applicantName = row.cells[2].textContent.toLowerCase(); // Get Name

        // Show row if the search term matches ID or Name
        if (applicantID.includes(filter) || applicantName.includes(filter)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
});

</script>
                                    <script>
                            
                            document.addEventListener("DOMContentLoaded", function () {
    // 📌 View Applicant Details
    document.addEventListener("click", function (event) {
        if (event.target.classList.contains("view-btn")) {
            let applicantId = event.target.getAttribute("data-id");
            fetch("get_applicant_details.php?id=" + applicantId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById("viewModalBody").innerHTML = data;
                    new bootstrap.Modal(document.getElementById("viewModal")).show();
                })
                .catch(error => console.error("Error fetching applicant details:", error));
        }
    });
    

    // 📌 Open Edit Status Modal
    document.addEventListener("click", function (event) {
        if (event.target.classList.contains("edit-btn")) {
            let applicantId = event.target.getAttribute("data-id");
            document.getElementById("editApplicantId").value = applicantId;
            new bootstrap.Modal(document.getElementById("editModal")).show();
        }
    });

    // 📌 Save Edited Status
    document.getElementById("saveStatusBtn").addEventListener("click", function () {
        let applicantId = parseInt(document.getElementById("editApplicantId").value, 10);
let newStatus = document.getElementById("statusSelect").value.trim();


    let formData = new FormData();
    formData.append("id", applicantId);
    formData.append("status", newStatus);

    fetch("edit_applicant_status.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update status in the table
            let statusElement = document.getElementById(`status-${applicantId}`);
            if (statusElement) {
                statusElement.textContent = newStatus;
            }

            // Close modal
            let editModalElement = document.getElementById("editModal");
            if (editModalElement) {
                let editModalInstance = bootstrap.Modal.getOrCreateInstance(editModalElement);
                editModalInstance.hide();
            }
        } else {
            alert("Failed to update status: " + data.message);
        }
    })
    .catch(error => console.error("Error updating status:", error));
});




    // 📌 Delete Applicant
    document.addEventListener("click", function (event) {
        if (event.target.classList.contains("delete-btn")) {
            let applicantId = event.target.getAttribute("data-id");

            if (confirm("Are you sure you want to delete this applicant?")) {
                fetch(`delete_applicant.php?id=${applicantId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById(`row-${applicantId}`).remove();
                        } else {
                            alert("Failed to delete applicant.");
                        }
                    })
                    .catch(error => console.error("Error deleting applicant:", error));
            }
        }
    });
});
                                    </script>

</body>
</html>

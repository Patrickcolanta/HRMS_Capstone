<?php
session_start();
include('../includes/header.php');
include('../includes/config.php'); // Database connection

// Regenerate session ID for security
session_regenerate_id(true);

// Check if the user is logged in and has the required role
if (!isset($_SESSION['slogin']) || !isset($_SESSION['srole']) || !in_array($_SESSION['srole'], ['Manager', 'Admin'])) {
    header('Location: ../index.php');
    exit();
}

// Fetch only applicants who have "Passed" the interview and include required fields
$sql = "SELECT ja.id, 
               CONCAT(ja.first_name, ' ', ja.last_name) AS applicant_name, 
               ja.email, 
               ja.phone, 
               jo.status, 
               jo.offer_sent,  -- Fetch offer_sent status
               jo.message      -- Fetch message content
        FROM job_applications ja
        LEFT JOIN job_offers jo ON ja.id = jo.application_id
        ORDER BY jo.status DESC";

$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Database Query Failed: " . mysqli_error($conn)); // Debugging SQL errors
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
                <?php $page_name = "job_offer"; ?>
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
        <th>Phone Number</th>
        <th>Job Offer Status</th>
        <th>Offer Sent</th> <!-- New column -->
        <th>Action</th>
    </tr>
</thead>
<tbody>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['applicant_name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td><?= $row['offer_sent'] ? 'Yes' : 'No' ?></td> <!-- Show if offer was sent -->
            
            <td>
                <button class='btn btn-primary btn-sm send-email-btn' 
                    data-id='<?= $row['id'] ?>' 
                    data-email='<?= $row['email'] ?>'
                    data-message='<?= htmlspecialchars($row['message']) ?>'>
                    Send Email
                </button>
                
                <button class='btn btn-success btn-sm update-status-btn' 
                    data-id='<?= $row['id'] ?>' 
                    data-status='<?= $row['status'] ?>'>
                    Update Status
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

                                
                            <!-- Send Email Modal -->
<div class="modal fade" id="emailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
    <form id="emailForm">
        <input type="hidden" id="email_application_id" name="application_id">
        <div class="mb-3">
            <label class="form-label">Recipient Email</label>
            <input type="email" id="recipient_email" name="email" class="form-control" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea id="email_message" name="message" class="form-control" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
</div>

        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Job Offer Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="statusForm">
                    <input type="hidden" id="status_application_id" name="application_id">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select id="job_offer_status" name="status" class="form-control" required>
                            <option value="Accepted">Accepted</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const emailModal = new bootstrap.Modal(document.getElementById("emailModal"));
    const statusModal = new bootstrap.Modal(document.getElementById("statusModal"));

    document.addEventListener("click", function (event) {
        if (event.target.classList.contains("send-email-btn")) {
            openEmailModal(event.target);
        } else if (event.target.classList.contains("update-status-btn")) {
            openStatusModal(event.target);
        }
    });

    function openEmailModal(button) {
    document.getElementById("email_application_id").value = button.dataset.id;
    document.getElementById("recipient_email").value = button.dataset.email;
    document.getElementById("email_message").value = button.dataset.message || ""; // Pre-fill message
    emailModal.show();
}


    function openStatusModal(button) {
        document.getElementById("status_application_id").value = button.dataset.id;
        document.getElementById("job_offer_status").value = button.dataset.status;
        statusModal.show();
    }

    function handleFormSubmit(form, mode, modalId) {
    form.addEventListener("submit", function (e) {
        e.preventDefault();
        
        let formData = new FormData(form);
        formData.append("mode", mode);

        fetch("manage_job_offer.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                const modalElement = document.getElementById(modalId);
                const modalInstance = bootstrap.Modal.getInstance(modalElement);

                if (modalInstance) {
                    document.activeElement.blur();  // Move focus away from button
                    modalInstance.hide();  // Hide modal
                }

                setTimeout(() => {
                    Swal.fire("Success!", data.message, "success");
                    setTimeout(() => location.reload(), 1500);
                }, 500);
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


    handleFormSubmit(document.getElementById("emailForm"), "send_email", "emailModal");
    handleFormSubmit(document.getElementById("statusForm"), "update_status", "statusModal");
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

<?php
session_start();
include('../includes/header.php');
include('../includes/config.php'); // Database connection

// Regenerate session ID for security
session_regenerate_id(true);

// Allow only Admin, Manager, or HR employees
if ($_SESSION['srole'] !== 'Admin' && $_SESSION['srole'] !== 'HR' && $_SESSION['sdepartment'] !== 'Human Resources') {
    header("Location: index.php");
    exit();
}

// Fetch only applicants who have "Completed" the interview and have "Passed" the result
$sql = "SELECT ja.id, 
               CONCAT(ja.first_name, ' ', ja.last_name) AS applicant_name, 
               ja.email, 
               ja.phone, 
               IFNULL(i.offer_status, 'Not Endorsed') AS status, 
               IFNULL(i.offer_sent, 0) AS offer_sent
        FROM job_applications ja
        LEFT JOIN interviews i ON ja.id = i.application_id
        WHERE i.status = 'Completed' 
              AND i.result = 'Passed'
        ORDER BY i.offer_status ASC";

$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Database Query Failed: " . mysqli_error($conn)); // Debugging SQL errors
}

$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}

// echo '<pre>';
// print_r($rows);
// echo '</pre>';

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
                                                <div class="mb-3">
                                                    <label for="filter_status">Filter by Job Offer Status:</label>
                                                    <select id="filter_status" class="form-control">
                                                        <option value="">All</option>
                                                        <option value="Accepted">Accepted</option>
                                                        <option value="Rejected">Rejected</option>
                                                        <option value="Pending">Pending</option>
                                                        <option value="Not Endorsed">Not Endorsed</option>
                                                    </select>
                                                </div>

                                                <script>
                                                    document.addEventListener("DOMContentLoaded", function() {
                                                        const filterStatus = document.getElementById("filter_status");
                                                        const tableRows = document.querySelectorAll("table tbody tr");

                                                        filterStatus.addEventListener("change", function() {
                                                            const selectedStatus = this.value.toLowerCase();

                                                            tableRows.forEach(row => {
                                                                const statusCell = row.querySelector("td:nth-child(5)");
                                                                const statusText = statusCell ? statusCell.textContent.trim().toLowerCase() : "";

                                                                if (selectedStatus === "" || statusText === selectedStatus) {
                                                                    row.style.display = "";
                                                                } else {
                                                                    row.style.display = "none";
                                                                }
                                                            });
                                                        });
                                                    });
                                                </script>
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
                                                            <?php foreach ($rows as $row): ?>
                                                                <tr>
                                                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                                                    <td><?= htmlspecialchars($row['applicant_name']) ?></td>
                                                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                                                    <td><?= htmlspecialchars($row['phone']) ?></td>
                                                                    <td>
                                                                        <span style="display: inline-block; padding: 5px 10px; border-radius: 15px; color: #fff; 
                                                                            background-color: 
                                                                                <?= $row['status'] === 'Accepted' ? '#28a745' : ($row['status'] === 'Rejected' ? '#dc3545' : ($row['status'] === 'Pending' ? '#ffc107' : '#6c757d')) ?>;">
                                                                            <?= htmlspecialchars($row['status'] ?: 'Pending') ?>
                                                                        </span>
                                                                    </td>
                                                                    <td><?= $row['offer_sent'] ? $row['offer_sent'] : 'No email sent yet' ?></td>

                                                                    <td>
                                                                        <?php if ($row['offer_sent'] !== 'Onboarding Offer Sent'): ?>
                                                                            <button class='btn btn-primary btn-sm send-email-btn'
                                                                                data-id='<?= $row['id'] ?>'
                                                                                data-email='<?= $row['email'] ?>'
                                                                                data-status='<?= $row['status'] ?>'
                                                                                data-applicant='<?= htmlspecialchars($row['applicant_name']) ?>'
                                                                                data-message='<?= htmlspecialchars($row['message']) ?>'>
                                                                                Send Email
                                                                            </button>
                                                                        <?php endif; ?>

                                                                        <?php if ($row['status'] !== 'Accepted' && $row['status'] !== 'Rejected' && $row['offer_sent']): ?>
                                                                            <button class='btn btn-success btn-sm update-status-btn'
                                                                                data-id='<?= $row['id'] ?>'
                                                                                data-status='<?= $row['status'] ?>'>
                                                                                Update Status
                                                                            </button>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
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



                        <!-- Applicant Name -->
                        <div class="mb-3">
                            <label class="form-label">Applicant Name</label>
                            <input type="text" id="applicant_name" name="applicant_name" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Template</label>
                            <select id="email_template" class="form-select">
                                <option value="">-- Select Template --</option>
                                <option value="job_offer">Job Offer</option>
                                <option value="onboarding">Onboarding</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea id="email_message" name="message" class="form-control" rows="8" required></textarea>
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
        document.addEventListener("DOMContentLoaded", function() {
            const emailModal = new bootstrap.Modal(document.getElementById("emailModal"));
            const statusModal = new bootstrap.Modal(document.getElementById("statusModal"));

            const templateSelect = document.getElementById("email_template");
            const messageBox = document.getElementById("email_message");

            const templates = {
                job_offer: `Subject: Job Offer for [Job Title] at [Company Name]

Hi [Candidate’s Name],

I hope you're doing well! We’re excited to offer you the position of [Job Title] at [Company Name]. After reviewing your qualifications and getting to know you during the interview process, we’re confident that you'll be a valuable addition to our team.

Here are the details of the offer:

Position: [Job Title]
Start Date: [Proposed Start Date]
Location: [Remote / On-site / Hybrid – Office Location]
Salary: [Base Salary] per [year/month/hour]
Benefits: [Briefly list key benefits – health insurance, 401(k), PTO, etc.]

Please find the formal offer letter attached for your review. If you have any questions or would like to discuss any part of the offer, don’t hesitate to reach out.

We’d love to have you on board and look forward to your response by [Response Deadline, e.g., Friday, April 25].

Best regards,
[Your Full Name]
[Your Job Title]
[Company Name]
[Phone Number]
[Email Address]`,

                onboarding: `Hi [Candidate's First Name],

Welcome to the team – we’re thrilled to have you on board!

You’ve officially joined Charlex International Corporation, and we’re excited to begin this journey with you. Your skills and experience stood out, and we’re confident you’ll make a great impact here.

To get started, please log in to our portal: https://hrms-charlex.site/

Click “Forgot Password” to set your password (minimum 8 characters, with 1 uppercase and 1 special character), then log in again.

If you have questions, feel free to reach out.

Best regards,  
John Patrick A. Colanta  
HR Head  
Charlex International Corporation`
            };

            templateSelect.addEventListener("change", function() {
                const selected = this.value;
                messageBox.value = templates[selected] || "";
            });

            document.addEventListener("click", function(event) {
                if (event.target.classList.contains("send-email-btn")) {
                    openEmailModal(event.target);
                } else if (event.target.classList.contains("update-status-btn")) {
                    openStatusModal(event.target);
                }
            });

            function openEmailModal(button) {
                document.getElementById("email_application_id").value = button.dataset.id;
                document.getElementById("recipient_email").value = button.dataset.email;
                document.getElementById("applicant_name").value = button.dataset.applicant;
                const status = button.dataset.status;
                document.getElementById("job_offer_status").value = status;

                // Pre-select template if status is "Accepted"
                if (status === "Accepted") {
                    templateSelect.value = "onboarding";
                    messageBox.value = templates["onboarding"];
                } else {
                    templateSelect.value = "job_offer";
                    messageBox.value = templates["job_offer"];
                }

                emailModal.show();
            }

            function openStatusModal(button) {
                document.getElementById("status_application_id").value = button.dataset.id;
                document.getElementById("job_offer_status").value = button.dataset.status;
                statusModal.show();
            }

            function handleFormSubmit(form, mode, modalId) {
                form.addEventListener("submit", function(e) {
                    e.preventDefault();

                    let formData = new FormData(form);
                    formData.append("mode", mode);

                    // Pass the selected template to the server
                    if (mode === "send_email") {
                        const selectedTemplate = document.getElementById("email_template").value;
                        formData.append("template", selectedTemplate);
                    }

                    fetch("manage_job_offer.php", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.text())
                        .then(text => {
                            console.log("Raw response:", text);
                            try {
                                let data = JSON.parse(text);
                                if (data.status === "success") {
                                    Swal.fire("Success!", data.message, "success").then(() => {
                                        if (modalId) {
                                            const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
                                            modal.hide();
                                        }
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire("Error!", data.message, "error");
                                }
                            } catch (e) {
                                console.error("JSON Parsing Error:", e, text);
                                Swal.fire("Error!", "Invalid response from server", "error");
                            }
                        })
                        .catch(error => {
                            console.error("Request failed:", error);
                            Swal.fire("Error!", "An unexpected error occurred.", "error");
                        })
                        .finally(() => {
                            if (modalId === "emailModal") {
                                emailModal.hide();
                            }
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
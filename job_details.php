<?php
include('admin/config.php'); // Database connection

// Check if job ID is provided
if (!isset($_GET['id'])) {
    die("<div class='alert alert-danger text-center mt-5'>Error: Job ID is missing!</div>");
}

$job_id = intval($_GET['id']); // Sanitize input

// Fetch job details from job_listings including salary and location
$sql = "SELECT id, job_title, description, job_type, salary, location, created_at 
        FROM job_listings 
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();
$job = $result->fetch_assoc();

if (!$job) {
    die("<div class='alert alert-danger text-center mt-5'>Error: No job found!</div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($job['job_title']) ?> - Job Details</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: 'Arial', sans-serif;
        }
        .job-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .job-title {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
        }
        .job-info {
            font-size: 16px;
            color: #555;
        }
        .job-description {
            font-size: 16px;
            line-height: 1.6;
        }
        .btn-custom {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: 0.3s;
        }
        .btn-custom:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="job-container">
        <h2 class="job-title"><i class="fa-solid fa-briefcase"></i> <?= htmlspecialchars($job['job_title']) ?></h2>
        <hr>
        <p class="job-info"><i class="fa-solid fa-calendar"></i> <strong>Posted on:</strong> <?= date('F j, Y', strtotime($job['created_at'])) ?></p>
        <p class="job-info"><i class="fa-solid fa-clipboard-list"></i> <strong>Job Type:</strong> <?= htmlspecialchars($job['job_type']) ?></p>

        <!-- Display salary -->
        <?php if (!empty($job['salary'])): ?>
            <p class="job-info"><i class="fa-solid fa-dollar-sign"></i> <strong>Salary:</strong> $<?= number_format($job['salary'], 2) ?></p>
        <?php endif; ?>

        <!-- Display location -->
        <?php if (!empty($job['location'])): ?>
            <p class="job-info"><i class="fa-solid fa-location-pin"></i> <strong>Location:</strong> <?= htmlspecialchars($job['location']) ?></p>
        <?php endif; ?>

        <hr>
        <h4>Description:</h4>
        <p class="job-description"><?= nl2br(htmlspecialchars($job['description'])) ?></p>

        <div class="text-center mt-4">
            <a href="recruitment_index.php" class="btn-custom"><i class="fa-solid fa-arrow-left"></i> Back to Home Page</a>
        </div>

        <!-- Apply Now Button -->
        <div class="text-center mt-4">
            <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#applyJobModal">
                <i class="fa-solid fa-paper-plane"></i> Apply Now
            </a>
        </div>
    </div>
</div>

<!-- Modal Form for Applying -->
<div class="modal fade" id="applyJobModal" tabindex="-1" aria-labelledby="applyJobModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="applyJobModalLabel">Job Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="applyJobForm" action="submit_application.php" method="POST" enctype="multipart/form-data">

                    <input type="hidden" name="job_id" id="job_id" value="<?php echo $_GET['id']; ?>">

                    <!-- First Name -->
                    <div class="mb-3">
                        <label for="firstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="firstName" name="first_name" required>
                    </div>

                    <!-- Last Name -->
                    <div class="mb-3">
                        <label for="lastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="lastName" name="last_name" required>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <div class="input-group">
                                <span class="input-group-text">+63</span>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                    pattern="^\d{10}$" 
                                    required 
                                    maxlength="10"
                                    placeholder="XXXXXXXXXX">
                            </div>
                            <small class="text-muted">Enter your 10-digit mobile number (e.g., 9123456781).</small>
                        </div>


                    <!-- Resume Upload -->
                    <div class="mb-3">
                        <label for="resume" class="form-label">Upload Resume</label>
                        <input type="file" class="form-control" id="resume" name="resume" required>
                    </div>

                    <!-- Cover Letter -->
                    <div class="mb-3">
                        <label for="coverLetter" class="form-label">Cover Letter</label>
                        <textarea class="form-control" id="coverLetter" name="cover_letter" rows="3" required></textarea>
                    </div>  

                    <div class="col-md-12 form-check">
                    <input type="checkbox" id="privacyAgreement" class="form-check-input" required>
                    <label for="privacyAgreement" class="form-check-label">
                        I agree to the <a href="privacy_policy.php" target="_blank">Data Privacy Policy</a>.
                    </label>
                </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submitBtn" class="btn btn-primary w-100" disabled>Submit Application</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS (required for modal functionality) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById('privacyAgreement').addEventListener('change', function() {
    document.getElementById('submitBtn').disabled = !this.checked;
});
</script>


<script>
document.getElementById('phone').addEventListener('input', function() {
    const phoneInput = this;
    const phoneNumber = phoneInput.value;

    // Regular expression for exactly 10 digits
    const phRegex = /^\d{10}$/;

    if (!phRegex.test(phoneNumber)) {
        phoneInput.setCustomValidity("Please enter exactly 10 digits.");
    } else {
        phoneInput.setCustomValidity("");
    }
});
</script>


<script>
    document.getElementById('applyJobForm').addEventListener('submit', function(event) {
    event.preventDefault();
    console.log("📩 Form submitted!");

    let jobId = document.getElementById('job_id') ? document.getElementById('job_id').value : null;
    let formData = new FormData(this);

    if (!formData.has('job_id') && jobId) {
        formData.append('job_id', jobId);
    }

    fetch('submit_application.php', {
    method: 'POST',
    body: formData
    })
    .then(response => response.text())  // Get raw response
    .then(data => {
        console.log("📢 Raw Response:", data); // Log the full response

        try {
            let jsonData = JSON.parse(data); // Try parsing JSON
            console.log("✅ Parsed JSON:", jsonData);
            
            Swal.fire({
                icon: jsonData.status === 'success' ? 'success' : 'error',
                title: jsonData.status === 'success' ? 'Application Submitted!' : 'Error',
                text: jsonData.message,
                confirmButtonColor: jsonData.status === 'success' ? '#01a9ac' : '#eb3422'
            });
        } catch (error) {
            console.error("❌ JSON Parse Error:", error);
            Swal.fire({
                icon: 'error',
                title: 'Invalid Response!',
                text: 'Server returned an invalid response. Check the console.',
                confirmButtonColor: '#eb3422'
            });
        }
    })
    .catch(error => {
        console.error("🚨 Fetch Error:", error);
        Swal.fire({
            icon: 'error',
            title: 'Submission Failed!',
            text: '🚨 Something went wrong. Please try again.',
            confirmButtonColor: '#eb3422'
        });
    });


});


</script>

</body>
</html>

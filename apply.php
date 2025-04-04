<?php
include('admin/config.php'); // Database connection

if (!isset($_GET['id'])) {
    die("Error: Job ID is missing!");
}

$job_id = intval($_GET['id']);
$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $cover_letter = trim($_POST['cover_letter']);
    $expected_salary = trim($_POST['expected_salary']);
    $linkedin = trim($_POST['linkedin']);
    $start_date = trim($_POST['start_date']);

    // Email Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Invalid email format!";
    }

    // Phone Validation (Philippines format)
    if (!preg_match("/^(09|\+639)\d{9}$/", $phone)) {
        $errorMessage = "Invalid phone number! Use PH format (09XXXXXXXXX or +639XXXXXXXXX).";
    }

    // LinkedIn URL Validation
    if (!empty($linkedin) && !filter_var($linkedin, FILTER_VALIDATE_URL)) {
        $errorMessage = "Invalid LinkedIn URL!";
    }

    // Resume Upload Handling
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $allowedExtensions = ['pdf', 'doc', 'docx'];
        $fileInfo = pathinfo($_FILES['resume']['name']);
        $resume_extension = strtolower($fileInfo['extension']);
        $resume_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['resume']['name']);
        $resume_tmp = $_FILES['resume']['tmp_name'];
        $resume_path = "uploads/" . $resume_name;

        if (!in_array($resume_extension, $allowedExtensions)) {
            $errorMessage = "Invalid file format! Only PDF, DOC, and DOCX are allowed.";
        } elseif ($_FILES['resume']['size'] > 5 * 1024 * 1024) {
            $errorMessage = "File size must be under 5MB!";
        } else {
            // Ensure uploads directory exists
            if (!is_dir("uploads")) {
                mkdir("uploads", 0777, true);
            }

            if (move_uploaded_file($resume_tmp, $resume_path)) {
                // Insert data into database
                $stmt = $conn->prepare("INSERT INTO job_applications 
                    (job_id, applicant_name, email, phone, address, cover_letter, expected_salary, linkedin, start_date, resume_path, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");

                $stmt->bind_param("isssssssss", $job_id, $name, $email, $phone, $address, $cover_letter, $expected_salary, $linkedin, $start_date, $resume_name);

                if ($stmt->execute()) {
                    $successMessage = "Application submitted successfully!";
                } else {
                    $errorMessage = "Database Error: " . $stmt->error;
                }

                $stmt->close();
            } else {
                $errorMessage = "Error uploading file.";
            }
        }
    } else {
        $errorMessage = "Please upload a valid resume.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Job</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center text-primary">Apply for Job</h2>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <div class="card shadow-lg p-4">
        <form id="jobApplicationForm" action="" method="post" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone (PH Format)</label>
                    <input type="text" name="phone" class="form-control" required pattern="^(09|\+639)\d{9}$" title="Use PH format: 09XXXXXXXXX or +639XXXXXXXXX">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" required></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Expected Salary (PHP)</label>
                    <input type="number" name="expected_salary" class="form-control" required min="1000">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Available Start Date</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">LinkedIn Profile (Optional)</label>
                    <input type="url" name="linkedin" class="form-control">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Cover Letter</label>
                    <textarea name="cover_letter" class="form-control" required></textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Upload Resume (PDF, DOCX - Max 5MB)</label>
                    <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx" required>
                </div>

                <div class="col-md-12 form-check">
                    <input type="checkbox" id="privacyAgreement" class="form-check-input" required>
                    <label for="privacyAgreement" class="form-check-label">
                        I agree to the <a href="privacy_policy.php" target="_blank">Data Privacy Policy</a>.
                    </label>
                </div>

                <div class="col-md-12 text-center">
                    <button type="submit" id="submitBtn" class="btn btn-primary w-100" disabled>Submit Application</button>
                    <a href="job_details.php?id=<?php echo $job_id; ?>" class="btn btn-secondary w-100 mt-2">← Back to Job Details</a>  
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('privacyAgreement').addEventListener('change', function() {
    document.getElementById('submitBtn').disabled = !this.checked;
});
</script>

</body>
</html>

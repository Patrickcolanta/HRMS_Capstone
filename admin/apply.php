<?php
include('config.php');

if (!isset($_GET['id'])) {
    die("Error: Job ID is missing!");
}

$job_id = intval($_GET['id']);
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    if ($_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $resume_name = basename($_FILES['resume']['name']);
        $resume_tmp = $_FILES['resume']['tmp_name'];
        $resume_path = "uploads/" . $resume_name;
        
        if (move_uploaded_file($resume_tmp, $resume_path)) {
            $sql = "INSERT INTO job_applications (job_id, name, email, resume) VALUES ('$job_id', '$name', '$email', '$resume_name')";
            if (mysqli_query($conn, $sql)) {
                $successMessage = "Application submitted successfully!";
            } else {
                $successMessage = "Error: " . mysqli_error($conn);
            }
        } else {
            $successMessage = "Error uploading file.";
        }
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
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">Apply for Job</h2>

    <?php if ($successMessage): ?>
        <div class="alert alert-success"><?php echo $successMessage; ?></div>
    <?php endif; ?>

    <div class="card p-4">
        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Upload Resume (PDF, DOCX)</label>
                <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx" required>
            </div>

            <button type="submit" class="btn btn-primary">Submit Application</button>
        </form>
    </div>
</div>

</body>
</html>

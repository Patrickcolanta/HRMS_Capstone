<?php
include('config.php'); 

if (!isset($_GET['id'])) {
    die("Error: Job ID is missing!");
}

$job_id = intval($_GET['id']);
$sql = "SELECT j.job_title, j.job_description, d.name AS department, j.job_type, j.created_at 
        FROM job_listings j 
        JOIN department d ON j.department_id = d.id 
        WHERE j.id = $job_id";
$result = mysqli_query($conn, $sql);
$job = mysqli_fetch_assoc($result);

if (!$job) {
    die("Error: No job found!");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['job_title']); ?> - Job Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center"><?php echo htmlspecialchars($job['job_title']); ?></h2>
    <p class="text-muted text-center"><?php echo htmlspecialchars($job['department']); ?> | <?php echo htmlspecialchars($job['job_type']); ?></p>
    
    <div class="card p-4">
        <h5>Job Description</h5>
        <p><?php echo nl2br(htmlspecialchars($job['job_description'])); ?></p>
        
        <a href="apply.php?id=<?php echo $job_id; ?>" class="btn btn-success">Apply Now</a>
    </div>
</div>

</body>
</html>

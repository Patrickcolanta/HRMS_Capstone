<?php
include('../includes/config.php');

if (!isset($_GET['id'])) {
    echo "<p class='text-danger'>Invalid request!</p>";
    exit();
}

$jobId = intval($_GET['id']);

// Update the SQL query to include salary and location
$sql = "SELECT job_title, job_type, vacancy, status, description, salary, location, created_at FROM job_listings WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $jobId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $job = $result->fetch_assoc();
    ?>
    <div class="p-3">
        <h5><?= htmlspecialchars($job['job_title']) ?></h5>
        <p><strong>Job Type:</strong> <?= htmlspecialchars($job['job_type']) ?></p>
        <p><strong>Vacancy:</strong> <?= htmlspecialchars($job['vacancy']) ?></p>
        <p><strong>Status:</strong> <span class="badge bg-<?= $job['status'] == "Active" ? "success" : "danger" ?>"><?= htmlspecialchars($job['status']) ?></span></p>
        <p><strong>Description:</strong></p>
        <p><?= nl2br(htmlspecialchars($job['description'])) ?></p>

        <!-- Display salary and location -->
        <p><strong>Salary:</strong> $<?= number_format($job['salary'], 2) ?></p>
        <p><strong>Location:</strong> <?= htmlspecialchars($job['location']) ?></p>

        <p><small class="text-muted">Posted on: <?= date("F j, Y, g:i a", strtotime($job['created_at'])) ?></small></p>
    </div>
    <?php
} else {
    echo "<p class='text-danger'>Job not found!</p>";
}

$stmt->close();
$conn->close();
?>

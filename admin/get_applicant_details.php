<?php
include('../includes/config.php');

if (!isset($_GET['id'])) {
    echo "<p class='text-danger'>Invalid request!</p>";
    exit();
}

$applicantId = intval($_GET['id']);

$sql = "SELECT ja.id, ja.first_name, ja.last_name, ja.email, ja.phone,  ja.status, ja.applied_at, ja.resume_path, 
               jl.job_title 
        FROM job_applications ja
        LEFT JOIN job_listings jl ON ja.job_id = jl.id
        WHERE ja.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $applicantId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
?>
    <div class="p-3">
        <h5><?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?></h5>
        <p><strong>Applied for:</strong> <?= htmlspecialchars($row['job_title']) ?></p>
        <p><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($row['email']) ?>"><?= htmlspecialchars($row['email']) ?></a></p>
        <p><strong>Phone:</strong> <a href="tel:<?= htmlspecialchars($row['phone']) ?>"><?= htmlspecialchars($row['phone']) ?></a></p>
        <p><strong>Status:</strong> <span class="badge bg-<?= $row['status'] == "Active" ? "success" : "danger" ?>"><?= htmlspecialchars($row['status']) ?></span></p>
        <p><strong>Applied At:</strong> <?= date("F j, Y, g:i a", strtotime($row['applied_at'])) ?></p>

        <?php if (!empty($row['resume_path'])) { ?>
            <p><strong>Resume:</strong> <a href="../<?= htmlspecialchars($row['resume_path']) ?>" target="_blank" class="btn btn-primary btn-sm">View Resume</a></p>
        <?php } else { ?>
            <p><strong>Resume:</strong> No resume uploaded</p>
        <?php } ?>
    </div>
<?php
} else {
    echo "<p class='text-danger'>Applicant not found.</p>";
}

$stmt->close();
$conn->close();
?>

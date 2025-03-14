<?php
include('../includes/config.php');

if (isset($_GET['id'])) {
    $job_id = $_GET['id'];

    $sql = "SELECT * FROM job_listings WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "Job not found"]);
    }

    $stmt->close();
    $conn->close();
}
?>

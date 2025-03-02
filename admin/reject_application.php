<?php
include('../includes/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['application_id']) && isset($_POST['reason'])) {
    $application_id = intval($_POST['application_id']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    $sql = "UPDATE applications SET status = 'Rejected', cover_letter = CONCAT(cover_letter, '\n\nRejection Reason: ', ?) WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $reason, $application_id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["status" => "success", "message" => "Application rejected successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to reject application."]);
    }
}
?>

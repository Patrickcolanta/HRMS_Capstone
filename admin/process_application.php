<?php
include 'config.php';

$id = $_GET['id'];
$action = $_GET['action'];

$status = ($action == 'approve') ? 'Approved' : 'Rejected';

$sql = "UPDATE applications SET status='$status' WHERE id=$id";
if (mysqli_query($conn, $sql)) {
    echo "Application $status successfully!";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
<a href="applications.php">Back to Applications</a>

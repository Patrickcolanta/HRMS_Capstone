<?php
include('../includes/config.php');

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

// Check if the necessary data is set
if (!isset($_POST['application_id']) || !isset($_POST['new_status'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit();
}

$applicationId = intval($_POST['application_id']); // Ensure ID is an integer
$newStatus = mysqli_real_escape_string($conn, trim($_POST['new_status'])); // Sanitize input

// Allowed statuses
$validStatuses = ['Approved', 'Interview Scheduled', 'Hired', 'Rejected'];
if (!in_array($newStatus, $validStatuses)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
    exit();
}

// Check if application exists
$appCheckQuery = "SELECT id FROM applications WHERE id = $applicationId";
$appCheckResult = mysqli_query($conn, $appCheckQuery);
if (!$appCheckResult || mysqli_num_rows($appCheckResult) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Application not found']);
    exit();
}

// Update status
$updateQuery = "UPDATE applications SET status = '$newStatus' WHERE id = $applicationId";
if (mysqli_query($conn, $updateQuery)) {
    echo json_encode(['status' => 'success', 'new_status' => $newStatus]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update status: ' . mysqli_error($conn)]);
}
?>

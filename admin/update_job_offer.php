<?php
include('../includes/config.php'); // Include database connection

if (isset($_GET['id']) && isset($_GET['status'])) {
    $application_id = intval($_GET['id']);
    $status = $_GET['status'] === 'Accepted' ? 'Accepted' : 'Rejected'; // Ensure valid status

    // Update the job offer status
    $query = "UPDATE job_offers SET status = ? WHERE application_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $application_id);

    if ($stmt->execute()) {
        // If the job offer is accepted, update the hiring status
        if ($status === 'Accepted') {
            $hiring_status = "Hired";
            $hiringQuery = "UPDATE job_applications SET hiring_status = ? WHERE id = ?";
            $hiringStmt = $conn->prepare($hiringQuery);
            $hiringStmt->bind_param("si", $hiring_status, $application_id);
            
            if ($hiringStmt->execute()) {
                echo "<h2>Thank you!</h2>";
                echo "<p>Your response has been recorded as: <strong>$status</strong></p>";
                echo "<p>Hiring status has been updated to: <strong>$hiring_status</strong></p>";
            } else {
                echo "<h2>Error</h2>";
                echo "<p>Failed to update hiring status. Please try again later.</p>";
            }
            $hiringStmt->close();
        } else {
            echo "<h2>Thank you!</h2>";
            echo "<p>Your response has been recorded as: <strong>$status</strong></p>";
        }
    } else {
        echo "<h2>Error</h2>";
        echo "<p>Failed to update the job offer status. Please try again later.</p>";
    }

    $stmt->close();
} else {
    echo "<h2>Error</h2>";
    echo "<p>Invalid request.</p>";
}

$conn->close();
?>

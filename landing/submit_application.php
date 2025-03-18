<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json'); // Ensure JSON response

include('../admin/config.php'); // Include database connection

$response = ['status' => 'error', 'message' => 'Unknown error occurred.'];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request.');
    }

    // ✅ Ensure all required POST fields are received
    $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
    $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $cover_letter = isset($_POST['cover_letter']) ? trim($_POST['cover_letter']) : '';

    if (!$job_id || empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
        throw new Exception('Missing required fields.');
    }

    // ✅ Check if job exists
    $stmt = $conn->prepare("SELECT vacancy FROM job_listings WHERE id = ?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows === 0) {
        throw new Exception('Job not found.');
    }

    $job = $result->fetch_assoc();
    if (intval($job['vacancy']) <= 0) {
        throw new Exception('No more vacancies for this job.');
    }

    // ✅ Handle Resume Upload
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $allowedExtensions = ['pdf', 'doc', 'docx'];
        $fileInfo = pathinfo($_FILES['resume']['name']);
        $resume_extension = strtolower($fileInfo['extension']);
        $resume_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['resume']['name']);
        $resume_tmp = $_FILES['resume']['tmp_name'];
        $resume_path = "resume_uploads/" . $resume_name;

        if (!in_array($resume_extension, $allowedExtensions)) {
            throw new Exception('Invalid file format. Only PDF, DOC, and DOCX allowed.');
        } elseif ($_FILES['resume']['size'] > 5 * 1024 * 1024) {
            throw new Exception('File size must be under 5MB.');
        } elseif (!move_uploaded_file($resume_tmp, $resume_path)) {
            throw new Exception('Error uploading file.');
        }
    } else {
        throw new Exception('Please upload a valid resume.');
    }

    // ✅ Insert Job Application
    $conn->begin_transaction();
    $stmt = $conn->prepare("INSERT INTO job_applications (job_id, first_name, last_name, email, phone, cover_letter, resume_path, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("issssss", $job_id, $first_name, $last_name, $email, $phone, $cover_letter, $resume_path);
    if (!$stmt->execute()) {
        throw new Exception('Error submitting application: ' . $stmt->error);
    }

    // ✅ Deduct Vacancy & Update Status
    $stmt = $conn->prepare("UPDATE job_listings SET vacancy = vacancy - 1 WHERE id = ? AND vacancy > 0");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();

    $stmt = $conn->prepare("SELECT vacancy FROM job_listings WHERE id = ?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        $updatedJob = $result->fetch_assoc();
        if (intval($updatedJob['vacancy']) === 0) {
            $stmt = $conn->prepare("UPDATE job_listings SET status = 'Inactive' WHERE id = ?");
            $stmt->bind_param("i", $job_id);
            $stmt->execute();
        }
    }

    $conn->commit();
    $response = ['status' => 'success', 'message' => 'Application submitted successfully!'];

} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = $e->getMessage();
}

// ✅ Final JSON Output
echo json_encode($response);
exit;
?>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json'); // Ensure JSON response

include('admin/config.php'); // Include database connection

$response = ['status' => 'error', 'message' => 'Unknown error occurred.']; // Default response

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request.';
    echo json_encode($response);
    exit;
}

// Get form data safely
$job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
$first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$cover_letter = isset($_POST['cover_letter']) ? trim($_POST['cover_letter']) : '';

// Check for required fields
if (!$job_id || empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
    $response['message'] = 'Missing required fields.';
    echo json_encode($response);
    exit;
}

// ✅ Step 1: Check Job Vacancy
$vacancyQuery = "SELECT vacancy, status FROM job_listings WHERE id = ?";
$stmt = $conn->prepare($vacancyQuery);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 0) {
    $response['message'] = 'Job not found.';
    echo json_encode($response);
    exit;
}

$job = $result->fetch_assoc();
$vacancy = intval($job['vacancy']);
$status = $job['status'];

if ($vacancy <= 0) {
    $response['message'] = 'No more vacancies for this job.';
    echo json_encode($response);
    exit;
}

// ✅ Step 2: Handle Resume Upload
$resume_path = null;
if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
    $allowedExtensions = ['pdf', 'doc', 'docx'];
    $fileInfo = pathinfo($_FILES['resume']['name']);
    $resume_extension = strtolower($fileInfo['extension']);
    $resume_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['resume']['name']);
    $resume_tmp = $_FILES['resume']['tmp_name'];
    $resume_path = "resume_uploads/" . $resume_name;

    if (!in_array($resume_extension, $allowedExtensions)) {
        $response['message'] = 'Invalid file format. Only PDF, DOC, and DOCX allowed.';
        echo json_encode($response);
        exit;
    } elseif ($_FILES['resume']['size'] > 5 * 1024 * 1024) {
        $response['message'] = 'File size must be under 5MB.';
        echo json_encode($response);
        exit;
    } elseif (!move_uploaded_file($resume_tmp, $resume_path)) {
        $response['message'] = 'Error uploading file.';
        echo json_encode($response);
        exit;
    }
} else {
    $response['message'] = 'Please upload a valid resume.';
    echo json_encode($response);
    exit;
}

// ✅ Step 3: Insert Application
$conn->begin_transaction(); // Start transaction to ensure atomic operation

$sql = "INSERT INTO job_applications 
        (job_id, first_name, last_name, email, phone, cover_letter, resume_path, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    $response['message'] = "Database error: " . $conn->error;
    echo json_encode($response);
    exit;
}

$stmt->bind_param("issssss", $job_id, $first_name, $last_name, $email, $phone, $cover_letter, $resume_path);

if ($stmt->execute()) {
    // ✅ Step 4: Deduct 1 from Vacancy
    $updateVacancyQuery = "UPDATE job_listings SET vacancy = vacancy - 1 WHERE id = ? AND vacancy > 0";
    $stmt = $conn->prepare($updateVacancyQuery);
    $stmt->bind_param("i", $job_id);
    $stmt->execute();

    // ✅ Step 5: Check if Vacancy is now 0 and Update Status
    $checkVacancyQuery = "SELECT vacancy FROM job_listings WHERE id = ?";
    $stmt = $conn->prepare($checkVacancyQuery);
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        $updatedJob = $result->fetch_assoc();
        if (intval($updatedJob['vacancy']) === 0) {
            // ✅ Update job status to "Inactive"
            $updateStatusQuery = "UPDATE job_listings SET status = 'Inactive' WHERE id = ?";
            $stmt = $conn->prepare($updateStatusQuery);
            $stmt->bind_param("i", $job_id);
            $stmt->execute();
        }
    }

    $conn->commit(); // Commit transaction
    $response['status'] = 'success';
    $response['message'] = 'Application submitted successfully!';
} else {
    $conn->rollback(); // Rollback if insertion fails
    $response['message'] = 'Error submitting application: ' . $stmt->error;
}

// Close database connections
$stmt->close();
$conn->close();

echo json_encode($response);
exit;
?>

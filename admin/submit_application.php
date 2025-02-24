<?php
include('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_id = intval($_POST['job_id']);
    $applicant_name = mysqli_real_escape_string($conn, $_POST['applicant_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $cover_letter = mysqli_real_escape_string($conn, $_POST['cover_letter']);

    // Resume upload handling
    $uploadDir = "resumes/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
    }

    $resume = $_FILES['resume']['name'];
    $resumeTemp = $_FILES['resume']['tmp_name'];
    $resumePath = $uploadDir . basename($resume);

    if (move_uploaded_file($resumeTemp, $resumePath)) {
        $sql = "INSERT INTO applications (job_id, applicant_name, email, phone, address, cover_letter, resume, status) 
                VALUES ('$job_id', '$applicant_name', '$email', '$phone', '$address', '$cover_letter', '$resume', 'Pending')";

        if (mysqli_query($conn, $sql)) {
            echo "<script>
                alert('Application submitted successfully!');
                window.location.href='index.php';
            </script>";
        } else {
            echo "<script>
                alert('Error: " . mysqli_error($conn) . "');
                window.history.back();
            </script>";
        }
    } else {
        echo "<script>
            alert('Error uploading file.');
            window.history.back();
        </script>";
    }
}
?>

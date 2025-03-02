<?php
session_start();
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email_id'];

    // Delete existing OTP for user
    $stmt = $conn->prepare("DELETE FROM otp_verifications WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->close();

    // Call OTP generate script
    include 'otp_generate.php';
}
?>

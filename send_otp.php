<?php
session_start();
include('includes/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM tblemployees WHERE email_id = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $user_id = $row['id'];

        // Generate OTP
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        // Store OTP in database
        $stmt = $conn->prepare("INSERT INTO otp_verification (user_id, otp_code, expires_at) VALUES (?, ?, ?)
                                ON DUPLICATE KEY UPDATE otp_code=?, expires_at=?");
        $stmt->bind_param("issss", $user_id, $otp, $expiry, $otp, $expiry);
        $stmt->execute();

        // Send OTP via email
        $subject = "Your OTP Code";
        $message = "Your OTP code is $otp. It expires in 5 minutes.";
        $headers = "From: no-reply@yourdomain.com";
        mail($email, $subject, $message, $headers);

        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Email not registered"]);
    }
}
?>

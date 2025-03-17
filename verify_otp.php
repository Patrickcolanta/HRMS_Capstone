<?php
session_start();
include('includes/config.php');

// Check if OTP is set
if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_email']) || !isset($_SESSION['otp_expiry'])) {
    echo json_encode(['status' => 'error', 'message' => 'OTP session expired. Please log in again.']);
    exit;
}

// Handle OTP Verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = trim($_POST['otp']);
    $email = $_SESSION['otp_email'];

    // Check if OTP has expired
    if (time() > $_SESSION['otp_expiry']) {
        unset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['otp_email']); // Clear expired OTP
        echo json_encode(['status' => 'error', 'message' => 'OTP expired. Please log in again.']);
        exit;
    }

    // Verify OTP
    if ($entered_otp == $_SESSION['otp']) {
        // Fetch user details from database
        $stmt = $conn->prepare("SELECT * FROM tblemployees WHERE email_id = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            $role = $user['role'];

            // Clear OTP session data
            unset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['otp_email']);

            // Set full authentication session
            $_SESSION['slogin'] = $user['emp_id']; 
            $_SESSION['srole'] = $role;
            $_SESSION['semail'] = $user['email_id'];
            $_SESSION['sfirstname'] = $user['first_name'];
            $_SESSION['slastname'] = $user['last_name'];
            $_SESSION['scontact'] = $user['phone_number'];
            $_SESSION['sdesignation'] = $user['designation'];
            $_SESSION['is_supervisor'] = $user['is_supervisor'];
            $_SESSION['simageurl'] = $user['image_path'];
            $_SESSION['last_activity'] = time();

            // Set redirection based on role
            $redirect_url = "index.php"; // Default redirection
            if ($role == 'Admin' || $role == 'Manager') {
                $redirect_url = "admin/index.php";
            } elseif ($role == 'Staff') {
                $redirect_url = "staff/index.php";
            }

            // Return response for JavaScript handling
            echo json_encode([
                'status' => 'success',
                'message' => "Welcome, " . $user['first_name'] . "! You are logged in as " . $role . ".",
                'firstname' => $user['first_name'],
                'role' => $role,
                'redirect' => $redirect_url
            ]);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User role not found. Contact admin.']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid OTP. Please try again.']);
        exit;
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <title>HRMS</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="#">
    <meta name="keywords" content="Admin, Responsive, Landing, Bootstrap, App">
    <meta name="author" content="#">
    <link rel="icon" href="./files/assets/images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,800" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="./files/bower_components/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="./files/assets/icon/themify-icons/themify-icons.css">
    <link rel="stylesheet" type="text/css" href="./files/assets/css/style.css">
</head>
<body class="fix-menu">
    <section class="login-block">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <form id="otp-form" method="POST" class="md-float-material form-material">
                        <div class="auth-box card">
                            <div class="card-block">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3 class="text-center"><i class="feather icon-mail text-primary f-60 p-t-15 p-b-20 d-block"></i></h3>
                                        <h4 class="text-center">Enter OTP</h4>
                                        <p class="text-center text-muted">Type in the 6-digit pin code we sent to your email.</p>
                                    </div>
                                </div>
                                <p class="text-center text-muted">
    Didn't receive the OTP? <a href="#" id="resend-otp" class="text-primary">Resend OTP</a>
</p>
<p id="resend-timer" class="text-center text-danger" style="display:none;"></p>
                                <div class="form-group form-primary">
    <input type="text" id="otp" name="otp" class="form-control" required 
           placeholder="Enter your 6-digit pin code" 
           maxlength="6" pattern="\d{6}" 
           oninput="this.value = this.value.replace(/\D/g, '').slice(0, 6)">
    <span class="form-bar"></span>
</div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary btn-md btn-block waves-effect text-center m-b-20">
                                            <i class="icofont icofont-email"></i> VERIFY
                                        </button>
                                    </div>
                                </div>
                                <p class="text-inverse text-right"><a href="index.php">Back to Login</a></p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function() {
        let canResend = true;

        $("#otp-form").submit(function(event) {
            event.preventDefault();
            let otp = $("#otp").val().trim();
            
            if (otp === '') {
                Swal.fire({
                    icon: 'warning',
                    text: 'Please enter the OTP',
                    confirmButtonColor: '#ffc107',
                    confirmButtonText: 'OK'
                });
                return;
            }

            $.ajax({
                url: "verify_otp.php",
                type: "POST",
                data: { otp: otp },
                dataType: "json",
                success: function(response) {
                    if (response.status === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            confirmButtonColor: '#01a9ac',
                            confirmButtonText: 'Proceed'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = response.redirect;
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            text: response.message,
                            confirmButtonColor: '#eb3422',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        text: 'Something went wrong! Please try again.',
                        confirmButtonColor: '#eb3422',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        // Handle Resend OTP
        $("#resend-otp").click(function(event) {
            event.preventDefault();

            if (!canResend) return;

            canResend = false;
            let timer = 60; 
            $("#resend-timer").text(`Please wait ${timer} seconds before requesting a new OTP.`).show();
            $("#resend-otp").hide();

            let countdown = setInterval(() => {
                timer--;
                $("#resend-timer").text(`Please wait ${timer} seconds before requesting a new OTP.`);
                if (timer <= 0) {
                    clearInterval(countdown);
                    $("#resend-otp").show();
                    $("#resend-timer").hide();
                    canResend = true;
                }
            }, 1000);

            // Extract email from URL
            let urlParams = new URLSearchParams(window.location.search);
            let email = urlParams.get('email'); // Get email from URL

            console.log("Extracted Email:", email); 

            if (!email) {
                Swal.fire({
                    icon: 'error',
                    text: 'Email not found in URL!',
                    confirmButtonColor: '#eb3422',
                    confirmButtonText: 'OK'
                });
                return;
            }

            $.ajax({
                url: "resend_otp.php",
                type: "POST",
                data: { email: email },  // Send email as POST data
                dataType: "json",
                success: function(response) {
                    if (response.status === "success") {
                        Swal.fire({
                            icon: 'success',
                            text: 'A new OTP has been sent to your email.',
                            confirmButtonColor: '#01a9ac',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            text: response.message,
                            confirmButtonColor: '#eb3422',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, status, error) {
                console.error("AJAX Error:", error); // Log AJAX error
                console.error("XHR Response:", xhr.responseText); // Log full server response
                Swal.fire({
                    icon: 'error',
                    text: 'Something went wrong! Check console for details.',
                    confirmButtonColor: '#eb3422',
                    confirmButtonText: 'OK'
                });
                }
            });
        });

    });
</script>
</body>
</html>

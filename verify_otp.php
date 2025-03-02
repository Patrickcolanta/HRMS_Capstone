<!DOCTYPE html>
<html lang="en">
<head>
    <title>HRMS - Forgot Password</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="#">
    <meta name="keywords" content="Admin , Responsive, Landing, Bootstrap, App">
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
                    <form method="POST" id="forgot-password-form" class="md-float-material form-material">
                        <div class="auth-box card">
                            <div class="card-block">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3 class="text-center"><i class="feather icon-mail text-primary f-60 p-t-15 p-b-20 d-block"></i></h3>
                                        <h4 class="text-center">Enter OTP</h4>
                                        <p class="text-center text-muted">Type in the 6 digits pin code we sent to your email.</p>
                                    </div>
                                </div>
                                <div class="form-group form-primary">
                                    <input type="email" id="email" name="email" class="form-control" required placeholder="Enter your 6 digits pin code">
                                    <span class="form-bar"></span>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary btn-md btn-block waves-effect text-center m-b-20">
                                            <i class="icofont icofont-email"></i> VERIFY
                                        </button>
                                    </div>
                                </div>
                                <p class="text-inverse text-right"> <a href="index.php">Back to Login</a></p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <script type="text/javascript" src="./files/bower_components/jquery/js/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#forgot-password-form').submit(function(event) {
                event.preventDefault();
                var email = $('#email').val().trim();

                if (email === '') {
                    Swal.fire({
                        icon: 'warning',
                        text: 'Please enter your email',
                        confirmButtonColor: '#ffc107',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                
                $.ajax({
                    url: 'forgot_password_function.php',
                    type: 'POST',
                    data: { email: email },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Email Sent!',
                                text: response.message,
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
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>HRMS </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="#">
    <meta name="keywords" content="Admin , Responsive, Landing, Bootstrap, App, Template, Mobile, iOS, Android, apple, creative app">
    <meta name="author" content="#">
    <!-- Favicon icon -->
    <link rel="icon" href=".\files\assets\images\favicon.ico" type="image/x-icon">
    <!-- Google font--><link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,800" rel="stylesheet">
    <!-- Required Fremwork -->
    <link rel="stylesheet" type="text/css" href=".\files\bower_components\bootstrap\css\bootstrap.min.css">
    <!-- themify-icons line icon -->
    <link rel="stylesheet" type="text/css" href=".\files\assets\icon\themify-icons\themify-icons.css">
    <link rel="stylesheet" type="text/css" href=".\files\assets\icon\feather\css\feather.css">
    <!-- ico font -->
    <link rel="stylesheet" type="text/css" href=".\files\assets\icon\icofont\css\icofont.css">
    <!-- Style.css -->
    <link rel="stylesheet" type="text/css" href=".\files\assets\css\style.css">
</head>

<?php include('includes/config.php'); ?>


<body class="fix-menu">
    <!-- Pre-loader start -->
    <?php include('includes/loader.php')?>
    <!-- Pre-loader end -->

    <section class="login-block">
        <!-- Container-fluid starts -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <!-- Login card start -->
                    <form method="POST" class="md-float-material form-material">
                        <div class="auth-box card">
                            <div class="card-block">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3 class="text-center"><i class="feather icon-lock text-primary f-60 p-t-15 p-b-20 d-block"></i></h3>
                                    </div>
                                </div>
                                <div class="alert alert-info text-center" role="alert">
                                    Password must meet the following requirements:
                                    <ul class="text-left">
                                        <li>At least <strong>16 characters</strong></li>
                                        <li>At least <strong>one uppercase letter</strong> (A-Z)</li>
                                        <li>At least <strong>one lowercase letter</strong> (a-z)</li>
                                        <li>At least <strong>one number</strong> (0-9)</li>
                                        <li>At least <strong>one special character</strong> (@$!%*?&)</li>
                                    </ul>
                                </div>
                                <div class="form-group form-primary">
                                   <input type="password" id="new_password" name="new_password" class="form-control password" required="" placeholder="New Password">
                                    <span class="form-bar"></span>
                                </div>
                                <div class="form-group form-primary">
                                   <input type="password" id="confirm_password" name="confirm_password" class="form-control password" required="" placeholder="Confirm Password">
                                    <span class="form-bar"></span>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <button id="reset-password" type="submit" class="btn btn-primary btn-md btn-block waves-effect text-center m-b-20"><i class="icofont icofont-lock"></i> Reset Password </button>
                                    </div>
                                </div>
                                
                                <p class="text-inverse text-right"> <a href="index.php"> Back to Login</a></p>
                                
                            </div>
                        </div>
                    </form>
                    <!-- Login card end -->
                </div>
                <!-- end of col-sm-12 -->
            </div>
            <!-- end of row -->
        </div>
        <!-- end of container-fluid -->
    </section>
    <!-- Required Jquery -->
    <script type="text/javascript" src=".\files\bower_components\jquery\js\jquery.min.js"></script>
    <script type="text/javascript" src=".\files\bower_components\jquery-ui\js\jquery-ui.min.js"></script>
    <script type="text/javascript" src=".\files\bower_components\popper.js\js\popper.min.js"></script>
    <script type="text/javascript" src=".\files\bower_components\bootstrap\js\bootstrap.min.js"></script>
    <!-- jquery slimscroll js -->
    <script type="text/javascript" src=".\files\bower_components\jquery-slimscroll\js\jquery.slimscroll.js"></script>
    <!-- modernizr js -->
    <script type="text/javascript" src=".\files\bower_components\modernizr\js\modernizr.js"></script>
    <script type="text/javascript" src=".\files\bower_components\modernizr\js\css-scrollbars.js"></script>
    <!-- i18next.min.js -->
    <script type="text/javascript" src=".\files\bower_components\i18next\js\i18next.min.js"></script>
    <script type="text/javascript" src=".\files\bower_components\i18next-xhr-backend\js\i18nextXHRBackend.min.js"></script>
    <script type="text/javascript" src=".\files\bower_components\i18next-browser-languagedetector\js\i18nextBrowserLanguageDetector.min.js"></script>
    <script type="text/javascript" src=".\files\bower_components\jquery-i18next\js\jquery-i18next.min.js"></script>
    <!--Color Script Common-->
    <script type="text/javascript" src=".\files\assets\js\common-pages.js"></script>

    <script src="https://code.jquery.com/jquery-1.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.min.css"></script>


<!-- Global site tag (gtag.js) - Google Analytics -->
    <script async="" src="https://www.googletagmanager.com/gtag/js?id=UA-23581568-13"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-23581568-13');
    </script>
    
    <script type="text/javascript">
$(document).ready(function() {
    $('#reset-password').click(function(event) {
        event.preventDefault(); // Prevent form submission
        
        var newPassword = $('#new_password').val().trim();
        var confirmPassword = $('#confirm_password').val().trim();
        
        // ✅ Extract Token from URL
        var urlParams = new URLSearchParams(window.location.search);
        var token = urlParams.get('token'); // Fetches 'token' from URL

        if (!token) {
            Swal.fire({
                icon: 'error',
                text: 'Invalid or missing token',
                confirmButtonColor: '#eb3422',
                confirmButtonText: 'OK'
            });
            return;
        }

        // ✅ Strong Password Validation
        var passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

        if (newPassword === '' || confirmPassword === '') {
            Swal.fire({
                icon: 'warning',
                text: 'Please fill in all fields',
                confirmButtonColor: '#ffc107',
                confirmButtonText: 'OK'
            });
            return;
        }

        if (newPassword !== confirmPassword) {
            Swal.fire({
                icon: 'error',
                text: 'Passwords do not match',
                confirmButtonColor: '#eb3422',
                confirmButtonText: 'OK'
            });
            return;
        }

        if (!passwordPattern.test(newPassword)) {
            Swal.fire({
                icon: 'warning',
                html: `
                    Password must meet the following requirements:<br>
                    <ul style="text-align: left; margin-top: 10px;">
                        <li>At least <strong>16 characters</strong></li>
                        <li>At least <strong>one uppercase letter</strong> (A-Z)</li>
                        <li>At least <strong>one lowercase letter</strong> (a-z)</li>
                        <li>At least <strong>one number</strong> (0-9)</li>
                        <li>At least <strong>one special character</strong> (@$!%*?&)</li>
                    </ul>
                `,
                confirmButtonColor: '#ffc107',
                confirmButtonText: 'OK'
            });
            return;
        }

        // ✅ Send AJAX Request with Token in POST data
        $.ajax({
            url: 'reset_pass_function.php', // ❌ Removed token from URL
            type: 'POST',
            data: {
                token: token, // ✅ Token sent via POST
                new_password: newPassword,
                confirm_password: confirmPassword,
                action: "reset_password"
            },
            dataType: 'json',
            success: function(response) {
                console.log(response.message);

                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: response.message,
                        confirmButtonColor: '#01a9ac',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location = 'index.php'; // Redirect to login page
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
            error: function(jqXHR, textStatus, errorThrown) {
                console.log("AJAX error: " + textStatus + ' : ' + errorThrown);
            }
        });
    });
});
</script>


</body>

</html>

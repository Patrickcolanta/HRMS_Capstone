<?php
include('../admin/config.php'); // Database connection
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charlex International Corporation</title>

    <!-- Style.css -->
    <link rel="stylesheet" type="text/css" href=".\files\assets\css\style.css">

    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,800" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body style="background-color: #f8f9fa;" class="d-flex flex-column h-100">


    <nav class="navbar navbar-expand-lg navbar-light bg-light" style="position: absolute; top: 0; left: 0; width: 100%; z-index: 1000;">
        <div class="container">
            <a class="navbar-brand font-weight-bold" href="#">MySite</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Apartments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Jobs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contact</a>
                    </li>

                </ul>
            </div>

            <div>
                <a href="../index.php" class="btn btn-outline-dark">Login</a>
                <a href="../index.php" class="btn btn-dark">Get Started</a>
            </div>

        </div>
    </nav>



   
 
    <div class="position-relative h-50 text-white">
        <!-- Image with Black Gradient Overlay -->
        <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark" style="opacity: 0.5;"></div>

        <!-- Background Image -->
        <img class="cover  w-100 rounded" src="https://images.pexels.com/photos/1134176/pexels-photo-1134176.jpeg?cs=srgb&dl=dug-out-pool-hotel-poolside-1134176.jpg&fm=jpg" alt="">

        <div class="position-absolute top-50 start-50 translate-middle text-center">
            <h1 class="display-4 fw-bold">Find Your Dream Apartment & Career</h1>
            <p class="lead">Discover beautiful apartments for rent and exciting job opportunities all in one place.</p>
            <a href="#" class="btn btn-dark me-3">Browse Apartments</a>
            <a href="#" class="btn btn-outline-light">View Jobs</a>
        </div>

    </div>

    <div class="bg-light py-4">
        <div class="container">
            <div class="bg-white p-4 rounded shadow-sm">
                <div class="row">
                    <div class="col-12 col-md-4">
                        <label for="name" class="form-label"><strong>Location</strong></label>
                        <input type="text" id="name" class="form-control" placeholder="City or ZIP code">
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="name" class="form-label"><strong>Price Range</strong></label>
                        <input type="text" id="name" class="form-control" placeholder="City or ZIP code">
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="search" class="form-label"><strong>Search</strong></label>
                        <input type="search" id="search" class="form-control" placeholder="Search here...">
                    </div>


                </div>


            </div>

        </div>

    </div>

    <div class="py-5 bg-white">

        <div class="container">
            <h2 class="text-center mb-2"><strong>Available Apartments</strong></h2>
            <p class="text-center text-muted">Explore our selection of premium apartments available for rent in prime locations.</p>

            <div class="row g-4 mt-4">
                <div class="col-md-4 col-sm-12">
                    <div class="card shadow-sm">
                        <div class="position-relative">
                            <img src="https://images.ctfassets.net/pg6xj64qk0kh/2r4QaBLvhQFH1mPGljSdR9/39b737d93854060282f6b4a9b9893202/camden-paces-apartments-buckhead-ga-terraces-living-room-with-den_1.jpg" class="card-img-top" alt="Apartment Image">
                            <span class="badge bg-dark position-absolute top-0 end-0 m-2">New</span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Modern Studio Apartment</h5>
                            <p class="text-muted"><i class="bi bi-geo-alt"></i> Downtown, City 1</p>
                            <h6 class="fw-bold">$1100/month</h6>
                            <p class="text-muted">
                                <i class="bi bi-bed"></i> 1
                                <i class="bi bi-bath ms-2"></i> 1
                                <i class="bi bi-arrows-fullscreen ms-2"></i> 700 ft²
                            </p>
                            <a href="#" class="btn btn-outline-dark w-100">View Details</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-12">
                    <div class="card shadow-sm">
                        <div class="position-relative">
                            <img src="https://images.ctfassets.net/pg6xj64qk0kh/2r4QaBLvhQFH1mPGljSdR9/39b737d93854060282f6b4a9b9893202/camden-paces-apartments-buckhead-ga-terraces-living-room-with-den_1.jpg" class="card-img-top" alt="Apartment Image">
                            <span class="badge bg-dark position-absolute top-0 end-0 m-2">New</span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Modern Studio Apartment</h5>
                            <p class="text-muted"><i class="bi bi-geo-alt"></i> Downtown, City 1</p>
                            <h6 class="fw-bold">$1100/month</h6>
                            <p class="text-muted">
                                <i class="bi bi-bed"></i> 1
                                <i class="bi bi-bath ms-2"></i> 1
                                <i class="bi bi-arrows-fullscreen ms-2"></i> 700 ft²
                            </p>
                            <a href="#" class="btn btn-outline-dark w-100">View Details</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-12">
                    <div class="card shadow-sm">
                        <div class="position-relative">
                            <img src="https://images.ctfassets.net/pg6xj64qk0kh/2r4QaBLvhQFH1mPGljSdR9/39b737d93854060282f6b4a9b9893202/camden-paces-apartments-buckhead-ga-terraces-living-room-with-den_1.jpg" class="card-img-top" alt="Apartment Image">
                            <span class="badge bg-dark position-absolute top-0 end-0 m-2">New</span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Modern Studio Apartment</h5>
                            <p class="text-muted"><i class="bi bi-geo-alt"></i> Downtown, City 1</p>
                            <h6 class="fw-bold">$1100/month</h6>
                            <p class="text-muted">
                                <i class="bi bi-bed"></i> 1
                                <i class="bi bi-bath ms-2"></i> 1
                                <i class="bi bi-arrows-fullscreen ms-2"></i> 700 ft²
                            </p>
                            <a href="#" class="btn btn-outline-dark w-100">View Details</a>
                        </div>
                    </div>
                </div>

            </div>

            <div class="text-center mt-4">
                <a href="#" class="btn btn-outline-dark">View All Apartments <i class="bi bi-arrow-right"></i></a>
            </div>


        </div>




    </div>

    <div class="py-5 bg-light">

        <div class="container">
            <h2 class="text-center mb-2"><strong>Why Chohttps://github.com/Patrickcolanta/HRMS_Capstone.gitose Us</strong></h2>
            <p class="text-center text-muted">We offer more than just a place to live - we provide a complete lifestyle experience.</p>


            <div class="row g-4 mt-4">
                <div class="col-md-3 col-sm-12">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mt-2">Prime Locations</h5>
                            <p class="text-muted">All our properties are situated in the most desirable neighborhoods with easy access to amenities.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-12">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mt-2">Modern Amenities</h5>
                            <p class="text-muted">Enjoy state-of-the-art facilities including gyms, pools, and community spaces.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-12">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mt-2">24/7 Maintenance</h5>
                            <p class="text-muted">Our dedicated team is always available to address any maintenance issues promptly.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-12">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mt-2">Flexible Leases</h5>
                            <p class="text-muted">Choose from various lease terms that suit your lifestyle and requirements.</p>
                        </div>
                    </div>
                </div>
            </div>


        </div>

    </div>


    <div class="py-5 bg-white">

        <div class="container">

            <h2 class="text-center mb-2"><strong>Available Jobs</strong></h2>
            <p class="text-center text-muted">Find exciting career opportunities in our properties and partner companies.</p>

            <div class="row mt-5">
                <?php
                $sql = "SELECT id, job_title, description, vacancy, job_type, salary, location, created_at 
        FROM job_listings 
        WHERE status = 'Active' 
        ORDER BY created_at DESC";

                $result = mysqli_query($conn, $sql);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $short_description = substr($row['description'], 0, 100) . "..."; // Limit to 100 characters

                        // Ensure that salary is a number, even if it is an empty string or invalid value
                        $salary = floatval($row['salary']);

                        echo "<div class='col-md-4 d-flex'>
                <div class='card mb-3 shadow-lg flex-fill'>
                    <div class='card-body d-flex flex-column'>
                        <h5 class='card-title text-primary'>{$row['job_title']}</h5>
                        <p class='text-muted'><small>Posted on: " . date("F j, Y", strtotime($row['created_at'])) . "</small></p>
                        <p class='card-text text-truncate'><strong>Description:</strong> $short_description</p>
                        <p><strong>Vacancy:</strong> {$row['vacancy']}</p>
                        <p><strong>Type:</strong> <span class='badge bg-success'>{$row['job_type']}</span></p>
                        <p><strong>Salary:</strong> $" . number_format($salary, 2) . "</p>
                        <p><strong>Location:</strong> {$row['location']}</p>
                        <div class='mt-auto'>
                            <a href='job_details.php?id={$row['id']}' class='btn btn-outline-primary w-100'>View Details</a>
                        </div>
                    </div>
                </div>
              </div>";
                    }
                } else {
                    echo "<p class='text-center text-muted'>No job listings available.</p>";
                }
                ?>





            </div>

            <div class="text-center mt-4">
                <a href="#" class="btn btn-outline-dark">View All Jobs <i class="bi bi-arrow-right"></i></a>
            </div>


        </div>
    </div>


    <!-- contact us -->

    <div class="py-5 bg-light">

        <div class="container">
            <h2 class="text-center mb-2"><strong>Contact Us</strong></h2>
            <p class="text-center text-muted">Get in touch with us for any inquiries or assistance.</p>


            <div class="card shadow-sm mt-5">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-center">Send Us a Message</h5>
                    <form>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" id="name" class="form-control" placeholder="Enter your name">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" class="form-control" placeholder="Enter your email">
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea id="message" class="form-control" rows="5" placeholder="Enter your message"></textarea>
                        </div>
                        <button type="submit" class="btn btn-dark w-100">Send Message</button>

                    </form>

                </div>
            </div>

        </div>
    </div>


    <!-- CTA -->

    <div class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h2 class="fw-bold">Subscribe to Our Newsletter</h2>
                    <p>Stay updated with the latest news, promotions, and offers by subscribing to our newsletter.</p>
                </div>
                <div class="col-md-6">
                    <form>
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" placeholder="Enter your email">
                            <button class="btn btn-primary" type="submit">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>






    <div class="m-4">
        <footer class="py-3 my-4">
            <ul class="nav justify-content-center border-bottom pb-3 mb-3">
                <li class="nav-item"><a href="#" class="nav-link px-2 text-muted">Home</a></li>
                <li class="nav-item"><a href="#" class="nav-link px-2 text-muted">Features</a></li>
                <li class="nav-item"><a href="#" class="nav-link px-2 text-muted">Pricing</a></li>
                <li class="nav-item"><a href="#" class="nav-link px-2 text-muted">FAQs</a></li>
                <li class="nav-item"><a href="#" class="nav-link px-2 text-muted">About</a></li>
            </ul>
            <p class="text-center text-muted">© 2021 Company, Inc</p>
        </footer>
    </div>

</body>

</html>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
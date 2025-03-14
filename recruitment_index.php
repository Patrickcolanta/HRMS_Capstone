<?php
include('admin/config.php'); // Database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charlex International Corporation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body style="background-color: #f8f9fa;">



<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="#">MySite</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="#">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Contact</a>
                </li>
            </ul>
            <!-- Login Button on the Right -->
            <a href="index.php" class="btn btn-primary ms-auto">Login</a>

        </div>
    </div>
</nav>


<div class="position-relative m-4">
    <!-- Image with Black Gradient Overlay -->
    <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark" style="opacity: 0.5;"></div>

    <!-- Background Image -->
    <img class="cover w-100 rounded" src="https://images.pexels.com/photos/1134176/pexels-photo-1134176.jpeg?cs=srgb&dl=dug-out-pool-hotel-poolside-1134176.jpg&fm=jpg" alt="">

    <!-- Text Content -->
    <div class="position-absolute top-50 start-50 translate-middle text-center">
        <h1 class="fs-1 text-white fw-bold">Rent an Island Platform</h1>
    </div>
</div>


<div class="m-4">
<div class="row row-cols-4">

    <div class="col">
      <img class="w-100 bg-warning"  src="https://legaltemplates.net/wp-content/uploads/2018/11/renting-a-room-in-a-house.jpg" alt="">
      
        <p  class="p-2 bg-white">Apartment & Units</p>
    </div>

    <div class="col">
      <img class="w-100 bg-warning"  src="https://legaltemplates.net/wp-content/uploads/2018/11/renting-a-room-in-a-house.jpg" alt="">
      
        <p  class="p-2 bg-white">Apartment & Units</p>
    </div>

    <div class="col">
      <img class="w-100 bg-warning"  src="https://legaltemplates.net/wp-content/uploads/2018/11/renting-a-room-in-a-house.jpg" alt="">
      
        <p  class="p-2 bg-white">Apartment & Units</p>
    </div>

    <div class="col">
      <img class="w-100 bg-warning"  src="https://legaltemplates.net/wp-content/uploads/2018/11/renting-a-room-in-a-house.jpg" alt="">
      
        <p  class="p-2 bg-white">Apartment & Units</p>
    </div>

  
   
   
  </div>
</div>

<div class="m-4 d-flex align-items-center justify-content-between gap-4 bg-white h-100 py-4 px-2">

    <div class="h-100 w-100 d-flex flex-column align-items-start justify-content-center ps-4">
        <h1>Sample Title</h1>
        <p>Sample Description</p>        
    </div>

    <div class="bg-warning h-100 w-100">
        <img src="" alt="">
    </div>

</div>


<div class="mx-4 my-5 d-flex flex-column align-items-center justify-content-center gap-3">

    <div class=" text-center">
      <h1>Sample Title</h1>
      <p>Sample Description</p>
    </div>

    <div class="row w-100">

        <div class="col">
         <img class="w-100 h-100" src="https://cdn.pixabay.com/photo/2023/01/08/14/22/sample-7705350_640.jpg" alt="">  
        </div>
    

       <div class="col d-flex flex-column align-content-center justify-content-between gap-2">

          <div class="rounded p-2 h-100 bg-white shadow-sm">
            <p>Title</p>
            <p>Subtitle</p>
          </div>

          <div class="rounded p-2 h-100 bg-white shadow-sm">
            <p>Title</p>
            <p>Subtitle</p>
          </div>

          <div class="rounded p-2 h-100 bg-white shadow-sm">
            <p>Title</p>
            <p>Subtitle</p>
          </div>

       </div>
    </div>

</div>


<div class="m-4">
    <h2 class="text-center mb-5">Available Jobs</h2>

    <div class="row">
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

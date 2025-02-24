<?php
include('config.php'); // Database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">Available Jobs</h2>

    <div class="row">
        <?php
        $sql = "SELECT j.id, j.job_title, d.name AS department, j.job_type, j.created_at 
                FROM job_listings j 
                LEFT JOIN department d ON j.department_id = d.id 
                ORDER BY j.created_at DESC";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class='col-md-4'>
                        <div class='card mb-3 p-3'>
                            <h4>{$row['job_title']}</h4>
                            <p><strong>Department:</strong> {$row['department']}</p>
                            <p><strong>Type:</strong> {$row['job_type']}</p>
                            <a href='job_details.php?id={$row['id']}' class='btn btn-primary'>View Details</a>
                        </div>
                      </div>";
            }
        } else {
            echo "<p class='text-center'>No job listings available.</p>";
        }
        ?>
    </div>
</div>

</body>
</html>

<?php
session_start();
include('../includes/header.php');
include('../includes/config.php'); // Database connection

// Allow only Admin, Manager, or HR employees
if ($_SESSION['srole'] !== 'Admin' && $_SESSION['srole'] !== 'Manager' && $_SESSION['sdepartment'] !== 'Human Resources') {
    header("Location: index.php");
    exit();
}
$userRole = $_SESSION['srole'];



// Fetch leave request breakdown by status (Pending, Approved, Rejected)
$leaveQuery = "SELECT leave_status, COUNT(*) as count FROM tblleave GROUP BY leave_status";
$leaveResult = mysqli_query($conn, $leaveQuery);
$leaveData = ["Pending" => 0, "Approved" => 0, "Rejected" => 0];
while ($row = mysqli_fetch_assoc($leaveResult)) {
    $leaveData[$row['leave_status']] = $row['count'];
}

// Fetch leave request breakdown by type using JOIN with tblleavetype
$leaveTypeQuery = "
    SELECT lt.leave_type, COUNT(l.id) AS count 
    FROM tblleave l 
    JOIN tblleavetype lt ON l.leave_type_id = lt.id 
    GROUP BY lt.leave_type
";
$leaveTypeResult = mysqli_query($conn, $leaveTypeQuery);
$leaveTypeData = [];
while ($row = mysqli_fetch_assoc($leaveTypeResult)) {
    $leaveTypeData[$row['leave_type']] = $row['count'];
}
$leaveLabels = json_encode(array_keys($leaveTypeData));
$leaveValues = json_encode(array_values($leaveTypeData));

// Fetch task status breakdown (Pending, In Progress, Completed)
$taskQuery = "SELECT status, COUNT(*) as count FROM tbltask GROUP BY status";
$taskResult = mysqli_query($conn, $taskQuery);
$taskData = ["Pending" => 0, "In Progress" => 0, "Completed" => 0];
while ($row = mysqli_fetch_assoc($taskResult)) {
    $taskData[$row['status']] = $row['count'];
}

// Fetch task priority breakdown (Low, Medium, High)
$taskPriorityQuery = "SELECT priority, COUNT(*) as count FROM tbltask GROUP BY priority";
$taskPriorityResult = mysqli_query($conn, $taskPriorityQuery);
$taskPriority = ["Low" => 0, "Medium" => 0, "High" => 0]; // Default values
while ($row = mysqli_fetch_assoc($taskPriorityResult)) {
    $taskPriority[$row['priority']] = $row['count'];
}

// Fetch leave trends over time (monthly)
$leaveTrendQuery = "SELECT MONTH(from_date) as month, COUNT(*) as count FROM tblleave GROUP BY MONTH(from_date)";
$leaveTrendResult = mysqli_query($conn, $leaveTrendQuery);
$leaveTrends = [];
$months = [];
while ($row = mysqli_fetch_assoc($leaveTrendResult)) {
    $months[] = date("F", mktime(0, 0, 0, $row['month'], 1)); // Convert month number to name
    $leaveTrends[] = $row['count'];
}

// Fetch task trends over time (monthly)
$taskTrendQuery = "SELECT MONTH(created_at) as month, COUNT(*) as count FROM tbltask GROUP BY MONTH(created_at)";
$taskTrendResult = mysqli_query($conn, $taskTrendQuery);
$taskTrends = [];
while ($row = mysqli_fetch_assoc($taskTrendResult)) {
    $taskTrends[] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRMS Analytics</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include('../includes/loader.php'); ?>
    <div id="pcoded" class="pcoded">
        <div class="pcoded-container navbar-wrapper">
            <?php include('../includes/topbar.php'); ?>
            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php $page_name = "Analytics"; ?>
                    <?php include('../includes/sidebar.php'); ?>
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body">
                                <div class="page-wrapper">
                                <h2 class="text-center">HRMS Analytics Dashboard</h2>
                              
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="card text-center p-3">
                                            <h5>Leave Trends Over Time</h5>
                                            <canvas id="leaveTrendChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card text-center p-3">
                                            <h5>Task Progress Over Time</h5>
                                            <canvas id="taskProgressChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="card text-center p-3">
                                            <h5>Leave Requests</h5>
                                            <canvas id="leaveChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card text-center p-3">
                                            <h5>Task Status</h5>
                                            <canvas id="taskChart"></canvas>
                                        </div>
                                    </div>
                                </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    $(document).ready(function () {
        // Leave Chart (Dynamically fetches leave types)
        var ctx1 = document.getElementById('leaveChart').getContext('2d');
        var leaveChart = new Chart(ctx1, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_keys($leaveTypeData)); ?>, // Fetch dynamic leave types
                datasets: [{
                    data: <?php echo json_encode(array_values($leaveTypeData)); ?>, // Fetch corresponding counts
                    backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#9966ff', '#ff9f40'] // Dynamic colors
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Task Priority Chart
        var ctx2 = document.getElementById('taskChart').getContext('2d');
        var taskChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: ['Low', 'Medium', 'High'],
                datasets: [{
                    data: [
                        <?php echo $taskPriority["Low"] ?? 0; ?>, 
                        <?php echo $taskPriority["Medium"] ?? 0; ?>, 
                        <?php echo $taskPriority["High"] ?? 0; ?>
                    ],
                    backgroundColor: ['#4caf50', '#ffeb3b', '#f44336']
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Leave Trends Over Time
        var ctx3 = document.getElementById('leaveTrendChart').getContext('2d');
        var leaveTrendChart = new Chart(ctx3, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Leave Requests',
                    data: <?php echo json_encode($leaveTrends); ?>,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.2)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });

        // Task Progress Over Time
        var ctx4 = document.getElementById('taskProgressChart').getContext('2d');
        var taskProgressChart = new Chart(ctx4, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Completed Tasks',
                    data: <?php echo json_encode($taskTrends); ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });
    });
</script>

</body>
</html>
                                    <?php include('../includes/scripts.php'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

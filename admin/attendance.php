<?php include('../includes/header.php')?>
<?php
// Check if the user is logged in
if (!isset($_SESSION['slogin']) || !isset($_SESSION['srole'])) {
    header('Location: ../index.php');
    exit();
}

// Check if the user has the role of Manager or Admin
$userRole = $_SESSION['srole'];
if ($userRole !== 'HR' && $userRole !== 'Admin') {
    header('Location: ../index.php');
    exit();
}
?>
<body>
<!-- Pre-loader start -->
<?php include('../includes/loader.php')?>
<!-- Pre-loader end -->
<div id="pcoded" class="pcoded">
    <div class="pcoded-overlay-box"></div>
    <div class="pcoded-container navbar-wrapper">

        <?php include('../includes/topbar.php')?>

        <div class="pcoded-main-container">
            <div class="pcoded-wrapper">
                 <?php $page_name = "attendance"; ?>
                <?php include('../includes/sidebar.php')?>

                <div class="pcoded-content">
                    <div class="pcoded-inner-content">
                        <!-- Main-body start -->
                        <div class="main-body">
                            <div class="page-wrapper">
                                <!-- Page-header start -->
                                <div class="page-header">
                                    <div class="row align-items-end">
                                        <div class="col-lg-8">
                                            <div class="page-header-title">
                                                <div class="d-inline">
                                                    <h4>Attendance</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Page-header end -->

                                <!-- Page-body start -->
                                <div class="page-body">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <!-- tab content start -->
                                            <div class="tab-content">
                                                <!-- tab pane contact start -->
                                                <div class="tab-pane active" id="contacts" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-xl-12">
                                                            <div class="row">
                                                                <div class="col-sm-12">
                                                                    <!-- contact data table card start -->
                                                                    <?php
    $fromDate = $_GET['from_date'] ?? null;
    $toDate = $_GET['to_date'] ?? null;

    // Build the SQL query with optional date filtering
    $query = "SELECT a.date, a.staff_id, 
                    e.first_name, e.middle_name, e.last_name, a.attendance_id,
                    a.time_in, a.time_out 
            FROM tblattendance a
            JOIN tblemployees e ON a.staff_id = e.staff_id
            WHERE a.is_archived = 0";

    if ($fromDate && $toDate) {
        $query .= " AND a.date BETWEEN ? AND ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $fromDate, $toDate);
    } else {
        $stmt = mysqli_prepare($conn, $query);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
?>

<!-- ✅ Date Filter and Export Form -->
<form method="GET" class="mb-3">
    <label>From: <input type="date" name="from_date" value="<?php echo htmlspecialchars($fromDate); ?>" required></label>
    <label>To: <input type="date" name="to_date" value="<?php echo htmlspecialchars($toDate); ?>" required></label>
    <button type="submit" class="btn btn-info">Filter</button>
    <a href="export_excel.php?from_date=<?php echo $fromDate; ?>&to_date=<?php echo $toDate; ?>" target="_blank" class="btn btn-success">
        <i class="icofont icofont-file-excel"></i> Export to PDF
    </a>
</form>

<!-- ✅ Existing Attendance Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-header-text">Attendance Records</h5>
    </div>
    <div class="card-block contact-details">
        <div class="data_table_main table-responsive dt-responsive">
            <table id="simpletable" class="table  table-striped table-bordered nowrap">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Staff ID</th>
                        <th>Full Name</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Total Hours</th>
                        <th>Status(In/Out)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <?php
                            $time_in = new DateTime($row['time_in']);
                            $time_out = $row['time_out'] ? new DateTime($row['time_out']) : null;
                            if ($time_out) {
                                $interval = $time_in->diff($time_out);
                                $hours = $interval->h;
                                $minutes = $interval->i;
                                $seconds = $interval->s;
                                $total_hours = '';
                                if ($hours > 0) $total_hours .= $hours . ' hr' . ($hours > 1 ? 's ' : ' ');
                                if ($minutes > 0) $total_hours .= $minutes . ' min' . ($minutes > 1 ? 's ' : ' ');
                                if ($seconds > 0) $total_hours .= $seconds . ' sec' . ($seconds > 1 ? 's' : '');
                                $total_hours = trim($total_hours);
                            } else {
                                $total_hours = '-';
                            }
                            $status = $row['time_out'] ? 'In/Out' : 'In';
                            $formatted_status = $status === 'In/Out'
                                ? '<span style="color: green;">In</span>/<span style="color: orange;">Out</span>'
                                : '<span style="color: green;">In</span>';
                        ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                            <td><?php echo htmlspecialchars($row['staff_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars(date('h:i A', strtotime($row['time_in']))); ?></td>
                            <td><?php echo $time_out ? htmlspecialchars(date('h:i A', strtotime($row['time_out']))) : '-'; ?></td>
                            <td><strong><?php echo htmlspecialchars($total_hours); ?></strong></td>
                            <td><?php echo $formatted_status; ?></td>
                            <td class="dropdown">
                                <button class="btn_archive btn btn-warning" type="button" data-id="<?php echo $row['attendance_id']; ?>">
                                    <i class="icofont icofont-archive"></i> Archive
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Date</th>
                        <th>Staff ID</th>
                        <th>Full Name</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Total Hours</th>
                        <th>Status(In/Out)</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

                                                                    <!-- contact data table card end -->
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- tab pane contact end -->
                                            </div>
                                            <!-- tab content end -->
                                        </div>
                                    </div>
                                </div>
                                <!-- Page-body end -->
                            </div>
                            <!-- Main body end -->
                            <div id="styleSelector">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Required Jquery -->
    <?php include('../includes/scripts.php')?>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-23581568-13');
        
        $(function() {
            var interval = setInterval(function() {
                var momentNow = moment();
                $('.date').html(momentNow.format('MMMM DD, YYYY'));  
                $('.time').html(momentNow.format('hh:mm:ss A'));
                $('.day').html(momentNow.format('dddd').toUpperCase());
            }, 100);
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.btn_archive').click(function(event){
                event.preventDefault();
                var attendanceId = $(this).data('id');
                console.log('Archive button clicked for attendance ID:', attendanceId); // Added console.log

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you really want to archive this record?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, archive it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'attendance_function.php',
                            type: 'POST',
                            data: {
                                action: 'archive_attendance',
                                attendance_id: attendanceId
                            },
                            success: function(response) {
                                console.log('Server response:', response); // Added console.log
                                try {
                                    if (typeof response === 'string') {
                                        response = JSON.parse(response);
                                    }
                                    if(response.status === 'success') {
                                        Swal.fire(
                                            'Archived!',
                                            'The record has been archived.',
                                            'success'
                                        ).then(() => {
                                            location.reload(); // Refresh the page to reflect changes
                                        });
                                    } else {
                                        Swal.fire(
                                            'Failed!',
                                            'Failed to archive record: ' + response.message,
                                            'error'
                                        );
                                    }
                                } catch (e) {
                                    console.error('Parsing error:', e);
                                    Swal.fire(
                                        'Error!',
                                        'Invalid response from server.',
                                        'error'
                                    );
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                console.error('Error:', errorThrown);
                                Swal.fire(
                                    'Error!',
                                    'Error archiving record',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>

</body>

</html>
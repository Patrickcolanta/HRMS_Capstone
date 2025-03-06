<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

if (!isset($_SESSION['slogin']) || !isset($_SESSION['srole'])) {
    header('Location: ../index.php');
    exit();
}

$userRole = $_SESSION['srole'];

if ($userRole !== 'Admin') {
    header('Location: ../index.php');
    exit();
}

// Fetch only archived logs
$sql = "SELECT id, email_id, action, timestamp, ip_address, user_agent FROM audit_logs WHERE is_archived = 1 ORDER BY timestamp DESC";
$result = mysqli_query($conn, $sql);
?>

<body>
<?php include('../includes/loader.php'); ?>

<div id="pcoded" class="pcoded">
    <div class="pcoded-container navbar-wrapper">
        <?php include('../includes/topbar.php'); ?>
        <div class="pcoded-main-container">
            <div class="pcoded-wrapper">
                <?php $page_name = "archived_logs"; ?>
                <?php include('../includes/sidebar.php'); ?>
                <div class="pcoded-content">
                    <div class="pcoded-inner-content">
                        <div class="main-body">
                            <div class="page-wrapper">
                                <div class="page-header">
                                    <div class="row align-items-end">
                                        <div class="col-lg-8">
                                            <div class="page-header-title">
                                                <h4>Archived Audit Logs</h4>
                                                <span>View and restore archived logs</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="page-body">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Archived Activity Logs</h5>
                                        </div>
                                        <div class="card-block">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                    <thead class="thead-dark">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Email</th>
                                                            <th>Action</th>
                                                            <th>Timestamp</th>
                                                            <th>IP Address</th>
                                                            <th>User Agent</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        if ($result && mysqli_num_rows($result) > 0) {
                                                            $count = 1;
                                                            while ($row = mysqli_fetch_assoc($result)) {
                                                                echo "<tr>
                                                                    <td>{$count}</td>
                                                                    <td>{$row['email_id']}</td>
                                                                    <td>{$row['action']}</td>
                                                                    <td>{$row['timestamp']}</td>
                                                                    <td>{$row['ip_address']}</td>
                                                                    <td>{$row['user_agent']}</td>
                                                                    <td>
                                                                        <form method='post' class='d-inline restore-form'>
                                                                            <input type='hidden' name='log_id' value='{$row['id']}'>
                                                                            <button type='button' class='btn btn-success btn-sm restore-btn'>Restore</button>
                                                                        </form>
                                                                    </td>
                                                                </tr>";
                                                                $count++;
                                                            }
                                                        } else {
                                                            echo "<tr><td colspan='7' class='text-center'>No archived logs found</td></tr>";
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center mt-3">
                                        <a href="audit_logs.php" class="btn btn-primary">Back to Active Logs</a>
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

<?php include('../includes/scripts.php'); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.restore-btn').forEach(button => {
        button.addEventListener('click', function () {
            const form = this.closest('.restore-form');
            const logId = form.querySelector('input[name="log_id"]').value;

            Swal.fire({
                title: 'Are you sure?',
                text: 'This log will be restored to active logs.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, restore it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../functions/audit_log_function.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `restore_log=1&log_id=${encodeURIComponent(logId)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.fire({
                            title: data.status === 'success' ? 'Restored!' : 'Error!',
                            text: data.message,
                            icon: data.status,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            if (data.status === 'success') location.reload();
                        });
                    })
                    .catch(error => {
                        Swal.fire('Error!', 'An unexpected error occurred.', 'error');
                    });
                }
            });
        });
    });
});
</script>

</body>
</html>

<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

if (!isset($_SESSION['slogin']) || !isset($_SESSION['srole'])) {
    header('Location: ../index.php');
    exit();
}

$userRole = $_SESSION['srole'];

if ($userRole !== 'Manager' && $userRole !== 'Admin') {
    header('Location: ../index.php');
    exit();
}

// Fetch only active (non-archived) logs
$sql = "SELECT id, email_id, action, timestamp, ip_address, user_agent FROM audit_logs WHERE is_archived = 0 ORDER BY timestamp DESC";
$result = mysqli_query($conn, $sql);
?>

<body>
<?php include('../includes/loader.php'); ?>

<div id="pcoded" class="pcoded">
    <div class="pcoded-container navbar-wrapper">
        <?php include('../includes/topbar.php'); ?>
        <div class="pcoded-main-container">
            <div class="pcoded-wrapper">
                <?php $page_name = "audit_logs"; ?>
                <?php include('../includes/sidebar.php'); ?>
                <div class="pcoded-content">
                    <div class="pcoded-inner-content">
                        <div class="main-body">
                            <div class="page-wrapper">
                                <div class="page-header">
                                    <div class="row align-items-end">
                                        <div class="col-lg-8">
                                            <div class="page-header-title">
                                                <h4>Audit Logs</h4>
                                                <span>Track user logins and actions</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="page-body">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5>Activity Logs</h5>
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
                                                            <?php if ($userRole === 'Admin') echo "<th>Actions</th>"; ?>
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
                                                                    <td>{$row['user_agent']}</td>";

                                                                if ($userRole === 'Admin') {
                                                                    echo "<td>
                                                                        <form method='post' class='d-inline archive-form'>
                                                                            <input type='hidden' name='log_id' value='{$row['id']}'>
                                                                            <button type='button' class='btn btn-warning btn-sm archive-btn'>Archive</button>
                                                                        </form>
                                                                    </td>";
                                                                }

                                                                echo "</tr>";
                                                                $count++;
                                                            }
                                                        } else {
                                                            echo "<tr><td colspan='7' class='text-center'>No logs found</td></tr>";
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center mt-3">
                                        <a href="archived_logs.php" class="btn btn-secondary">View Archived Logs</a>
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
    document.querySelectorAll('.archive-btn').forEach(button => {
        button.addEventListener('click', function () {
            const form = this.closest('.archive-form');
            const logId = form.querySelector('input[name="log_id"]').value;

            Swal.fire({
                title: 'Are you sure?',
                text: 'This log will be archived.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, archive it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../functions/audit_log_function.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `archive_log=1&log_id=${encodeURIComponent(logId)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire('Archived!', data.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error("Fetch Error:", error);
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

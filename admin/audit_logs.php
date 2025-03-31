<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

if (!isset($_SESSION['slogin']) || !isset($_SESSION['srole'])) {
    header('Location: ../index.php');
    exit();
}

$userRole = $_SESSION['srole'];

if ($userRole !== 'HR' && $userRole !== 'Admin') {
    header('Location: ../index.php');
    exit();
}

// ✅ Updated query to fetch audit logs with correct fields
$stmt = $conn->prepare("
    SELECT al.id, al.emp_id, e.staff_id, e.first_name, e.last_name, al.email_id, e.role, al.action, 
           al.timestamp, al.ip_address, al.user_agent, al.status, al.severity_level
    FROM audit_logs al
    LEFT JOIN tblemployees e ON al.emp_id = e.emp_id
    WHERE al.is_archived = 0
    ORDER BY al.timestamp DESC
");

$stmt->execute();
$result = $stmt->get_result();
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
</div>
</div>
</div>
</div>
<div class="page-body">
    <div class="card">
        <div class="card-header">
            <h5>System Audit Logs</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Staff ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Status</th>
                        <th>Severity</th>
                        <th>IP Address</th>
                        <th>Device Info</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['staff_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['role']); ?></td>
                            <td><?php echo htmlspecialchars($row['action']); ?></td>
                            <td>
                                <?php 
                                    if ($row['status'] == 'Success') {
                                        echo '<span class="text-success">Success</span>';
                                    } elseif ($row['status'] == 'Failed') {
                                        echo '<span class="text-danger">Failed</span>';
                                    } else {
                                        echo '<span class="text-warning">Unknown</span>';
                                    }
                                ?>
                            </td>
                            <td>
                                <?php 
                                    if ($row['severity_level'] == 1) {
                                        echo '<span class="badge badge-info">Low</span>';
                                    } elseif ($row['severity_level'] == 2) {
                                        echo '<span class="badge badge-warning">Medium</span>';
                                    } elseif ($row['severity_level'] == 3) {
                                        echo '<span class="badge badge-danger">High</span>';
                                    } else {
                                        echo '<span class="badge badge-secondary">Unknown</span>';
                                    }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['ip_address']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_agent']); ?></td>
                            <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
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

<?php include('../includes/scripts.php'); ?>
</body>
</html>

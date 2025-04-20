<?php
include("config.php");


$emp_id = $_SESSION['slogin'];
$notifications = [];
$unread_count = 0;

// Use prepared statement to fetch notifications
$sql = "SELECT notifications.title, notifications.message, notifications.type, notifications.created_at
        FROM user_notifications
        JOIN notifications ON user_notifications.notification_id = notifications.id
        WHERE user_notifications.emp_id = ?
        ORDER BY notifications.created_at DESC
        LIMIT 5"; // Show latest 5

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
    $unread_count++;
}
?>


<ul class="nav-right">
    <!-- Notification Dropdown -->
    <li class="header-notification">
        <div class="dropdown-primary dropdown">
            <div class="dropdown-toggle" data-toggle="dropdown">
                <i class="feather icon-bell"></i>
                <span class="badge bg-c-pink"><?= $unread_count ?></span>
            </div>
            <ul class="show-notification notification-view dropdown-menu"
                data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                <li>
                    <h6>Notifications</h6>
                    <?php if ($unread_count > 0): ?>
                        <label class="label label-danger">New</label>
                    <?php endif; ?>
                </li>

                <?php if (count($notifications) > 0): ?>
                    <?php foreach ($notifications as $note): ?>
                        <li>
                            <div class="media">
                                <div class="media-body">
                                    <h5 class="notification-user"><?= htmlspecialchars($note['title']) ?></h5>
                                    <p class="notification-msg"><?= htmlspecialchars($note['message']) ?></p>
                                    <span class="notification-time"><?= date("M d, Y h:i A", strtotime($note['created_at'])) ?></span>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="text-center p-3">
                        <p>No new notifications</p>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </li>


    <!-- User Profile Dropdown -->
    <li class="user-profile header-notification">
        <div class="dropdown-primary dropdown">
            <div class="dropdown-toggle" data-toggle="dropdown">
                <?php
                $image_src = !empty($session_image) ? $session_image : '..\files\assets\images\avatar-4.jpg';
                echo '<img src="' . $image_src . '" class="img-radius" alt="User-Profile-Image">';
                ?>
                <span><?php echo $session_sfirstname . ' ' . $session_smiddlename . ' ' . $session_slastname; ?></span>
                <i class="feather icon-chevron-down"></i>
            </div>
            <ul class="show-notification profile-notification dropdown-menu"
                data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                <li>
                    <a href="staff_detailed.php?id=<?= $session_id ?>&view=2">
                        <i class="feather icon-user"></i> Profile
                    </a>
                </li>
                <li>
                    <a href="../logout.php">
                        <i class="feather icon-log-out"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </li>
</ul>
<?php
// Start a session only if it's not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check whether the session variable SESS_MEMBER_ID is present or not
if (!isset($_SESSION['slogin']) || trim($_SESSION['slogin']) == '') {
    header("Location: ../index.php");
    exit;
}

// Check if the session has expired
if (isset($_SESSION['last_activity'])) {
    $sessionExpiration = 60 * 30; // Session expires after 30 minutes of inactivity

    if (time() - $_SESSION['last_activity'] > $sessionExpiration) {
        // Destroy the session and redirect to login
        session_unset();
        session_destroy();

        echo "<script>alert('Your session has expired. Please log in again.');</script>";
        echo "<script>window.location = '../index.php';</script>";
        exit;
    }
}

// Update the last activity time
$_SESSION['last_activity'] = time();

// Assign session variables
$session_id = $_SESSION['slogin'] ?? null;
$session_role = $_SESSION['srole'] ?? null;
$session_semail = $_SESSION['semail'] ?? null;
$session_sfirstname = $_SESSION['sfirstname'] ?? null;
$session_slastname = $_SESSION['slastname'] ?? null;
$session_smiddlename = $_SESSION['smiddlename'] ?? null;
$session_scontact = $_SESSION['scontact'] ?? null;
$session_sdesignation = $_SESSION['sdesignation'] ?? null;
$session_sstaff_id = $_SESSION['sstaff_id'] ?? null;
$session_image = $_SESSION['simageurl'] ?? null;
$session_depart = $_SESSION['department'] ?? null;
$session_supervisor = $_SESSION['is_supervisor'] ?? null;
?>

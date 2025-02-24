<?php
// Prevent re-declaration of constants
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'leave_portal');
}

// Establish MySQLi database connection (Only once)
$conn = $conn ?? new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check MySQLi connection
if ($conn->connect_error) {
    error_log("MySQLi Connection Error: " . $conn->connect_error);
    die(json_encode(array('status' => 'error', 'message' => 'Database connection failed')));
}

// Establish PDO database connection (Only if needed)
try {
    if (!isset($dbh)) {
        $dbh = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS, [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
        ]);
    }
} catch (PDOException $e) {
    error_log("PDO Connection Error: " . $e->getMessage());
    die(json_encode(array('status' => 'error', 'message' => 'Database connection failed')));
}
?>

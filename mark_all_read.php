<?php
require 'connector.php';
session_start();

$userId = $_SESSION['user_id'];

$sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
sqlsrv_query($conn, $sql, array($userId));

header("Location: student_portal.php");
exit();
?>
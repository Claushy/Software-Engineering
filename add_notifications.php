<?php
include 'connector.php';

$user_id = $_POST['user_id'];
$message = $_POST['message'];

$sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
$params = array($user_id, $message);

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt) {
    echo "success";
} else {
    die(print_r(sqlsrv_errors(), true));
}
?>
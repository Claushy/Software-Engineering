<?php
include 'connector.php';

$id = $_POST['id'];

$sql = "UPDATE notifications SET status = 'read' WHERE notification_id = ?";
$params = array($id);

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt) {
    echo "updated";
} else {
    die(print_r(sqlsrv_errors(), true));
}
?>
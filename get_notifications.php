<?php
include 'connector.php';

$user_id = $_GET['user_id'];

$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$params = array($user_id);

$stmt = sqlsrv_query($conn, $sql, $params);

$notifications = array();

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $notifications[] = [
        "notification_id" => $row['notification_id'],
        "message" => $row['message'],
        "status" => $row['status'],
        "created_at" => $row['created_at']->format('Y-m-d H:i:s')
    ];
}

echo json_encode($notifications);
?>
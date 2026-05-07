<?php
include 'connector.php';

$user_id = $_GET['user_id'];
$last_id = $_GET['last_id']; // last notification received

set_time_limit(0); // allow script to run longer

while (true) {

    $sql = "SELECT TOP 1 * FROM notifications 
            WHERE user_id = ? AND notification_id > ?
            ORDER BY notification_id DESC";

    $params = array($user_id, $last_id);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

        echo json_encode([
            "notification_id" => $row['notification_id'],
            "message" => $row['message'],
            "created_at" => $row['created_at']->format('Y-m-d H:i:s')
        ]);

        break; // stop loop when new notif found
    }

    sleep(2); // wait before checking again
}
?>
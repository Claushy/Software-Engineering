<?php
require 'auth.php';
require 'connector.php';
requireLogin();

$id = $_GET['id'];

// update document status
$sql = "UPDATE documents SET status = 'Approved' WHERE id = ?";
sqlsrv_query($conn, $sql, array($id));

// get document owner
$sqlUser = "SELECT user_id, title FROM documents WHERE id = ?";
$stmt = sqlsrv_query($conn, $sqlUser, array($id));
$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

// create notification
$notif = "Your document '{$row['title']}' was APPROVED.";

$sqlNotif = "INSERT INTO notifications (user_id, message, is_read, created_at)
             VALUES (?, ?, 0, GETDATE())";

sqlsrv_query($conn, $sqlNotif, array($row['user_id'], $notif));

header("Location: professor_dashboard.php");
exit();
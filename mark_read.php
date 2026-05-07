<?php
require 'connector.php';

$id = $_GET['id'];

$sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
sqlsrv_query($conn, $sql, array($id));

header("Location: student_portal.php");
exit();
?>
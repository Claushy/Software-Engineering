<?php
require 'auth.php';
require 'connector.php';
requireLogin();

// ONLY ADMIN CAN DELETE
if ($_SESSION['role'] !== 'admin') {
    die("Access denied");
}

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$id = $_GET['id'];

// GET FILE PATH FIRST (to delete file)
$sqlGet = "SELECT file_path FROM documents WHERE id = ?";
$stmtGet = sqlsrv_query($conn, $sqlGet, array($id));
$row = sqlsrv_fetch_array($stmtGet, SQLSRV_FETCH_ASSOC);

if ($row) {
    $filePath = $row['file_path'];

    // DELETE FILE FROM SERVER
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // DELETE FROM DATABASE
    $sqlDelete = "DELETE FROM documents WHERE id = ?";
    $stmtDelete = sqlsrv_query($conn, $sqlDelete, array($id));

    if ($stmtDelete === false) {
        die(print_r(sqlsrv_errors(), true));
    }
}

// REDIRECT BACK
header("Location: my_documents.php");
exit();
?>
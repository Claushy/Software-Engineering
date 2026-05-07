<?php
require 'auth.php';
require 'connector.php';
requireLogin();

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {

    $file = $_FILES['profile_image'];

    // CHECK FOR ERRORS
    if ($file['error'] !== 0) {
        die("Upload error.");
    }

    // ALLOWED TYPES
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!in_array($file['type'], $allowedTypes)) {
        die("Only JPG and PNG allowed.");
    }

    // CREATE FOLDER IF NOT EXISTS
    $uploadDir = "uploads/profile/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // UNIQUE FILE NAME
    $fileName = time() . "_" . basename($file['name']);
    $filePath = $uploadDir . $fileName;

   // GET OLD IMAGE FIRST (BEFORE UPLOAD)
$getOld = sqlsrv_query($conn, 
    "SELECT profile_image FROM ceatuser WHERE id = ?", 
    array($userId)
);

$old = sqlsrv_fetch_array($getOld, SQLSRV_FETCH_ASSOC);

// DELETE OLD FILE (if exists)
if (!empty($old['profile_image']) && file_exists($old['profile_image'])) {
    unlink($old['profile_image']);
}

// MOVE NEW FILE
if (move_uploaded_file($file['tmp_name'], $filePath)) {
   
    // SAVE TO DATABASE
    $sql = "UPDATE ceatuser SET profile_image = ? WHERE id = ?";


    // SAVE NEW PATH
    $sql = "UPDATE ceatuser SET profile_image = ? WHERE id = ?";
    $params = array($filePath, $userId);

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
        header("Location: personal_info.php");
        exit();
    } else {
        die(print_r(sqlsrv_errors(), true));
    }

} else {
    die("Failed to upload file.");
}
}
?>
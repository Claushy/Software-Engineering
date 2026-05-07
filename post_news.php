<?php
require 'auth.php';
require 'connector.php';
requireLogin();

$title = $_POST['title'];
$content = $_POST['content'];
$course = $_POST['course']; // NEW
$userId = $_SESSION['user_id'];

$imagePath = null;

// HANDLE IMAGE UPLOAD
if (!empty($_FILES['image']['name'])) {
    $targetDir = "uploads/";
    $imageName = time() . "_" . basename($_FILES["image"]["name"]);
    $targetFile = $targetDir . $imageName;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
        $imagePath = $targetFile;
    }
}

// INSERT INTO announcements
$sql = "INSERT INTO announcements (posted_by, title, content, image_path, course, created_at)
        VALUES (?, ?, ?, ?, ?, GETDATE())";

$params = array($userId, $title, $content, $imagePath, $course);

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

header("Location: professor_dashboard.php");
exit();
?>
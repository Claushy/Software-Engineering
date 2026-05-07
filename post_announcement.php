<?php
require 'auth.php';
require 'connector.php';
requireRole('professor');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $course = $_POST['course'] ?? null; // Make sure to capture the course from the dropdown
    $userId = $_SESSION['user_id'];
    $imagePath = null;

    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        
        // Create directory if it doesn't exist
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES["image"]["name"]));
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $imagePath = $targetFile;
        }
    }

    // Updated SQL to include course if you added that column to your table
    $sql = "INSERT INTO announcements (user_id, title, content, image_path, course, created_at)
            VALUES (?, ?, ?, ?, ?, GETDATE())";

    $params = array($userId, $title, $content, $imagePath, $course);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    header("Location: professor_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Post Announcement</title>
</head>
<body>

<h2>Post Announcement</h2>

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Title" required><br><br>
    
    <textarea name="content" placeholder="Content" required></textarea><br><br>
    
    <input type="file" name="image"><br><br>
    
    <button type="submit">Post</button>
</form>

</body>
</html>
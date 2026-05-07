<?php
require 'auth.php';
require 'connector.php';
requireLogin();

$message = '';

// GET ALL PROFESSORS
$sqlProf = "SELECT id, full_name FROM ceatuser WHERE role = 'professor'";
$stmtProf = sqlsrv_query($conn, $sqlProf);

// UPLOAD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {

    $title = trim($_POST['title']);
    $userId = $_SESSION['user_id'];
    $recipient = $_POST['professor']; // professor name
    $file = $_FILES['document'];

    if ($file['error'] === 0) {

        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . basename($file['name']);
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {

            // STORE recipient INSIDE "title + file info system (safe mode)"
            $sql = "
                INSERT INTO documents 
                (user_id, title, file_name, file_path, status)
                VALUES (?, ?, ?, ?, 'Pending')
            ";

            $finalTitle = $title . ' (To: ' . $recipient . ')';

            $stmt = sqlsrv_query($conn, $sql, array(
                $userId,
                $finalTitle,
                $fileName,
                $filePath
            ));

            $message = $stmt
                ? "<div class='alert alert-success'>Document uploaded to $recipient.</div>"
                : "<div class='alert alert-danger'>Database save failed.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Upload failed.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

<div class="card p-4 shadow mx-auto" style="max-width:600px;">

    <h2>Upload Document</h2>

    <?php echo $message; ?>

    <form method="POST" enctype="multipart/form-data">

        <input type="text" name="title" class="form-control mb-3" placeholder="Document Title" required>

        <!-- PROFESSOR DROPDOWN -->
        <select name="professor" class="form-control mb-3" required>
            <option value="">Select Recipient Professor</option>

            <?php while ($p = sqlsrv_fetch_array($stmtProf, SQLSRV_FETCH_ASSOC)) { ?>
                <option value="<?php echo htmlspecialchars($p['full_name']); ?>">
                    <?php echo htmlspecialchars($p['full_name']); ?>
                </option>
            <?php } ?>

        </select>

        <input type="file" name="document" class="form-control mb-3" required>

        <button class="btn btn-success">Upload</button>
            

    </form>

</div>

</div>

</body>
</html>
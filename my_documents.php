<?php
require 'auth.php';
require 'connector.php';
requireLogin();
if ($_SESSION['role'] !== 'student' && $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// --- PUT THE DATABASE FETCHING LOGIC HERE ---
$userId = $_SESSION['user_id'];
$sql = "SELECT id, user_id, title, file_name, file_path, status, uploaded_at 
        FROM documents 
        WHERE user_id = ? 
        ORDER BY uploaded_at DESC";

$params = array($userId);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo "<pre>";
    die(print_r(sqlsrv_errors(), true));
    echo "</pre>";
}

// Optional: Keep this alert here or move it inside the HTML container
if (!sqlsrv_has_rows($stmt)) {
    $noDataMessage = "<div class='alert alert-warning mt-3'>Database connected, but no rows found for User ID: $userId. Check if the ID in the table matches.</div>";
} else {
    $noDataMessage = "";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Documents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card p-4 shadow">
        <h2>My Documents</h2>
        <?php
        $backLink = ($_SESSION['role'] === 'admin')
            ? "admin_dashboard.php"
            : "student_portal.php";
        ?>

        <a href="<?= $backLink ?>" class="btn btn-secondary mb-3">
            Back
        </a>
        
        <?php echo $noDataMessage; ?>

        <table class="table table-bordered mt-3">
            <thead class="table-dark">
<tr>
    <th>Title</th>
    <th>File</th>
    <th>Status</th>
    <th>Uploaded</th>
    <?php if ($_SESSION['role'] === 'admin') { ?>
        <th>Action</th>
    <?php } ?>
</tr>
</thead>
            <tbody>
<?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
<tr>
    <td><?php echo htmlspecialchars($row['title']); ?></td>

    <td>
        <a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank">
            View
        </a>
    </td>

    <td><?php echo htmlspecialchars($row['status']); ?></td>

    <td>
        <?php 
        if ($row['uploaded_at'] instanceof DateTime) {
            echo $row['uploaded_at']->format('Y-m-d H:i');
        } else {
            echo "N/A";
        }
        ?>
    </td>

    <?php if ($_SESSION['role'] === 'admin') { ?>
    <td>
        <a href="delete_document.php?id=<?php echo $row['id']; ?>"
           class="btn btn-danger btn-sm"
           onclick="return confirm('Are you sure you want to delete this document?');">
           Delete
        </a>
    </td>
    <?php } ?>

</tr>
<?php } ?>
</tbody>
        </table>
    </div>
</div>
</body>
</html>
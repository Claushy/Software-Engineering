<?php
require 'auth.php';
require 'connector.php';
requireLogin();

$userId = $_SESSION['user_id'];

$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = sqlsrv_query($conn, $sql, array($userId));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h3>Notifications</h3>

    <a href="student_portal.php" class="btn btn-secondary mb-3">Back</a>

    <div class="card p-3">

        <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
            <div class="border-bottom p-2">
                <p class="mb-1"><?php echo htmlspecialchars($row['message']); ?></p>
                <small class="text-muted">
                    <?php echo $row['created_at']->format('Y-m-d H:i'); ?>
                </small>
            </div>
        <?php } ?>

    </div>
</div>

</body>
</html>
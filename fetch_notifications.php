<?php
require 'connector.php';
session_start();

$userId = $_SESSION['user_id'];

$sql = "SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC";

$stmt = sqlsrv_query($conn, $sql, array($userId));

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
?>

<div class="border rounded p-2 mb-2 bg-light">

    <small><?= $row['message'] ?></small><br>

    <small class="text-muted">
        <?= $row['created_at']->format('Y-m-d H:i') ?>
    </small>

    <div class="mt-2 d-flex gap-2">

        <!-- MARK AS READ -->
        <a href="mark_read.php?id=<?= $row['id'] ?>" 
           class="btn btn-sm btn-success">
           Mark as read
        </a>

        <!-- DELETE -->
        <a href="delete_notification.php?id=<?= $row['id'] ?>" 
           class="btn btn-sm btn-danger"
           onclick="return confirm('Delete this notification?')">
           Delete
        </a>

    </div>

</div>

<?php } ?>
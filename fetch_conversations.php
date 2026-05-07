<?php
require 'connector.php';
session_start();

$userId = $_SESSION['user_id'];

$sql = "
SELECT 
    u.id,
    u.full_name,
    MAX(m.created_at) as last_time,
    (
        SELECT TOP 1 message 
        FROM messages 
        WHERE 
            (sender_id = u.id AND receiver_id = ?) 
            OR (sender_id = ? AND receiver_id = u.id)
        ORDER BY created_at DESC
    ) as last_message
FROM ceatuser u
LEFT JOIN messages m 
    ON (m.sender_id = u.id AND m.receiver_id = ?)
    OR (m.sender_id = ? AND m.receiver_id = u.id)
WHERE u.id != ?
GROUP BY u.id, u.full_name
ORDER BY last_time DESC
";

$params = array($userId, $userId, $userId, $userId, $userId);
$stmt = sqlsrv_query($conn, $sql, $params);

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
?>

<div class="chat-item" onclick="openChat(<?= $row['id'] ?>, '<?= htmlspecialchars($row['full_name']) ?>')">

    <div class="chat-avatar">
        <?= strtoupper(substr($row['full_name'], 0, 1)) ?>
    </div>

    <div class="chat-text">
        <strong><?= htmlspecialchars($row['full_name']) ?></strong><br>
        <small><?= htmlspecialchars($row['last_message'] ?? 'No messages yet') ?></small>
    </div>

</div>

<?php } ?>
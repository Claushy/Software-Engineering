<?php
require 'auth.php';
require 'connector.php';
requireLogin();

$userId = $_SESSION['user_id'];

// Create uploads folder if it doesn't exist
if (!is_dir('uploads')) {
    mkdir('uploads', 0777, true);
}

// 1. Update activity
sqlsrv_query($conn, "UPDATE ceatuser SET last_active = GETDATE() WHERE id = ?", array($userId));

// 2. Get User Info
$sql = "SELECT * FROM ceatuser WHERE id = ?";
$stmt = sqlsrv_query($conn, $sql, array($userId));
$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

// 3. SEND MESSAGE & FILE LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_id = $_POST['receiver_id'];
    $message = trim($_POST['message'] ?? '');
    $filePath = null;

    // Handle File Upload
    if (isset($_FILES['chat_file']) && $_FILES['chat_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['chat_file']['tmp_name'];
        $fileName = $_FILES['chat_file']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Sanitize name to prevent overwrites
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $dest_path = 'uploads/' . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $filePath = $newFileName;
        }
    }

    // Only send if there is text OR a file
    if (!empty($message) || $filePath) {
        $sql = "INSERT INTO messages (sender_id, receiver_id, message, file_path, created_at) VALUES (?, ?, ?, ?, GETDATE())";
        sqlsrv_query($conn, $sql, array($userId, $receiver_id, $message, $filePath));

        $notif = "New attachment/message from " . $user['full_name'];
        $sqlNotif = "INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, GETDATE())";
        sqlsrv_query($conn, $sqlNotif, array($receiver_id, $notif));
        
        header("Location: chat.php?user=" . $receiver_id);
        exit();
    }
}

// 4. SEARCH & RECENT LOGIC (Same as before)
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$isSearching = !empty($searchTerm);
if ($isSearching) {
    $contactStmt = sqlsrv_query($conn, "SELECT id, full_name, last_active, role FROM ceatuser WHERE full_name LIKE ? AND id != ?", array("%$searchTerm%", $userId));
} else {
    $sqlRecent = "SELECT DISTINCT u.id, u.full_name, u.last_active, u.role FROM ceatuser u JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id) WHERE (m.sender_id = ? OR m.receiver_id = ?) AND u.id != ?";
    $contactStmt = sqlsrv_query($conn, $sqlRecent, array($userId, $userId, $userId));
}

// 5. GET MESSAGES
$chatWith = $_GET['user'] ?? null;
$messages = [];
if ($chatWith) {
    $sql = "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC";
    $stmtMsgs = sqlsrv_query($conn, $sql, array($userId, $chatWith, $chatWith, $userId));
    while ($row = sqlsrv_fetch_array($stmtMsgs, SQLSRV_FETCH_ASSOC)) { $messages[] = $row; }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CEAT Messenger</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      
        body { background:
    linear-gradient(rgba(7,35,26,0.6),rgba(7,35,26,0.6)),
    url("images/campus.jpeg");
    }
        .chat-container { height: 65vh; overflow-y: auto; background: white; border-radius: 10px; padding: 20px; border: 1px solid #ddd; }
        .msg { margin-bottom: 15px; padding: 10px 15px; border-radius: 18px; max-width: 70%; position: relative; }
        .msg-sent { background: #157347; color: white; margin-left: auto; border-bottom-right-radius: 2px; }
        .msg-received { background: #e9ecef; color: #333; margin-right: auto; border-bottom-left-radius: 2px; }
        .active-contact { background: #e8f5e9 !important; border-left: 4px solid #157347 !important; font-weight: bold; }
        .chat-img { max-width: 100%; border-radius: 10px; margin-top: 5px; cursor: pointer; }
        .file-link { color: inherit; text-decoration: underline; font-size: 0.85rem; }
        .status-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; border: 2px solid white; }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-md-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Messages</h5>
        </div>

        <div class="card-body p-2">
            <form method="GET" action="chat.php" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search names..." value="<?= htmlspecialchars($searchTerm) ?>">
                    <button class="btn btn-success btn-sm" type="submit">Go</button>
                </div>
                <?php if($isSearching): ?>
                    <a href="chat.php" class="text-danger small mt-1 d-block text-center">Clear Search</a>
                <?php endif; ?>
            </form>

            <div class="list-group list-group-flush" style="max-height: 55vh; overflow-y: auto;">
                <small class="text-muted px-3 mb-2 d-block">
                    <?= $isSearching ? 'Search Results' : 'Recent Chats' ?>
                </small>

                <?php 
                $hasContacts = false;
                while ($u = sqlsrv_fetch_array($contactStmt, SQLSRV_FETCH_ASSOC)) : 
                    $hasContacts = true;
                    $isOnline = false;
                    if ($u['last_active']) {
                        $diff = (new DateTime())->getTimestamp() - $u['last_active']->getTimestamp();
                        if ($diff <= 300) $isOnline = true;
                    }
                ?>
                    <a href="?user=<?= $u['id'] ?>" class="list-group-item list-group-item-action border-0 mb-1 rounded <?= ($chatWith == $u['id']) ? 'active-contact' : '' ?>">
                        <div class="d-flex align-items-center">
                            <div class="position-relative me-3">
                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                                </div>
                                <span class="position-absolute bottom-0 end-0 status-dot <?= $isOnline ? 'bg-success' : 'bg-secondary' ?>"></span>
                            </div>
                            <div>
                                <div class="small fw-bold"><?= htmlspecialchars($u['full_name']) ?></div>
                                <small class="text-uppercase text-muted" style="font-size: 0.6rem;"><?= htmlspecialchars($u['role']) ?></small>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>

                <?php if(!$hasContacts): ?>
                    <p class="text-center text-muted small mt-3">No contacts found.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-footer bg-transparent border-top-0">
            <a href="<?= (strtolower($user['role']) === 'professor') ? 'professor_dashboard.php' : 'student_portal.php' ?>" 
               class="btn btn-outline-secondary btn-sm w-100 py-2">
                ← Back to Dashboard
            </a>
        </div>
    </div>
</div>

        <div class="col-md-8">
            <?php if ($chatWith) : ?>
                <div class="chat-container shadow-sm mb-3" id="messageBody">
                    <?php foreach ($messages as $m) : ?>
                        <div class="msg <?= ($m['sender_id'] == $userId) ? 'msg-sent' : 'msg-received' ?>">
                            <?php if(!empty($m['message'])): ?>
                                <div><?= htmlspecialchars($m['message']) ?></div>
                            <?php endif; ?>

                            <?php if(!empty($m['file_path'])): 
                                $ext = strtolower(pathinfo($m['file_path'], PATHINFO_EXTENSION));
                                $imgExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            ?>
                                <div class="mt-2">
                                    <?php if(in_array($ext, $imgExts)): ?>
                                        <a href="uploads/<?= $m['file_path'] ?>" target="_blank">
                                            <img src="uploads/<?= $m['file_path'] ?>" class="chat-img">
                                        </a>
                                    <?php else: ?>
                                        <a href="uploads/<?= $m['file_path'] ?>" class="file-link" target="_blank">
                                            📄 Download Attachment (<?= strtoupper($ext) ?>)
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <span class="timestamp"><?= $m['created_at']->format('H:i') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form method="POST" enctype="multipart/form-data" class="bg-white p-3 rounded shadow-sm border">
                    <input type="hidden" name="receiver_id" value="<?= $chatWith ?>">
                    <div class="d-flex gap-2 align-items-center">
                        <label class="btn btn-light rounded-circle mb-0" title="Attach File">
                            📁 <input type="file" name="chat_file" hidden onchange="this.form.submit()">
                        </label>
                        <input type="text" name="message" class="form-control rounded-pill" placeholder="Type a message..." autocomplete="off">
                        <button class="btn btn-success rounded-circle" style="width: 45px; height: 45px;">➤</button>
                    </div>
                </form>
            <?php else : ?>
                <div class="chat-container d-flex align-items-center justify-content-center">
                    <p class="text-muted">Select a contact to start sharing files and messages.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const msgBody = document.getElementById('messageBody');
    if(msgBody) msgBody.scrollTop = msgBody.scrollHeight;
</script>
</body>
</html>
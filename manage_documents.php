<?php
require 'auth.php';
require 'connector.php';
requireLogin();

// HANDLE APPROVE / REJECT ACTION
if (isset($_POST['action']) && isset($_POST['doc_id'])) {

    $docId = $_POST['doc_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'Approved';
        $message = "Your document has been APPROVED";
    } elseif ($action === 'reject') {
        $status = 'Rejected';
        $message = "Your document has been REJECTED";
    }

    // ✅ UPDATE DOCUMENT STATUS
    $sqlUpdate = "UPDATE documents SET status = ? WHERE id = ?";
    $params = array($status, $docId);
    $stmtUpdate = sqlsrv_query($conn, $sqlUpdate, $params);

    if ($stmtUpdate === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // ✅ GET STUDENT ID FROM DOCUMENT
    $sqlGetUser = "SELECT user_id FROM documents WHERE id = ?";
    $stmtUser = sqlsrv_query($conn, $sqlGetUser, array($docId));
    $rowUser = sqlsrv_fetch_array($stmtUser, SQLSRV_FETCH_ASSOC);

    if ($rowUser) {
        $studentId = $rowUser['user_id'];

        // ✅ INSERT NOTIFICATION
        $sqlNotif = "INSERT INTO notifications (user_id, message, created_at, is_read)
                     VALUES (?, ?, GETDATE(), 0)";
        sqlsrv_query($conn, $sqlNotif, array($studentId, $message));
    }

    header("Location: manage_documents.php");
    exit();
}



$userId = $_SESSION['user_id'];

// GET PROFESSOR NAME
$sqlUser = "SELECT full_name, role FROM ceatuser WHERE id = ?";
$stmtUser = sqlsrv_query($conn, $sqlUser, array($userId));
$user = sqlsrv_fetch_array($stmtUser, SQLSRV_FETCH_ASSOC);

if (strtolower($user['role']) !== 'professor') {
    header("Location: student_portal.php");
    exit();
}

$profName = $user['full_name'];

// FILTER DOCUMENTS FOR THIS PROFESSOR ONLY
// UPDATED SQL: Join documents with ceatuser to get the student's name
$sql = "
    SELECT d.*, u.full_name AS student_name 
    FROM documents d
    JOIN ceatuser u ON d.user_id = u.id
    WHERE d.title LIKE ?
    ORDER BY d.uploaded_at DESC
";

$searchPattern = "%To: $profName%";
$stmt = sqlsrv_query($conn, $sql, array($searchPattern));
?>

<!DOCTYPE html>
<html>
<head>
<title>My Document Reviews</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    :root {
        /* A more sophisticated palette for 2026 */
        --bg-main: #f4f7f6;
        --accent-glow: rgba(11, 143, 92, 0.08);
        --sidebar-gradient: linear-gradient(135deg, #01392b 0%, #04553f 100%);
    }

    body {
    margin: 0;
    min-height: 100vh;
    /* Sophisticated Lasallian Gradient */
    background-color: #f2f6f4;
    background-image: 
        radial-gradient(at 0% 0%, rgba(11, 143, 92, 0.12) 0px, transparent 50%),
        radial-gradient(at 100% 100%, rgba(1, 57, 43, 0.08) 0px, transparent 50%);
    background-attachment: fixed;
    display: flex;
    flex-direction: column;
}
    body::before {
    content: "";
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    opacity: 0.02; /* Keep it very faint! */
    pointer-events: none;
    background-image: url('https://www.transparenttextures.com/patterns/stardust.png');
    z-index: 9999;
}

    /* Add this to your .doc-container or .main wrapper */
    .glass-effect {
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .doc-container {
        max-width: 1000px;
        margin: 50px auto;
        padding: 0 20px;
    }

    /* Header Section */
    .header-group {
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }

    /* Modern Document Card */
    .doc-card {
    background: #ffffff;
    /* Soft border makes it look "cut" from the page */
    border: 1px solid rgba(11, 143, 92, 0.15);
    border-radius: 20px;
    padding: 30px; /* Increased padding */
    margin: 20px auto;
    max-width: 900px; /* Prevents it from being too wide on desktop */
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.doc-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 50px rgba(11, 143, 92, 0.1);
    border-color: rgba(11, 143, 92, 0.3);
}

    .doc-info {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .doc-icon {
        width: 55px; height: 55px;
        background: #e8f5ee;
        color: var(--brand-green);
        border-radius: 15px;
        display: flex;
        align-items: center; justify-content: center;
        font-size: 1.5rem;
    }

    .doc-details h5 {
        margin: 0;
        font-weight: 700;
        color: var(--text-main);
    }

    .doc-details p {
        margin: 2px 0 0;
        font-size: 0.9rem;
        color: #60766d;
    }

    /* Status Badges */
    .badge-modern {
        padding: 8px 16px;
        border-radius: 30px;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .status-pending { background: #fff8e1; color: #f57f17; }
    .status-approved { background: #e8f5ee; color: #0b8f5c; }
    .status-rejected { background: #ffebee; color: #c62828; }

    /* Action Buttons */
    .btn-action {
    font-weight: 600;
    letter-spacing: 0.3px;
    padding: 12px 24px;
    border-radius: 12px;
    transition: 0.2s all ease;
    border: none;
}

.btn-success {
    background: linear-gradient(135deg, #0b8f5c 0%, #076d46 100%) !important;
    color: white !important;
}

.btn-danger {
    background: #fff0f0 !important;
    color: #d32f2f !important;
    border: 1px solid #ffcdd2 !important;
}

    .btn-view {
        background: #f0f4f2;
        color: var(--brand-green);
    }

    .btn-danger:hover {
    background: #d32f2f !important;
    color: white !important;
}

</style>

</head>

<body class="bg-light">

<div class="container mt-5">

    <body class="bg-light">

    <div class="doc-container">
        
        <div class="header-group">
            <div>
                <h2 class="fw-bold">Document Review</h2>
                <p class="text-muted">Review and manage student submissions assigned to you.</p>
            </div>
            <a href="professor_dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">
                ← Dashboard
            </a>
        </div>

        <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { 
            $statusClass = 'status-pending';
            if($row['status'] == 'Approved') $statusClass = 'status-approved';
            if($row['status'] == 'Rejected') $statusClass = 'status-rejected';
        ?>
        <div class="doc-card">
            <div class="doc-info">
                <div class="doc-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <d<div class="doc-details">
    <h5><?= htmlspecialchars($row['title']) ?></h5>
    
    <p class="mb-0">
        <strong>Sender:</strong> <?= htmlspecialchars($row['student_name']) ?>
    </p>
    
    <p style="font-size: 0.8rem; opacity: 0.8;">
        Uploaded: <?= $row['uploaded_at'] instanceof DateTime ? $row['uploaded_at']->format('M d, Y') : htmlspecialchars($row['uploaded_at']) ?>
    </p>
</div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <span class="badge-modern <?= $statusClass ?>">
                    <?= htmlspecialchars($row['status']) ?>
                </span>

                <a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="btn-action btn-view text-decoration-none">
                    View File
                </a>

                <form method="POST" style="display:inline;">
                    <input type="hidden" name="doc_id" value="<?= $row['id'] ?>">
                    <button class="btn-action btn-success shadow-sm" name="action" value="approve" style="background-color:#0b8f5c; color: white;">
                        Approve
                    </button>
                </form>

                <form method="POST" style="display:inline;">
                    <input type="hidden" name="doc_id" value="<?= $row['id'] ?>">
                    <button class="btn-action btn-danger shadow-sm" name="action" value="reject" style="color: white;">
                        Reject
                    </button>
                </form> 
            </div>
        </div>
        <?php } ?>
    </div>

</body>

</div>

</body>
</html>
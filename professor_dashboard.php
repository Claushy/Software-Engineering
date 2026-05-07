<?php
require 'auth.php';
require 'connector.php';
requireLogin();

$userId = $_SESSION['user_id'];

sqlsrv_query($conn, "
    UPDATE ceatuser 
    SET last_active = GETDATE() 
    WHERE id = ?", 
    array($userId)
);

$userId = $_SESSION['user_id'];


// Get user
$sql = "SELECT * FROM ceatuser WHERE id = ?";
$stmt = sqlsrv_query($conn, $sql, array($userId));
$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$profileImage = $user['profile_image'] ?? null;
$online = false;

if (!empty($user['last_active'])) {
    $online = (time() - strtotime($user['last_active']->format('Y-m-d H:i:s'))) < 10;
}
// GET ALL USERS FOR CHAT (exclude self)
$sqlUsers = "SELECT id, full_name, last_active FROM ceatuser WHERE id != ?";
$stmtUsers = sqlsrv_query($conn, $sqlUsers, array($userId));

$course = $user['course'] ?? 'Not Assigned';
$email = $user['email'] ?? 'N/A';
$phone = $user['phone'] ?? 'N/A';

// Role check
if (strtolower($user['role']) !== 'professor') {
    header("Location: student_portal.php");
    exit();
}

$fullName = $user['full_name'] ?? 'Professor';
$initial = strtoupper(substr(trim($fullName), 0, 1));



// Pending documents (FOR THIS PROFESSOR ONLY)
$sqlPending = "
    SELECT COUNT(*) AS total
    FROM documents
    WHERE status = 'Pending'
    AND title LIKE ?
";

$search = "%To: $fullName%";

$stmtPending = sqlsrv_query($conn, $sqlPending, array($search));

if ($stmtPending === false) {
    die(print_r(sqlsrv_errors(), true));
}

$rowPending = sqlsrv_fetch_array($stmtPending, SQLSRV_FETCH_ASSOC);
$pendingCount = $rowPending['total'] ?? 0;

// Today's appointments
$sqlAppt = "SELECT COUNT(*) AS total 
FROM appointments 
WHERE status = 'pending'
AND CAST(appointment_date AS DATE) = CAST(GETDATE() AS DATE)";

$stmtAppt = sqlsrv_query($conn, $sqlAppt, array($userId));

if ($stmtAppt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$apptRow = sqlsrv_fetch_array($stmtAppt, SQLSRV_FETCH_ASSOC);
$apptCount = $apptRow['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Professor Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    margin: 0;
    font-family: "Segoe UI", sans-serif;
    background: linear-gradient(135deg, #eef5f2, #f6f8fb);
}
.form-control {
    border: 1px solid #e0e6ed;
    padding: 10px 15px;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.form-control:focus {
    border-color: #1b5e4b;
    box-shadow: 0 0 0 0.25rem rgba(27, 94, 75, 0.1);
}

.badge {
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 8px;
}


/* SIDEBAR (cleaner + modern) */
.sidebar {
    width: 260px;
    height: 100vh;
    position: fixed;
    background: linear-gradient(180deg, #0b2e22, #041c15);
    color: white;
    padding: 25px;
}

.logo {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 5px;
}

.subtitle {
    font-size: 12px;
    color: #a7b6b0;
    margin-bottom: 25px;
}

.nav a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    color: #cfd8dc;
    text-decoration: none;
    border-radius: 12px;
    margin-top: 8px;
    transition: 0.3s;
}

.nav a:hover,
.nav a.active {
    background: rgba(255,255,255,0.12);
    color: #fff;
    transform: translateX(5px);
}

/* MAIN */
.main {
    margin-left: 260px;
    padding: 30px;
}

/* HERO HEADER (NEW) */
.hero {
    background: linear-gradient(135deg, #1b5e4b, #0d3f30);
    color: white;
    padding: 25px;
    border-radius: 18px;
    margin-bottom: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.hero h1 {
    margin: 0;
    font-size: 26px;
}

.hero p {
    margin-top: 5px;
    opacity: 0.85;
}

/* PROFILE CHIP */
.profile-chip {
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255,255,255,0.1);
    padding: 10px 15px;
    border-radius: 12px;
    backdrop-filter: blur(8px);
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: white;
    color: #1b5e4b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

/* STATS */
.stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-bottom: 25px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    display: flex;
    gap: 15px;
    align-items: center;
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
    transition: 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: #e8f5ef;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1b5e4b;
    font-size: 22px;
}

/* PANEL */
.panel {
    background: white;
    border-radius: 16px;
    padding: 22px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    transition: 0.3s;
}

.panel:hover {
    transform: translateY(-3px);
}

/* BUTTON */
.btn-custom {
    display: inline-block;
    background: linear-gradient(135deg, #1b5e4b, #144639);
    color: white;
    padding: 10px 14px;
    border-radius: 10px;
    text-decoration: none;
    transition: 0.3s;
}

.btn-custom:hover {
    background: #0d3f30;
}

</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="logo">CEAT Faculty Portal</div>
    <div class="subtitle">Professor Management System</div>

    <div class="nav">
        <a class="active" href="professor_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="manage_documents.php"><i class="bi bi-file-earmark-check"></i> Document Review</a>
        <a href="professor_appointments.php"><i class="bi bi-calendar-check"></i> Appointment Requests</a>
        <a href="schedule_management.php"><i class="bi bi-calendar"></i> Schedule</a>
        <a href="chat.php"><i class="bi bi-chat-dots"></i> Messages</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>

    </div>

</div>

<!-- MAIN -->
<div class="main">

    <!-- HEADER -->
    <div class="hero">
    <div style="display:flex; justify-content:space-between; align-items:center;">

        <div>
            <h1>Welcome, Prof. <?php echo htmlspecialchars($fullName); ?></h1>
            <p>Manage documents, announcements, and student consultations</p>
        </div>

        <div class="profile-chip">
            <a href="personal_info.php">
    <div class="avatar" style="overflow:hidden; padding:0; position:relative;">

        <?php if (!empty($profileImage)) { ?>
            <img src="<?= htmlspecialchars($profileImage) ?>" 
                 style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
        <?php } else { ?>
            <?= $initial ?>
        <?php } ?>

        <!-- 🟢 ONLINE DOT -->
        <?php if ($online) { ?>
            <span style="
                position:absolute;
                bottom:2px;
                right:2px;
                width:10px;
                height:10px;
                background:#00ff7f;
                border-radius:50%;
                border:2px solid white;
            "></span>
        <?php } ?>

    </div>
</a>
            <div>
            <strong><?php echo htmlspecialchars($fullName); ?></strong><br>

    <small>
        <?php echo htmlspecialchars($course); ?>
    </small>
</div>
        </div>

    </div>
</div>

   

    <!-- STATS -->
    <div class="stats">

        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <div class="stat-value"><?php echo $pendingCount; ?></div>
                <div class="stat-label">Pending Documents</div>
            </div>
        </div>
 
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-calendar-event"></i></div>
            <div>
                <div class="stat-value"><?php echo $apptCount; ?></div>
                <div class="stat-label">Today's Appointments</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-person-badge"></i></div>
            <div>
                <div class="stat-value">Active</div>
                <div class="stat-label">Faculty Status</div>
            </div>
        </div>

    </div>


    <div class="doc-container">
        <div class="panel shadow-sm" style="max-width: 600px; margin: auto; border-top: 5px solid #0b8f5c;">
    <h3 class="fw-bold mb-4"> Create New Announcement</h3>

    <form action="post_news.php" method="POST" enctype="multipart/form-data">

        <input type="text" name="title" placeholder="Announcement title..."
               required
               style="width:100%; padding:12px; margin-bottom:10px; border-radius:10px; border:1px solid #ddd;">

        <textarea name="content" placeholder="Write your announcement..."
                  required
                  style="width:100%; height:120px; padding:12px; margin-bottom:10px; border-radius:10px; border:1px solid #ddd;"></textarea>


<!-- ✅ ADD THIS HERE -->
<select name="course" required
    style="width:100%; padding:10px; margin-bottom:10px; border-radius:10px; border:1px solid #ddd;">

    <option value="">Select Course</option>
    <option>Architecture</option>
    <option>Civil Engineering</option>
    <option>Computer Engineering</option>
    <option>Electrical Engineering</option>
    <option>Electronics Engineering</option>
    <option>Industrial Engineering</option>
    <option>Mechanical Engineering</option>
    <option>Sanitary Engineering</option>
    <option>Multimedia Arts</option>

</select>

<input type="file" name="image" accept="image/*" style="margin-bottom:10px;">

        <button type="submit" class="btn-custom">Publish</button>

    </form>
</div>


</div>

</div>
<!-- CHAT SIDEBAR -->



<script>
document.getElementById('searchUser').addEventListener('keyup', function() {
    let value = this.value.toLowerCase();
    let chats = document.querySelectorAll('.chat-item');

    chats.forEach(chat => {
        chat.style.display = chat.innerText.toLowerCase().includes(value)
            ? 'flex'
            : 'none';
    });
});
</script>

</body>
</html>
<?php
require 'auth.php';
require 'connector.php';
requireLogin();

$userId = $_SESSION['user_id'];

$sql = "SELECT id, full_name, student_number, email, phone, role, course, profile_image 
        FROM ceatuser 
        WHERE id = ?";

$stmt = sqlsrv_query($conn, $sql, array($userId));
$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

$fullName = $user['full_name'] ?? 'User';
$initial = strtoupper(substr(trim($fullName), 0, 1));
$profileImage = $user['profile_image'] ?? null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Personal Info</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{
    margin:0;
    font-family:Segoe UI;
    background:#eef3f1;
}

.container{
    max-width:900px;
    margin:60px auto;
    padding:20px;
    
}

.back-btn{
    display:inline-flex;
    align-items:center;
    gap:10px;
    text-decoration:none;
    background:#ffffff;
    color:#12352b;
    padding:12px 18px;
    border-radius:14px;
    font-weight:700;
    margin-bottom:20px;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
    transition:0.2s;
}

.back-btn:hover{
    transform:translateX(-4px);
}

.card{
    background:#fff;
    border-radius:28px;
    padding:30px;
    box-shadow:0 20px 50px rgba(0,0,0,0.08);
}

.header{
    display:flex;
    align-items:center;
    gap:20px;
    margin-bottom:30px;
}

.avatar{
    width:90px;
    height:90px;
    border-radius:50%;
    background:#04965d;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:2rem;
    font-weight:800;
}

.avatar-img{
    width:90px;
    height:90px;
    border-radius:50%;
    object-fit:cover;
    box-shadow:0 10px 25px rgba(0,0,0,0.15);
}

h2{
    margin:0;
    color:#12352b;
}

.info{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}

.box{
    background:#f6faf8;
    padding:18px;
    border-radius:18px;
}

.label{
    font-size:13px;
    color:#6b7d75;
}

.value{
    font-size:16px;
    font-weight:700;
    color:#12352b;
    margin-top:6px;
}
</style>
</head>

<body>

<div class="container">
    <?php
$dashboardLink = ($user['role'] === 'professor')
    ? 'professor_dashboard.php'
    : 'student_portal.php';
?>

<a href="<?= $dashboardLink ?>" class="back-btn">
    <i class="bi bi-arrow-left"></i> Back to Dashboard
</a>

    <div class="card">

        <div class="header">
<?php
$profileImage = $user['profile_image'] ?? null;
?>

<div class="avatar">
    <?php if (!empty($profileImage)) { ?>
        <img src="<?= htmlspecialchars($profileImage) ?>?v=<?= time() ?>"
             style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
    <?php } else { ?>
        <?= $initial ?>
    <?php } ?>
</div>
            <div>
                <h2><?= htmlspecialchars($fullName) ?></h2>
                <small style="color:#6b7d75;">Personal Information</small>
                <form action="upload_profile.php" method="POST" enctype="multipart/form-data" style="margin-top:15px;">
                    <input type="file" name="profile_image" required>
                <button type="submit" style="
        margin-top:10px;
        padding:10px 14px;
        border:none;
        background:#04965d;
        color:#fff;
        border-radius:10px;
        font-weight:700;
        cursor:pointer;
    ">
        Upload Profile Picture
    </button>
</form>
            </div>
            
        </div>

        

        <div class="info">

            <div class="box">
                <div class="label">Full Name</div>
                <div class="value"><?= htmlspecialchars($user['full_name']) ?></div>
            </div>

            <div class="box">
                <div class="label">Course</div>
                <div class="value"><?= htmlspecialchars($user['course']) ?></div>
            </div>

            <div class="box">
                <div class="label">Student Number</div>
                <div class="value"><?= htmlspecialchars($user['student_number']) ?></div>
            </div>

            <div class="box">
                <div class="label">Role</div>
                <div class="value"><?= htmlspecialchars($user['role']) ?></div>
            </div>

            <div class="box">
                <div class="label">Phone</div>
                <div class="value"><?= htmlspecialchars($user['phone']) ?></div>
            </div>

            <div class="box">
                <div class="label">Email</div>
                <div class="value"><?= htmlspecialchars($user['email']) ?></div>
            </div>

        </div>

    </div>
</div>

</body>
</html>
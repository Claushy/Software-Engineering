<?php
session_start();
require 'connector.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // 1. Check Admin Table
    $adminSql = "SELECT admin_id, full_name FROM ceatadmin WHERE email = ? AND password = ?";
    $adminRes = sqlsrv_query($conn, $adminSql, array($email, $password));

    if ($adminRes && $adminRow = sqlsrv_fetch_array($adminRes, SQLSRV_FETCH_ASSOC)) {
        $_SESSION['user_name'] = $adminRow['full_name'];
        $_SESSION['role'] = 'admin';
        header("Location: admin_dashboard.php"); exit;
    }

    // 2. Check Student Table
    $userSql = "SELECT id, fullname FROM ceatuser WHERE email = ? AND password = ?";
    $userRes = sqlsrv_query($conn, $userSql, array($email, $password));

    if ($userRes && $userRow = sqlsrv_fetch_array($userRes, SQLSRV_FETCH_ASSOC)) {
        $_SESSION['user_name'] = $userRow['fullname'];
        $_SESSION['role'] = 'student';
        header("Location: student_portal.php"); exit;
    }
    $error = "Invalid institutional credentials!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login | DLSU CEA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --dlsu-green: #006a4e; }
        body { background: #f4f4f4; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .card { width: 100%; max-width: 400px; border-top: 8px solid var(--dlsu-green); border-radius: 12px; }
        .btn-green { background: var(--dlsu-green); color: white; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card shadow-lg p-4">
        <h3 class="text-center mb-4" style="color:var(--dlsu-green); font-weight: 800;">CEA PORTAL</h3>
        <?php if($error) echo "<div class='alert alert-danger small text-center'>$error</div>"; ?>
        <form method="POST">
            <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
            <input type="password" name="password" class="form-control mb-4" placeholder="Password" required>
            <button class="btn btn-green w-100 py-2">LOGIN</button>
        </form>
        <p class="text-center mt-3 small">New student? <a href="register.php" style="color:var(--dlsu-green); font-weight:bold; text-decoration:none;">Register here</a></p>
    </div>
</body>
</html>
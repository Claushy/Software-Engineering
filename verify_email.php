<?php
session_start();
require 'connector.php';

$message = '';

if (!isset($_GET['token']) || empty($_GET['token'])) {
    $message = "<div class='alert alert-danger'>Invalid verification link.</div>";
} else {
    $token = trim($_GET['token']);

    $sql = "SELECT id, email_verified FROM ceatuser WHERE verification_token = ?";
    $stmt = sqlsrv_query($conn, $sql, array($token));

    if ($stmt === false) {
        $message = "<div class='alert alert-danger'><pre>" . print_r(sqlsrv_errors(), true) . "</pre></div>";
    } else {
        $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        if ($user) {
            if ((int)$user['email_verified'] === 1) {
                $message = "<div class='alert alert-info'>Your account is already verified. <a href='login.php'>Login here</a>.</div>";
            } else {
                $updateSql = "UPDATE ceatuser SET email_verified = 1, verification_token = NULL WHERE id = ?";
                $updateStmt = sqlsrv_query($conn, $updateSql, array($user['id']));

                if ($updateStmt === false) {
                    $message = "<div class='alert alert-danger'><pre>" . print_r(sqlsrv_errors(), true) . "</pre></div>";
                } else {
                    $message = "<div class='alert alert-success'>Email verified successfully. <a href='login.php'>Login now</a>.</div>";
                }
            }
        } else {
            $message = "<div class='alert alert-danger'>Invalid or expired verification token.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify Email | DLSU CEA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card p-4 shadow">
                <h3 class="mb-3 text-center">Email Verification</h3>
                <?php echo $message; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
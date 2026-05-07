<?php
session_start();
require 'connector.php';

$message = '';

if(!isset($_SESSION['verify_email'])){
    header("Location: login.php");
    exit;
}

$email = $_SESSION['verify_email'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $otp = $_POST['otp'];

    $sql = "SELECT otp_code FROM ceatuser WHERE email=?";
    $stmt = sqlsrv_query($conn,$sql,array($email));

    $row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);

    if($row && $row['otp_code'] == $otp){

        sqlsrv_query($conn,"UPDATE ceatuser SET otp_verified=1 WHERE email='$email'");

        unset($_SESSION['verify_email']);

        echo "<h3>Account Verified</h3><a href='login.php'>Login</a>";
        exit;

    }else{
        $message = "Invalid OTP";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Verify OTP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">
<div class="card p-4 shadow">

<h3>Verify SMS Code</h3>

<?php if($message) echo "<div class='alert alert-danger'>$message</div>"; ?>

<form method="POST">

<input class="form-control mb-3" name="otp" placeholder="Enter OTP" required>

<button class="btn btn-success w-100">Verify</button>

</form>

</div>
</div>

</body>
</html>
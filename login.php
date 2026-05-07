<?php
session_start();
require 'connector.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM ceatuser WHERE email = ?";
    $stmt = sqlsrv_query($conn, $sql, array($email));

    if ($stmt === false) {
        $message = "<div class='alert error'>Database error.</div>";
    } else {

        $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {

            if ((int)$user['email_verified'] !== 1) {
                $message = "<div class='alert warning'>Please verify your email first.</div>";
            } else {

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = strtolower(trim($user['role']));

                if ($_SESSION['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } elseif ($_SESSION['role'] === 'professor') {
                    header("Location: professor_dashboard.php");
                } else {
                    header("Location: student_portal.php");
                }

                exit;
            }

        } else {
            $message = "<div class='alert error'>Invalid email or password.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>CEAT Portal Login</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<style>

body{
    margin:0;
    height:100vh;

    background:
    linear-gradient(rgba(7,35,26,0.6),rgba(7,35,26,0.6)),
    url("images/campus.jpeg");

    background-size:cover;
    background-position:center;

    display:flex;
    justify-content:center;
    align-items:center;

    font-family:Segoe UI, sans-serif;
}

/* phone style card */

.phone{

    width:340px;
    height:640px;

    background:#f7f5ef;

    border-radius:40px;

    box-shadow:0 20px 50px rgba(0,0,0,0.25);

    padding:40px 28px;

    position:relative;
}

/* top notch */

.phone::before{
    content:"";
    width:120px;
    height:16px;
    background:#ddd;
    border-radius:20px;

    position:absolute;
    top:12px;
    left:50%;
    transform:translateX(-50%);
}

/* bottom circle */

.phone::after{
    content:"";
    width:40px;
    height:40px;
    border:2px solid #ccc;
    border-radius:50%;

    position:absolute;
    bottom:14px;
    left:50%;
    transform:translateX(-50%);
}

/* logo */

.logo{
    display:flex;
    justify-content:center;
    align-items:center;
    margin-top:5px;
    margin-bottom:18px;
}

.logo-box{

    width:120px;
    height:120px;

    border-radius:50%;

    background:rgba(255,255,255,0.15);

    backdrop-filter:blur(10px);

    box-shadow:
    0 10px 30px rgba(0,0,0,0.15),
    inset 0 0 10px rgba(255,255,255,0.2);

    display:flex;
    justify-content:center;
    align-items:center;

    border:3px solid rgba(255,255,255,0.25);

    overflow:hidden;
}

.logo-box img{

    width:90px;
    height:90px;

    object-fit:contain;

    border-radius:50%;
}

/* title */

.title{
    text-align:center;
    margin-top:12px;
    color:#0b5d46;
    font-size:22px;
    font-weight:700;
}

/* subtitle */

.subtitle{
    text-align:center;
    font-size:14px;
    color:#6c7c76;
    margin-bottom:25px;
}

/* input */

.input{
    width:100%;
    border:none;
    border-bottom:2px solid #d8a437;
    background:transparent;

    padding:12px 5px;

    margin-bottom:20px;

    font-size:16px;
}

.input:focus{
    outline:none;
    border-bottom:2px solid #0b5d46;
}

/* button */

.btn{

    width:100%;

    padding:14px;

    border:none;

    border-radius:30px;

    background:linear-gradient(#ebb03d,#d8a437);

    font-weight:700;

    font-size:16px;

    cursor:pointer;

    margin-top:10px;
}

.btn:hover{
    transform:translateY(-1px);
}

/* register link */

.link{
    text-align:center;
    margin-top:15px;
    font-size:14px;
}

.link a{
    color:#d8a437;
    font-weight:600;
    text-decoration:none;
}

/* alerts */

.alert{
    padding:10px;
    border-radius:10px;
    font-size:13px;
    margin-bottom:15px;
}

.error{
    background:#fdecec;
    color:#a51d1d;
}

.warning{
    background:#fff3d9;
    color:#7a5710;
}

</style>

</head>

<body>

<div class="phone">

<div class="logo">

    <div class="logo-box">
        <img src="images/logo.jpg" alt="CEAT Logo">
    </div>

</div>
<div class="title">
CEAT Portal Login
</div>

<div class="subtitle">
Access your academic documents and appointments
</div>

<?php echo $message; ?>

<form method="POST">

<input
type="email"
name="email"
class="input"
placeholder="Email Address"
required>

<input
type="password"
name="password"
class="input"
placeholder="Password"
required>

<button class="btn">
LOGIN
</button>

</form>

<div class="link">
Don't have an account?
<a href="register.php">Register</a>
</div>

</div>

</body>
</html>
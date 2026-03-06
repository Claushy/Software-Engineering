<?php
require 'connector.php';
session_start();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname       = $_POST['fullname'] ?? '';
    $student_number = $_POST['student_number'] ?? '';
    $phone          = $_POST['phone'] ?? '';
    $email          = $_POST['email'] ?? '';
    $password       = $_POST['password'] ?? '';

    if (!empty($fullname) && !empty($student_number) && !empty($phone)) {

        $otp = rand(100000,999999);

        $sql = "INSERT INTO ceatuser (fullname, student_number, phone, email, password, otp_code, otp_verified)
                VALUES (?, ?, ?, ?, ?, ?, 0)";

        $params = array($fullname,$student_number,$phone,$email,$password,$otp);

        $stmt = sqlsrv_query($conn,$sql,$params);

        if($stmt){

            // SEND SMS
            $data = [
                "phone"=>$phone,
                "message"=>"Your CEAT verification code is: $otp"
            ];

            $ch = curl_init("https://api.textbee.dev/api/v1/gateway/devices/69aacf2d83044b1f6065f1df/send-sms");

            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($data));
            curl_setopt($ch,CURLOPT_HTTPHEADER,[
                "Content-Type: application/json",
                "x-api-key: aa8e449d-0719-4761-b7fa-c3a2589990a2"
            ]);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

            curl_exec($ch);
            curl_close($ch);

            $_SESSION['verify_email'] = $email;

            header("Location: verify_otp.php");
            exit;

        } else {

            $errors = sqlsrv_errors();
            $message = "<div class='alert alert-danger'><pre>".print_r($errors,true)."</pre></div>";

        }

    } else {
        $message = "<div class='alert alert-warning'>Please fill in all fields.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">
<div class="card p-4 shadow">

<h3 class="mb-3">CEAT Register</h3>

<?php echo $message; ?>

<form method="POST">

<input class="form-control mb-3" name="fullname" placeholder="Full Name" required>

<input class="form-control mb-3" name="student_number" placeholder="Student Number" required>

<input class="form-control mb-3" name="phone" placeholder="639XXXXXXXXX" required>

<input class="form-control mb-3" type="email" name="email" placeholder="Email" required>

<input class="form-control mb-3" type="password" name="password" placeholder="Password" required>

<button class="btn btn-success w-100">Register</button>

</form>

</div>
</div>

</body>
</html>
<?php
require 'connector.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Using null coalescing (??) to prevent the "Undefined array key" error
    $fullname       = $_POST['fullname'] ?? '';
    $student_number = $_POST['student_number'] ?? '';
    $email          = $_POST['email'] ?? '';
    $password       = $_POST['password'] ?? '';

    if (!empty($fullname) && !empty($student_number)) {
        // Match the columns in your DLSU.dbo.ceatuser table
        $sql = "INSERT INTO ceatuser (fullname, student_number, email, password) VALUES (?, ?, ?, ?)";
        $params = array($fullname, $student_number, $email, $password);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt) {
            $message = "<div class='alert alert-success'>Registration Successful! <a href='login.php' class='fw-bold text-success'>Login here</a></div>";
        } else {
            $errors = sqlsrv_errors();
            $message = "<div class='alert alert-danger'><strong>SQL Error:</strong><br><pre>" . print_r($errors, true) . "</pre></div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Please fill in all required fields.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | DLSU CEAT Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --dlsu-green: #006a4e; }
        body { background: #f8f9fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
        .card { width: 100%; max-width: 450px; border-top: 8px solid var(--dlsu-green); border-radius: 12px; }
        .btn-green { background: var(--dlsu-green); color: white; border: none; font-weight: bold; }
        .btn-green:hover { background: #004d38; color: white; }
    </style>
</head>
<body>
    <div class="card shadow-lg p-4">
        <div class="text-center mb-4">
            <h3 style="color:var(--dlsu-green); font-weight: 800;">CEAT REGISTER</h3>
            <p class="text-muted small">Engineering & Architecture Department</p>
        </div>
        
        <?php echo $message; ?>

        <form method="POST" action="register.php">
            <div class="mb-3">
                <label class="form-label small fw-bold">Full Name</label>
                <input type="text" name="fullname" class="form-control" placeholder="Juan Dela Cruz" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Student Number</label>
                <input type="text" name="student_number" class="form-control" placeholder="2024-XXXXX" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="user@dlsu.edu.ph" required>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-green w-100 py-2 text-uppercase">Create Account</button>
        </form>
        <p class="text-center mt-3 small text-muted">Already a member? <a href="login.php" style="color:var(--dlsu-green); font-weight:bold; text-decoration:none;">Login</a></p>
    </div>
</body>
</html>
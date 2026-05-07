<?php
session_start();
require 'connector.php';
require 'mail_config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $student_number = trim($_POST['student_number']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = trim($_POST['role']);
    $course = trim($_POST['course']);
    $token = bin2hex(random_bytes(32));

    $allowedRoles = ['student', 'professor', 'admin'];
    

    if (!in_array($role, $allowedRoles, true)) {
        $message = "<div class='alert alert-danger'>Invalid role selected.</div>";
    } else {
        $checkSql = "SELECT id FROM ceatuser WHERE email = ?";
        $checkStmt = sqlsrv_query($conn, $checkSql, array($email));

        if ($checkStmt === false) {
            $errors = sqlsrv_errors();
            $message = "<div class='alert alert-danger'><pre>";
            foreach ($errors as $error) {
                $message .= "SQLSTATE: " . $error['SQLSTATE'] . "\n";
                $message .= "Code: " . $error['code'] . "\n";
                $message .= "Message: " . $error['message'] . "\n\n";
            }
            $message .= "</pre></div>";
        } else {
            $existing = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

            if ($existing) {
                $message = "<div class='alert alert-danger'>Email already exists.</div>";
            } else {
                $sql = "INSERT INTO ceatuser 
(full_name, student_number, email, phone, password, role, course, verification_token, email_verified)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";

$params = array(
    $full_name,
    $student_number,
    $email,
    $phone,
    $password,
    $role,
    $course,
    $token
);
                $stmt = sqlsrv_query($conn, $sql, $params);

                if ($stmt) {
                    $send = sendVerificationEmail($email, $full_name, $token);
                    if ($send === true) {
                        $message = "<div class='alert alert-success'>Registered successfully. Check your email to verify your account.</div>";
                    } else {
                        $message = "<div class='alert alert-warning'>Registered, but email sending failed: " . htmlspecialchars($send) . "</div>";
                    }
                } else {
                    $errors = sqlsrv_errors();
                    $message = "<div class='alert alert-danger'><pre>";
                    foreach ($errors as $error) {
                        $message .= "SQLSTATE: " . $error['SQLSTATE'] . "\n";
                        $message .= "Code: " . $error['code'] . "\n";
                        $message .= "Message: " . $error['message'] . "\n\n";
                    }
                    $message .= "</pre></div>";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEAT Portal Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root{
            --dlsu-green:#0b5d46;
            --dlsu-green-dark:#083f31;
            --dlsu-green-soft:#e8f3ef;
            --dlsu-gold:#d4af37;
            --ivory:#f8f6f1;
            --text-dark:#163128;
            --muted:#5f6f68;
            --white:#ffffff;
            --border:#d7dfda;
        }

        *{
            box-sizing:border-box;
        }

        body{
            margin:0;
            min-height:100vh;
            font-family:"Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                linear-gradient(rgba(7,32,26,0.68), rgba(7,32,26,0.68)),
                url("images/campus.jpeg") no-repeat center center fixed;
            background-size:cover;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:30px 16px;
            color:var(--text-dark);
        }

        .portal-shell{

    width:96%;
    max-width:1450px;

    min-height:92vh;

    border-radius:32px;

    overflow:hidden;

    background:rgba(255,255,255,0.10);

    backdrop-filter:blur(18px);

    box-shadow:
    0 30px 70px rgba(0,0,0,0.28);

    display:grid;

    grid-template-columns: 1.15fr 1fr;

    border:1px solid rgba(255,255,255,0.12);
}

        @keyframes fadeUp{

    from{
        opacity:0;
        transform:translateY(25px);
    }

    to{
        opacity:1;
        transform:translateY(0);
    }
}

        .branding-panel{

    position:relative;

    padding:80px 70px;

    color:white;

    background:
    linear-gradient(160deg,
    rgba(8,63,49,0.96),
    rgba(11,93,70,0.88)),
    url("images/campus.jpg") no-repeat center center;

    background-size:cover;

    display:flex;

    justify-content:flex-start;

    align-items:flex-start;
}

        .branding-panel::before{
            content:"";
            position:absolute;
            inset:0;
            background:
                radial-gradient(circle at top right, rgba(212,175,55,0.18), transparent 30%),
                linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0));
            pointer-events:none;
        }

        .branding-content{

    position:relative;

    z-index:1;

    width:100%;

    max-width:650px;
}

        .portal-tag{
            display:inline-block;
            padding:10px 18px;
            border-radius:999px;
            background:rgba(255,255,255,0.10);
            border:1px solid rgba(255,255,255,0.18);
            font-size:0.95rem;
            font-weight:700;
            letter-spacing:0.3px;
            margin-bottom:24px;
        }

        .crest-line{
            width:72px;
            height:4px;
            background:var(--dlsu-gold);
            border-radius:999px;
            margin-bottom:22px;
        }

        .branding-panel h1{
            font-size:3rem;
            line-height:1.12;
            font-weight:800;
            margin:0 0 16px;
        }

        .branding-panel p{
            font-size:1.12rem;
            line-height:1.8;
            color:rgba(255,255,255,0.92);
            margin:0 0 26px;
            max-width:540px;
        }

        .heritage-box{
            background:rgba(255,255,255,0.09);
            border:1px solid rgba(255,255,255,0.13);
            border-radius:24px;
            padding:22px 22px 18px;
            max-width:560px;
            backdrop-filter:blur(6px);
        }

        .heritage-title{
            font-size:1.02rem;
            font-weight:800;
            margin-bottom:10px;
            color:#fff3c4;
        }

        .heritage-list{
            margin:0;
            padding-left:20px;
            line-height:1.9;
            font-size:1.02rem;
            color:rgba(255,255,255,0.95);
        }

       .form-panel{

    background:
    linear-gradient(
    180deg,
    rgba(255,255,255,0.96),
    rgba(248,246,241,0.94)
    );

    padding:70px 60px;

    display:flex;

    justify-content:flex-start;

    align-items:flex-start;

    overflow-y:auto;
}

        .form-wrap{

    width:100%;

    max-width:650px;

    margin:auto;
}
@media (max-width: 980px){

    .portal-shell{

        grid-template-columns:1fr;

        width:100%;
        min-height:auto;
    }

    .branding-panel,
    .form-panel{

        padding:45px 28px;
    }

    .form-wrap{

        max-width:100%;
    }
}

        .form-heading{
            color:var(--dlsu-green-dark);
            font-size:2.2rem;
            font-weight:800;
            margin-bottom:8px;
        }

        .form-subheading{
            color:var(--muted);
            font-size:1.04rem;
            line-height:1.7;
            margin-bottom:26px;
        }

        .alert{
            border-radius:16px;
            font-size:1rem;
            padding:14px 16px;
            margin-bottom:18px;
        }

        .alert pre{
            margin:0;
            white-space:pre-wrap;
            word-break:break-word;
            font-size:0.92rem;
        }

        .form-label{
            font-size:1rem;
            font-weight:700;
            color:var(--dlsu-green-dark);
            margin-bottom:8px;
        }

        .form-control,
.form-select{

    height:58px;

    border-radius:18px;

    border:1px solid rgba(11,93,70,0.12);

    background:rgba(255,255,255,0.85);

    font-size:1rem;

    padding:12px 18px;

    transition:all 0.25s ease;

    box-shadow:
    inset 0 1px 2px rgba(0,0,0,0.04);
}

.form-control:focus,
.form-select:focus{

    transform:translateY(-1px);

    border-color:var(--dlsu-green);

    background:#fff;

    box-shadow:
    0 0 0 0.22rem rgba(11,93,70,0.12),
    0 8px 20px rgba(11,93,70,0.08);
}

        .form-control::placeholder{
            color:#8a9892;
        }

        .form-control:focus,
        .form-select:focus{
            border-color:var(--dlsu-green);
            box-shadow:0 0 0 0.22rem rgba(11,93,70,0.14);
            background:#ffffff;
        }

        .input-note{
            font-size:0.9rem;
            color:#6b7c75;
            margin-top:6px;
        }

        .btn-register{

    width:100%;
    height:60px;

    border:none;

    border-radius:18px;

    background:
    linear-gradient(135deg, #0b5d46, #0f7a5c);

    color:white;

    font-size:1.05rem;
    font-weight:800;

    letter-spacing:0.3px;

    transition:0.3s ease;

    box-shadow:
    0 15px 30px rgba(11,93,70,0.25);
}

.btn-register:hover{

    transform:translateY(-2px);

    background:
    linear-gradient(135deg, #0f7a5c, #11a177);

    box-shadow:
    0 18px 35px rgba(11,93,70,0.35);
}

        .bottom-link{
            margin-top:18px;
            text-align:center;
            color:var(--muted);
            font-size:1rem;
        }

        .bottom-link a{
            color:var(--dlsu-green);
            font-weight:800;
            text-decoration:none;
        }

        .bottom-link a:hover{
            text-decoration:underline;
        }

        .accessibility-note{
            margin-top:18px;
            padding:14px 16px;
            border-radius:16px;
            background:var(--dlsu-green-soft);
            color:var(--dlsu-green-dark);
            font-size:0.96rem;
            line-height:1.6;
            border-left:5px solid var(--dlsu-gold);
        }

        .field-grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:16px;
        }

        @media (max-width: 980px){
            .portal-shell{
                grid-template-columns:1fr;
            }

            .branding-panel,
            .form-panel{
                padding:34px 24px;
            }

            .branding-panel h1{
                font-size:2.35rem;
            }

            .form-heading{
                font-size:1.9rem;
            }
        }

        @media (max-width: 640px){
            .field-grid{
                grid-template-columns:1fr;
                gap:0;
            }

            .branding-panel h1{
                font-size:2rem;
            }

            .branding-panel p,
            .heritage-list,
            .form-subheading,
            .bottom-link,
            .accessibility-note{
                font-size:1rem;
            }

            .form-control,
            .form-select,
            .btn-register{
                height:54px;
            }
        }
    </style>
</head>
<body>
    <div class="portal-shell">
        <section class="branding-panel">
            <div class="branding-content">
                <div class="portal-tag">CEAT Role-Based Web Portal</div>
                <div class="crest-line"></div>
                <h1>Create your academic portal account</h1>
                <p>
                    A secure and welcoming digital space for document management,
                    appointment scheduling, and role-based services for students,
                    professors, and administrators.
                </p>

                <div class="heritage-box">
                    <div class="heritage-title">A design with a formal academic feel</div>
                    <ul class="heritage-list">
                        <li>Clear and readable for all age groups</li>
                        <li>Structured for both students and professors</li>
                        <li>Inspired by a calm, refined green campus identity</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="form-panel">
            <div class="form-wrap">
                <div class="register-logo">

    <div class="logo-circle">
        <img src="images/logo.jpg" alt="CEAT Logo">
    </div>

</div>
                <div class="form-heading">Register</div>
                <div class="form-subheading">
                    Please complete the form below to access the CEAT Portal.
                </div>

                <?php echo $message; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            class="form-control"
                            placeholder="Enter your complete name"
                            required
                        >
                    </div>

                    <div class="field-grid">
                        <div class="mb-3">
                            <label for="student_number" class="form-label">Student / Employee Number</label>
                            <input
                                type="text"
                                id="student_number"
                                name="student_number"
                                class="form-control"
                                placeholder="Enter your ID number"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select id="role" name="role" class="form-select" required>
                                <option value="">Select your role</option>
                                <option value="student">Student</option>
                                <option value="professor">Professor</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
    <label for="course" class="form-label">Course / Department</label>
    <select id="course" name="course" class="form-select" required>
        <option value="">Select your course</option>
        <option value="Architecture">Architecture</option>
        <option value="Civil Engineering">Civil Engineering</option>
        <option value="Computer Engineering">Computer Engineering</option>
        <option value="Electrical Engineering">Electrical Engineering</option>
        <option value="Electronics Engineering">Electronics Engineering</option>
        <option value="Industrial Engineering">Industrial Engineering</option>
        <option value="Mechanical Engineering">Mechanical Engineering</option>
        <option value="Sanitary Engineering">Sanitary Engineering</option>
        <option value="Multimedia Arts">Multimedia Arts</option>
    </select>
</div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="Enter your email address"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            class="form-control"
                            placeholder="Enter your mobile number"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Create your password"
                            required
                        >
                        <div class="input-note">Use a password that is easy for you to remember but hard for others to guess.</div>
                    </div>

                    <button type="submit" class="btn-register">Create Account</button>
                </form>

                <div class="bottom-link">
                    Already have an account?
                    <a href="login.php">Sign in here</a>
                </div>

                <div class="accessibility-note">
                    This page uses larger text, strong contrast, and simple labels so it remains clear for both younger and older users.
                </div>
            </div>
        </section>
    </div>
</body>
<style>
    /* LOGO */

.register-logo{
    display:flex;
    justify-content:center;
    margin-bottom:22px;
}

.logo-circle{

    width:110px;
    height:110px;

    border-radius:50%;

    background:rgba(255,255,255,0.65);

    backdrop-filter:blur(12px);

    display:flex;
    justify-content:center;
    align-items:center;

    box-shadow:
    0 10px 25px rgba(0,0,0,0.12),
    inset 0 0 12px rgba(255,255,255,0.35);

    border:3px solid rgba(255,255,255,0.4);
}

.logo-circle img{

    width:78px;
    height:78px;

    object-fit:contain;
    border-radius:50%;
}
</style>
</html>
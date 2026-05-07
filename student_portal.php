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
$sql = "SELECT id, full_name, student_number, email, phone, role, course, profile_image
        FROM ceatuser 
        WHERE id = ?";
$stmt = sqlsrv_query($conn, $sql, array($userId));
$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$profileImage = $user['profile_image'] ?? null;
$course = $user['course'] ?? 'Not Assigned';

$course = $user['course'];

$sqlNews = "SELECT a.*, u.full_name 
            FROM announcements a
            JOIN ceatuser u ON a.posted_by = u.id
            WHERE a.course = ?
            ORDER BY a.created_at DESC";

$stmtNews = sqlsrv_query($conn, $sqlNews, array($course));

// FETCH NEWS POSTS
$sqlNews = "
    SELECT n.*, u.full_name 
    FROM news n
    JOIN ceatuser u ON u.id = n.professor_id
    ORDER BY n.created_at DESC
";

$stmtNews = sqlsrv_query($conn, $sqlNews);

if ($stmtNews === false) {
    die(print_r(sqlsrv_errors(), true));
}

$fullName = $user['full_name'] ?? ($user['name'] ?? 'Portal User');
$studentNumber = $user['student_number'] ?? 'N/A';
$phone = $user['phone'] ?? 'N/A';
$email = $user['email'] ?? 'N/A';
$role = ucfirst($user['role'] ?? 'Student');

function findLogoPath() {
    $candidates = [
        'images/logo.png',
        'images/logo.jpg',
        'images/logo.jpeg',
        'images/logo.webp'
    ];

    foreach ($candidates as $file) {
        if (file_exists($file)) {
            return $file;
        }
    }

    return 'images/logo.jpg';
}

$logoPath = findLogoPath();
$initial = strtoupper(substr(trim($fullName), 0, 1));
$course = $user['course'];

$sqlNews = "SELECT a.*, u.full_name 
            FROM announcements a
            JOIN ceatuser u ON a.posted_by = u.id
            WHERE a.course = ?
            ORDER BY a.created_at DESC";

$stmtNews = sqlsrv_query($conn, $sqlNews, array($course));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEAT Portal Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        *{
    box-sizing:border-box;
}
        :root{
            --sidebar-dark:#024d38;
            --sidebar-darker:#013629;
            --brand-green:#04965d;
            --brand-green-dark:#046b47;
            --brand-green-soft:#e8f5ee;
            --page-bg:#eef3f1;
            --card-bg:#ffffff;
            --text-main:#17362c;
            --text-soft:#60766d;
            --shadow-soft:0 12px 28px rgba(14, 45, 34, 0.08);
            --shadow-top:0 10px 30px rgba(0, 0, 0, 0.10);
            --radius-xl:28px;
            --radius-lg:24px;
            --radius-md:20px;
        }

        *{
            box-sizing:border-box;
        }

        body{
    margin:0;
    font-family:"Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    background:var(--page-bg);
    color:var(--text-main);
    overflow-x:hidden;
}
        html, body{
    overflow-x:hidden;
}

        .app{
            display:flex;
            min-height:100vh;
            overflow:hidden;
        }

        .sidebar{
            width:286px;
            background:linear-gradient(180deg, #04553f 0%, #01392b 100%);
            color:#fff;
            padding:22px 18px 24px;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            position:relative;
            overflow:hidden;
        }

        .sidebar::before{
            content:"";
            position:absolute;
            inset:0;
            background:
                radial-gradient(circle at left top, rgba(24, 175, 110, 0.22), transparent 28%),
                radial-gradient(circle at 10% 88%, rgba(12, 110, 74, 0.45), transparent 30%);
            pointer-events:none;
            z-index:0;
        }

        .sidebar::after{
            content:"";
            position:absolute;
            left:-28px;
            bottom:-44px;
            width:210px;
            height:210px;
            background:
                radial-gradient(circle at 40% 40%, rgba(35, 170, 109, 0.35), rgba(35, 170, 109, 0) 60%);
            border-radius:50%;
            pointer-events:none;
            z-index:0;
        }

        .sidebar-top,
        .sidebar-bottom{
            position:relative;
            z-index:1;
        }

        .logo-wrap{
            margin-bottom:12px;
        }

        .logo-wrap img{
            width:82px;
            height:82px;
            object-fit:cover;
            border-radius:50%;
            background:#fff;
            padding:4px;
            display:block;
        }

        .brand-title{
            font-size:2.1rem;
            font-weight:800;
            line-height:1.05;
            margin:0 0 6px;
            letter-spacing:0.1px;
        }

        .brand-subtitle{
            font-size:0.95rem;
            line-height:1.45;
            color:rgba(255,255,255,0.88);
            margin:0 0 24px;
            max-width:210px;
        }

        .access-chip{
            display:flex;
            align-items:center;
            gap:12px;
            background:rgba(28, 151, 98, 0.30);
            border-radius:18px;
            padding:13px 16px;
            font-size:1rem;
            font-weight:700;
            margin-bottom:16px;
            box-shadow:inset 0 0 0 1px rgba(255,255,255,0.06);
        }

        .nav{
            display:flex;
            flex-direction:column;
            gap:8px;
        }

        .nav a{
            text-decoration:none;
            color:#fff;
            display:flex;
            align-items:center;
            gap:14px;
            padding:13px 14px;
            border-radius:16px;
            font-size:0.98rem;
            font-weight:600;
            transition:0.18s ease;
            cursor:pointer;
        }

        .nav a.active,
        .nav a:hover{
            background:rgba(255,255,255,0.08);
        }

        .nav i{
            font-size:1.45rem;
            line-height:1;
        }

        .logout-link{
            text-decoration:none;
            color:#ff6b57;
            font-weight:800;
            display:flex;
            align-items:center;
            gap:14px;
            font-size:1rem;
            padding:10px 6px;
            margin-top:16px;
        }

        .logout-link i{
            font-size:1.55rem;
        }

        .sidebar-statue{
            position:absolute;
            left:0;
            bottom:0;
            width:100%;
            height:220px;
            background:
                linear-gradient(180deg, rgba(1,57,43,0) 0%, rgba(1,57,43,0.10) 20%, rgba(1,57,43,0.72) 100%),
                linear-gradient(90deg, rgba(1,57,43,0.30) 0%, rgba(1,57,43,0.08) 35%, rgba(1,57,43,0.55) 100%),
                url("images/statue.jpeg") center bottom / cover no-repeat;
            opacity:0.35;
            mix-blend-mode:screen;
            pointer-events:none;
            z-index:0;
        }

        .sidebar-statue::after{
            content:"";
            position:absolute;
            inset:0;
            background:
                radial-gradient(circle at 30% 25%, rgba(255,255,255,0.10), transparent 22%),
                linear-gradient(180deg, rgba(6,94,70,0.00) 0%, rgba(6,94,70,0.22) 55%, rgba(1,57,43,0.68) 100%);
        }

        .main{
            flex:1;
            padding:22px 26px 26px;
            overflow-x:hidden;
        }

        .signin-avatar img {
    border-radius: 50%;
}

        .topbar{
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            gap:16px;
            margin-bottom:16px;
        }

        .welcome h1{
            margin:0;
            font-size:2.05rem;
            font-weight:400;
            color:#18483b;
        }

        .welcome h1 strong{
            font-weight:800;
            color:#0b5f46;
        }

        .welcome p{
            margin:8px 0 0;
            font-size:1.04rem;
            color:#485f56;
        }

        .top-right{
            display:flex;
            align-items:center;
            gap:14px;
        }

        .notif-btn{
            width:66px;
            height:66px;
            background:#fff;
            border-radius:50%;
            box-shadow:var(--shadow-top);
            display:flex;
            align-items:center;
            justify-content:center;
            position:relative;
        }

        .notif-btn i{
            font-size:1.7rem;
            color:#66786f;
        }

        .notif-dot{
            position:absolute;
            right:14px;
            top:13px;
            width:12px;
            height:12px;
            background:#f0b126;
            border:2px solid #fff;
            border-radius:50%;
        }

        .signin-chip{
            min-width:310px;
            background:#fff;
            border-radius:34px;
            box-shadow:var(--shadow-top);
            display:flex;
            align-items:center;
            gap:14px;
            padding:11px 18px 11px 12px;
        }

        .signin-avatar{
            width:56px;
            height:56px;
            border-radius:50%;
            background:linear-gradient(180deg, #0a8d5c 0%, #046d48 100%);
            display:flex;
            align-items:center;
            justify-content:center;
            color:#fff;
            font-size:1.25rem;
            font-weight:800;
            flex-shrink:0;
        }

        .signin-text small{
            display:block;
            font-size:0.95rem;
            color:#5e736b;
            margin-bottom:2px;
        }

        .signin-text strong{
            display:block;
            font-size:1.05rem;
            color:#0b8f5c;
            font-weight:800;
        }

        .hero{
            position:relative;
            border-radius:30px;
            overflow:hidden;
            min-height:222px;
            padding:30px 30px 88px;
            background:
                linear-gradient(90deg, rgba(2,89,62,0.96) 0%, rgba(4,135,92,0.92) 56%, rgba(6,126,83,0.88) 100%);
            box-shadow:var(--shadow-soft);
        }

        .hero::before{
            content:"";
            position:absolute;
            inset:0;
            background:
                radial-gradient(circle at 73% 28%, rgba(255,255,255,0.10), transparent 10%),
                radial-gradient(circle at 80% 14%, rgba(255,255,255,0.08), transparent 6%),
                radial-gradient(circle at 84% 36%, rgba(255,255,255,0.08), transparent 5%),
                radial-gradient(circle at 88% 20%, rgba(255,255,255,0.08), transparent 5%);
        }

        .hero-campus{
            position:absolute;
            right:0;
            top:0;
            bottom:0;
            width:38%;
            background:
                linear-gradient(90deg, rgba(3,100,69,0.0), rgba(3,100,69,0.18)),
                url('images/campus.jpg') center right/cover no-repeat;
            opacity:0.34;
            filter:saturate(0.85) brightness(1.06);
            clip-path:polygon(12% 0, 100% 0, 100% 100%, 0 100%);
        }

        .hero-star{
            position:absolute;
            right:40px;
            top:22px;
            font-size:7.3rem;
            line-height:1;
            color:#00b05e;
            text-shadow:
                0 0 10px rgba(0,0,0,0.08),
                0 0 24px rgba(0,255,153,0.14);
            transform:rotate(2deg);
            opacity:0.96;
        }

        .hero h2,
        .hero p{
            position:relative;
            z-index:2;
        }

        .hero h2{
            margin:0 0 10px;
            color:#fff;
            font-size:1.98rem;
            font-weight:800;
            letter-spacing:0.1px;
        }

        .hero p{
            margin:0;
            color:rgba(255,255,255,0.95);
            font-size:1.02rem;
            max-width:760px;
        }

        .stats{
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:16px;
    margin:0 22px 18px;
    margin-top:-60px;
    position:relative;
    z-index:3;
}

        .stat-card{
            background:#fff;
            border-radius:24px;
            box-shadow:var(--shadow-soft);
            padding:24px 24px;
            display:flex;
            align-items:center;
            gap:18px;
        }

        .stat-icon{
            width:78px;
            height:78px;
            border-radius:22px;
            background:#f4fbf7;
            display:flex;
            align-items:center;
            justify-content:center;
            color:#09985f;
            font-size:2.3rem;
            flex-shrink:0;
        }

        .stat-label{
            font-size:1rem;
            color:#40584f;
            margin:0 0 6px;
        }

        .stat-value{
            margin:0;
            font-size:1.18rem;
            color:#09985f;
            font-weight:800;
        }

        

        .panel{
            background:#fff;
            border-radius:28px;
            box-shadow:var(--shadow-soft);
            padding:24px;
        }

        .panel h3{
            margin:0 0 22px;
            font-size:1.45rem;
            line-height:1.25;
            color:#122b23;
            font-weight:800;
        }

        .big-btn{
            display:flex;
            align-items:center;
            justify-content:center;
            gap:12px;
            text-decoration:none;
            background:#09985f;
            color:#fff;
            border-radius:18px;
            font-size:1rem;
            font-weight:800;
            padding:20px 18px;
            margin-bottom:20px;
            transition:0.18s ease;
            cursor:pointer;
        }

        .big-btn:hover{
            background:#078651;
            color:#fff;
        }

        .big-btn i{
            font-size:1.35rem;
        }

        .small-btn{
            display:flex;
            align-items:center;
            justify-content:space-between;
            text-decoration:none;
            padding:22px 20px;
            border-radius:20px;
            background:#fbfcfb;
            color:#0c8e5b;
            box-shadow:0 6px 16px rgba(0,0,0,0.05);
            font-size:0.98rem;
            font-weight:800;
        }

        .small-btn:hover{
            color:#0a774d;
            background:#f5faf7;
        }

        .small-btn-left{
            display:flex;
            align-items:center;
            gap:12px;
        }

        .small-btn-left i{
            font-size:1.35rem;
        }

        .small-btn-arrow{
            font-size:2rem;
            color:#1b9c67;
            line-height:1;
        }

        
        
        .info{
            margin-bottom:18px;
        }

        .info-label{
            margin:0 0 4px;
            font-size:0.95rem;
            font-weight:800;
            color:#112c23;
        }

        .info-value{
            margin:0;
            font-size:0.98rem;
            color:#465d54;
            line-height:1.45;
            word-break:break-word;
        }

        .verified{
            width:100%;
            border:none;
            border-radius:18px;
            background:#089857;
            color:#fff;
            padding:18px 18px;
            font-size:1.02rem;
            font-weight:800;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:10px;
            margin-top:10px;
        }

        /* MODAL */
        .modal-overlay{
            position:fixed;
            inset:0;
            background:rgba(0,0,0,0.45);
            display:none;
            align-items:center;
            justify-content:center;
            z-index:9999;
            padding:20px;
        }

        .modal-box{
            width:min(760px, 100%);
            height:min(560px, 90vh);
            background:#fff;
            border-radius:22px;
            overflow:hidden;
            box-shadow:0 24px 60px rgba(0,0,0,0.25);
            animation:popup 0.22s ease;
        }

        .modal-header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:16px 20px;
            background:#089857;
            color:#fff;
        }

        .modal-header h3{
            margin:0;
            font-size:1.1rem;
            font-weight:800;
        }

        .modal-close{
            border:none;
            background:none;
            color:#fff;
            font-size:1.5rem;
            cursor:pointer;
            line-height:1;
        }

        .modal-frame{
            width:100%;
            height:calc(100% - 64px);
            border:none;
            background:#fff;
        }

        @keyframes popup{
            from{
                transform:scale(0.96);
                opacity:0;
            }
            to{
                transform:scale(1);
                opacity:1;
            }
        }

        @media (max-width: 1280px){
            .grid{
                grid-template-columns:1fr;
            }
        }

        @media (max-width: 1080px){
            .app{
                flex-direction:column;
            }

            .sidebar{
                width:100%;
            }

            .topbar{
                flex-direction:column;
                align-items:flex-start;
            }

            .top-right{
                width:100%;
                flex-wrap:wrap;
            }

            .signin-chip{
                width:100%;
                min-width:0;
            }

            .stats{
                grid-template-columns:1fr;
                margin:20px 0 18px;
            }

            .hero{
                padding-bottom:30px;
            }
        }

        @media (max-width: 700px){
            .main{
                padding:18px 14px 22px;
            }

            .welcome h1{
                font-size:1.65rem;
            }

            .hero h2{
                font-size:1.45rem;
            }

            .hero-star{
                font-size:5rem;
                right:18px;
                top:14px;
            }

            .hero-campus{
                width:50%;
            }

            .stat-card{
                padding:20px;
            }

            .stat-icon{
                width:68px;
                height:68px;
                font-size:2rem;
            }

            .modal-box{
                height:88vh;
            }
        }
    </style>
</head>

<body>
<div class="app">
    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="logo-wrap">
                <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="CEAT Logo">
            </div>

            <h2 class="brand-title">CEAT Portal</h2>
            <p class="brand-subtitle">Document Management and Appointment Scheduling</p>

            <div class="access-chip">
                <i class="bi bi-person-check"></i>
                <?php echo htmlspecialchars($role); ?> Access
            </div>

            <nav class="nav">
                <a href="student_portal.php" class="active">

                    <i class="bi bi-grid"></i>
                    Dashboard
                </a>

                <a href="personal_info.php">
                    <i class="bi bi-person-circle"></i>
                    Personal Info
                </a>

                <a href="#" onclick="openMsgModal(); return false;">
                    <i class="bi bi-chat-dots"></i>
                    Messages
                </a>

                <a href="#" onclick="openUploadModal(); return false;">
                    <i class="bi bi-file-earmark-arrow-up"></i>
                    Upload Document
                </a>

                <a href="my_documents.php">
                    <i class="bi bi-file-earmark-text"></i>
                    My Documents
                </a>

                <a href="book_appointment.php">
                    <i class="bi bi-calendar-plus"></i>
                    Book Appointment
                </a>

                <a href="my_appointments.php">
                    <i class="bi bi-calendar-check"></i>
                    My Appointments
                </a>

                <a href="logout.php" class="logout-link">
                <i class="bi bi-box-arrow-left"></i>
                Logout
            </a>
            </nav>
        </div>

        

        <div class="sidebar-statue"></div>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="welcome">
                <h1>Welcome, <strong><?php echo htmlspecialchars($fullName); ?></strong></h1>
                <p>A secure and user-friendly portal for students and professors.</p>
            </div>

         <div class="top-right">

    <?php
    $sqlNotif = "SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmtNotif = sqlsrv_query($conn, $sqlNotif, array($userId));
    $notifRow = sqlsrv_fetch_array($stmtNotif, SQLSRV_FETCH_ASSOC);
    $notifCount = $notifRow['total'] ?? 0;
    ?>

    <a href="#" class="notif-btn" onclick="openNotifModal(); return false;">
        <i class="bi bi-bell"></i>

        <?php if ($notifCount > 0) { ?>
            <span class="notif-dot"></span>
        <?php } ?>
    </a>

    <div class="signin-chip">
        <a href="personal_info.php">

        <div class="signin-avatar" style="overflow:hidden; padding:0;">

    <?php if (!empty($profileImage)) { ?>
        <img src="<?= htmlspecialchars($profileImage) ?>" 
             style="width:100%; height:100%; object-fit:cover;">
    <?php } else { ?>
        <?= $initial ?>
    <?php } ?>
</a>

</div>
        <div class="signin-text">
            
            <small>Signed in as</small>
                        <strong><?php echo htmlspecialchars($fullName); ?></strong>
            <small><?php echo htmlspecialchars($course); ?></small>
        </div>
    </div>

</div>
        </div>

        <section class="hero">
            <div class="hero-campus"></div>
            <div class="hero-star">★</div>
            <h2>Academic Services at Your Fingertips</h2>
            <p>Manage academic documents, schedule appointments, and access your profile.</p>
        </section>

        <section class="stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div>
                    <p class="stat-label">Documents</p>
                    <p class="stat-value">Ready</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-calendar2-check"></i>
                </div>
                <div>
                    <p class="stat-label">Appointments</p>
                    <p class="stat-value">Active</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <p class="stat-label">Account</p>
                    <p class="stat-value">Verified</p>
                </div>
            </div>

            
        </section>

       <!-- REPLACE YOUR CURRENT ANNOUNCEMENTS / NEWS SECTION WITH THIS -->

<section class="panel announcements-panel" style="margin-bottom: 24px;">

    <div id="notif-<?= $n['id'] ?>" style="
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:22px;
        flex-wrap:wrap;
        gap:12px;
    ">
        <div>
            <h3 style="
                margin:0;
                font-size:1.6rem;
                font-weight:800;
                color:#12352b;
            ">
                 Announcements & News
            </h3>

            <p style="
                margin:6px 0 0;
                color:#6b7d75;
                font-size:14px;
            ">
                Stay updated with important academic announcements and latest news
            </p>
        </div>

        <div style="
            background:linear-gradient(135deg,#089857,#0bbd6f);
            color:#fff;
            padding:10px 18px;
            border-radius:14px;
            font-weight:700;
            font-size:14px;
            box-shadow:0 8px 20px rgba(8,152,87,0.15);
        ">
            Latest Updates
        </div>
    </div>


    <?php
    $hasNews = false;

    while ($news = sqlsrv_fetch_array($stmtNews, SQLSRV_FETCH_ASSOC)) {
        $hasNews = true;
    ?>

        <div id="notif-<?= $n['id'] ?>" style="
    background: <?= $n['is_read'] ? '#ffffff' : '#f0fff7' ?>;
    border: 1px solid <?= $n['is_read'] ? '#e7ece9' : '#b8efd3' ?>;
    border-left: 6px solid <?= $n['is_read'] ? '#d7dfdb' : '#089857' ?>;
    border-radius: 16px;
    padding: 18px;
    margin-bottom: 14px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.04);
    transition: 0.2s;
">

            <!-- TOP SECTION -->
            <div style="
                display:flex;
                justify-content:space-between;
                align-items:flex-start;
                gap:18px;
                flex-wrap:wrap;
                margin-bottom:14px;
            ">

                <div style="flex:1;">
                    <h4 style="
                        margin:0 0 8px;
                        font-size:1.2rem;
                        font-weight:800;
                        color:#17362c;
                        line-height:1.4;
                    ">
                        <?= htmlspecialchars($news['title']) ?>
                    </h4>

                    <div style="
                        display:flex;
                        flex-wrap:wrap;
                        gap:14px;
                        color:#6b7d75;
                        font-size:13px;
                        font-weight:500;
                    ">
                        <span>
                            <i class="bi bi-person-circle"></i>
                            Posted by <?= htmlspecialchars($news['full_name']) ?>
                        </span>

                        <span>
                            <i class="bi bi-clock-history"></i>
                            <?= $news['created_at']->format('F d, Y • h:i A') ?>
                        </span>
                    </div>
                </div>

                <div style="
                    background:#eaf8f1;
                    color:#089857;
                    padding:8px 14px;
                    border-radius:12px;
                    font-size:13px;
                    font-weight:700;
                    white-space:nowrap;
                ">
                    Announcement
                </div>
            </div>


            <!-- CONTENT -->
            <div style="
                font-size:15px;
                line-height:1.8;
                color:#445c53;
                margin-bottom:16px;
            ">
                <?= nl2br(htmlspecialchars($news['content'])) ?>
            </div>


            <!-- IMAGE -->
            <?php if (!empty($news['image_path'])) { ?>
                <div style="
                    border-radius:18px;
                    overflow:hidden;
                    margin-top:12px;
                    box-shadow:0 10px 20px rgba(0,0,0,0.05);
                ">
                    <img 
                        src="<?= htmlspecialchars($news['image_path']) ?>"
                        alt="Announcement Image"
                        style="
                            width:100%;
                            max-height:380px;
                            object-fit:cover;
                            display:block;
                        "
                    >
                </div>
            <?php } ?>

        </div>

    <?php } ?>


    <!-- EMPTY STATE -->
    <?php if (!$hasNews) { ?>
        <div style="
            text-align:center;
            padding:60px 20px;
            background:#fafdfb;
            border-radius:20px;
            border:1px dashed #cfe6d8;
        ">
            <i class="bi bi-megaphone"
               style="
                    font-size:60px;
                    color:#b8d8c5;
                    display:block;
                    margin-bottom:16px;
               ">
            </i>

            <h4 style="
                margin:0 0 10px;
                color:#1b4332;
                font-size:1.2rem;
            ">
                No Announcements Yet
            </h4>

            <p style="
                margin:0;
                color:#6b7d75;
                font-size:14px;
            ">
                New academic updates and announcements will appear here.
            </p>
        </div>
    <?php } ?>

</section>            
<div id="uploadModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Upload Document</h3>
            <button class="modal-close" type="button" onclick="closeUploadModal()">×</button>
        </div>
        <iframe src="upload_document.php" class="modal-frame"></iframe>
    </div>
</div>



<script>
    function openUploadModal() {
        document.getElementById('uploadModal').style.display = 'flex';
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').style.display = 'none';
    }

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('uploadModal');
        if (event.target === modal) {
            closeUploadModal();
        }
    });

    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeUploadModal();
        }
    });

    function openNotifModal() {
    document.getElementById('notifModal').style.display = 'flex';
}

function closeNotifModal() {
    document.getElementById('notifModal').style.display = 'none';
}

// close when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('notifModal');
    if (event.target === modal) {
        closeNotifModal();
    }
});

// close with ESC
window.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeNotifModal();
    }
});

function openMsgModal() {
    document.getElementById('msgModal').style.display = 'flex';
}

function closeMsgModal() {
    document.getElementById('msgModal').style.display = 'none';
}
function deleteNotif(id, btn) {

    if (!confirm("Delete this notification?")) return;

    fetch("delete_notification.php?id=" + id)
    .then(response => response.text())
    .then(() => {

        // Remove notification smoothly
        const notif = document.getElementById("notif-" + id);
        notif.style.transition = "0.3s";
        notif.style.opacity = "0";
        notif.style.transform = "translateX(20px)";

        setTimeout(() => {
            notif.remove();
        }, 300);

    })
    .catch(error => console.error(error));
}

</script>


<!-- REPLACE YOUR ENTIRE notifModal SECTION WITH THIS CLEAN VERSION -->
<div id="notifModal" class="modal-overlay">
    <div class="modal-box" style="width:min(700px,100%); max-height:85vh;">

        <div class="modal-header">
            <h3><i class="bi bi-bell-fill"></i> Notifications</h3>
            <button class="modal-close" onclick="closeNotifModal()">×</button>
        </div>

        <div style="padding:20px; background:#f8fcfa; border-bottom:1px solid #e6f2ec;">
            <a href="mark_all_read.php"
               style="
                    display:inline-flex;
                    align-items:center;
                    gap:8px;
                    text-decoration:none;
                    background:linear-gradient(135deg,#089857,#0bbd6f);
                    color:#fff;
                    padding:12px 18px;
                    border-radius:14px;
                    font-weight:700;
                    font-size:14px;
                    box-shadow:0 8px 20px rgba(8,152,87,0.15);
               ">
                <i class="bi bi-check2-all"></i>
                Mark All as Read
            </a>
        </div>

        <div style="
            padding:20px;
            overflow-y:auto;
            max-height:500px;
            background:#ffffff;
        ">

            <?php
            $sqlNotifList = "
                SELECT id, message, is_read, created_at
                FROM notifications
                WHERE user_id = ?
                ORDER BY created_at DESC
            ";

            $stmtNotifList = sqlsrv_query($conn, $sqlNotifList, array($userId));

            if ($stmtNotifList === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            $hasNotifications = false;

            while ($n = sqlsrv_fetch_array($stmtNotifList, SQLSRV_FETCH_ASSOC)) {
                $hasNotifications = true;
            ?>

                <div style="
                    background: <?= $n['is_read'] ? '#ffffff' : '#f0fff7' ?>;
                    border: 1px solid <?= $n['is_read'] ? '#e7ece9' : '#b8efd3' ?>;
                    border-left: 6px solid <?= $n['is_read'] ? '#d7dfdb' : '#089857' ?>;
                    border-radius: 16px;
                    padding: 18px;
                    margin-bottom: 14px;
                    box-shadow: 0 6px 16px rgba(0,0,0,0.04);
                    transition: 0.2s;
                ">

                    <div style="
                        display:flex;
                        justify-content:space-between;
                        align-items:flex-start;
                        gap:15px;
                    ">

                        <div style="flex:1;">
                            <div style="
                                font-weight:700;
                                font-size:15px;
                                color:#17362c;
                                margin-bottom:8px;
                                line-height:1.5;
                            ">
                                <?= htmlspecialchars($n['message']) ?>
                            </div>

                            <small style="
                                color:#6b7d75;
                                font-size:13px;
                            ">
                                <i class="bi bi-clock"></i>
                                <?= $n['created_at']->format('Y-m-d h:i A') ?>
                            </small>
                        </div>

                        <?php if (!$n['is_read']) { ?>
                            <a href="mark_read.php?id=<?= $n['id'] ?>"
                               style="
                                    text-decoration:none;
                                    background:#089857;
                                    color:#fff;
                                    padding:10px 14px;
                                    border-radius:12px;
                                    font-size:13px;
                                    font-weight:700;
                                    white-space:nowrap;
                               ">
                                Mark as Read
                            </a>
                        <?php  } ?>

                        <!-- ✅ DELETE BUTTON -->
    <button 
    onclick="deleteNotif(<?= $n['id'] ?>, this)" 
    style="
        background:#dc3545;
        color:#fff;
        border:none;
        padding:8px 12px;
        border-radius:10px;
        font-size:12px;
        font-weight:700;
        cursor:pointer;
    ">
    Delete
</button>

                    </div>
                </div>

            <?php } ?>

            <?php if (!$hasNotifications) { ?>
                <div style="
                    text-align:center;
                    padding:50px 20px;
                    color:#6b7d75;
                ">
                    <i class="bi bi-bell-slash"
                       style="
                            font-size:52px;
                            display:block;
                            margin-bottom:15px;
                            color:#b5c7be;
                       ">
                    </i>

                    <h4 style="margin:0 0 8px;">No Notifications Yet</h4>
                    <p style="margin:0; font-size:14px;">
                        You're all caught up.
                    </p>
                </div>
            <?php } ?>

        </div>
    </div>
</div>

<div id="msgModal" class="modal-overlay">
    <div class="modal-box" style="height:auto; max-height:600px;">

        <div class="modal-header">
            <h3>Messages</h3>
            <button class="modal-close" onclick="closeMsgModal()">×</button>
        </div>

        <div style="padding:15px; overflow-y:auto; max-height:520px;">

            <?php
            // GET latest conversations (last message per user)
            $sqlMsg = "
SELECT TOP 20 
    m.*,
    u.full_name
FROM messages m
JOIN ceatuser u
    ON u.id = CASE 
        WHEN m.sender_id = ? THEN m.receiver_id
        ELSE m.sender_id
    END
WHERE m.sender_id = ? OR m.receiver_id = ?
ORDER BY m.created_at DESC
";

$params = array($userId, $userId, $userId);

$stmtMsg = sqlsrv_query($conn, $sqlMsg, $params);

if ($stmtMsg === false) {
    die(print_r(sqlsrv_errors(), true));
}

$stmtMsg = sqlsrv_query($conn, $sqlMsg, array($userId, $userId, $userId));

            $stmtMsg = sqlsrv_query($conn, $sqlMsg, array($userId, $userId, $userId));

            $seen = [];

while ($m = sqlsrv_fetch_array($stmtMsg, SQLSRV_FETCH_ASSOC)) {

    $chatUserId = ($m['sender_id'] == $userId)
        ? $m['receiver_id']
        : $m['sender_id'];

    if (in_array($chatUserId, $seen)) continue;
    $seen[] = $chatUserId;
            ?>
                <a href="chat.php?user=<?= $m['sender_id'] == $userId ? $m['receiver_id'] : $m['sender_id'] ?>"
                   style="
                        display:block;
                        padding:12px;
                        margin-bottom:10px;
                        border-radius:12px;
                        background:#f5faf7;
                        text-decoration:none;
                        color:#000;
                        border-left:5px solid #09985f;
                   ">
                    <b><?= htmlspecialchars($m['full_name']) ?></b><br>
                    <span><?= htmlspecialchars($m['message']) ?></span><br>
                    <small style="color:gray;">
                        <?= $m['created_at']->format('Y-m-d H:i') ?>
                    </small>
                </a>
            <?php } ?>

        </div>
    </div>
</div>



</body>
</html>


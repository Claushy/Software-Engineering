<?php
require 'auth.php';
require 'connector.php';
requireRole('admin');

$userCountStmt = sqlsrv_query($conn, "SELECT COUNT(*) AS total_users FROM ceatuser");
$docCountStmt = sqlsrv_query($conn, "SELECT COUNT(*) AS total_docs FROM documents");
$appCountStmt = sqlsrv_query($conn, "SELECT COUNT(*) AS total_apps FROM appointments");

$userCount = sqlsrv_fetch_array($userCountStmt, SQLSRV_FETCH_ASSOC);
$docCount = sqlsrv_fetch_array($docCountStmt, SQLSRV_FETCH_ASSOC);
$appCount = sqlsrv_fetch_array($appCountStmt, SQLSRV_FETCH_ASSOC);
?>
<?php
// USERS BY ROLE
$sqlRoles = "
SELECT role, COUNT(*) as total 
FROM ceatuser 
GROUP BY role
";
$stmtRoles = sqlsrv_query($conn, $sqlRoles);

$roles = [];
$roleCounts = [];

while ($row = sqlsrv_fetch_array($stmtRoles, SQLSRV_FETCH_ASSOC)) {
    $roles[] = $row['role'];
    $roleCounts[] = $row['total'];
}

// APPOINTMENTS PER DAY (last 7 days)
$sqlTrend = "
SELECT 
    FORMAT(appointment_date, 'yyyy-MM-dd') as date,
    COUNT(*) as total
FROM appointments
GROUP BY FORMAT(appointment_date, 'yyyy-MM-dd')
ORDER BY date DESC
";
$stmtTrend = sqlsrv_query($conn, $sqlTrend);

$dates = [];
$counts = [];

while ($row = sqlsrv_fetch_array($stmtTrend, SQLSRV_FETCH_ASSOC)) {
    $dates[] = $row['date'];
    $counts[] = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body{
    margin:0;
    font-family:"Segoe UI", sans-serif;
    background:#eef3f1;
}

canvas {
    max-height: 250px;
}

.app{ display:flex; }

/* SIDEBAR */
.sidebar{
    width:250px;
    background:#024d38;
    color:#fff;
    min-height:100vh;
    padding:20px;
}

.sidebar h2{ margin-bottom:20px; }

.sidebar a{
    display:block;
    color:#fff;
    text-decoration:none;
    padding:12px;
    border-radius:10px;
    margin-bottom:10px;
    font-weight:600;
}

.sidebar a:hover{
    background:rgba(255,255,255,0.1);
}

/* MAIN */
.main{
    flex:1;
    padding:20px 30px;
}

/* TOPBAR */
.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.topbar h1{
    margin:0;
    font-size:1.8rem;
}

/* STATS */
.stats{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;
    margin-bottom:20px;
}

.card{
    background:#fff;
    padding:20px;
    border-radius:16px;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
}


/* PANELS */
.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}

.panel{
    background:#fff;
    padding:20px;
    border-radius:16px;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
}

/* BUTTONS */
.btn{
    display:inline-block;
    padding:10px 15px;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
    margin-top:10px;
}

.btn-primary{
    background:#04965d;
    color:#fff;
}

.btn-outline{
    border:2px solid #04965d;
    color:#04965d;
}

.btn-outline:hover{
    background:#04965d;
    color:#fff;
}
</style>
</head>

<body>

<div class="app">

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>CEAT Admin</h2>

    <a href="#"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="manage_users.php"><i class="bi bi-people"></i> Users</a>
    <a href="my_documents.php"><i class="bi bi-file-earmark"></i> Documents</a>
    <a href="manage_appointments.php"><i class="bi bi-calendar"></i> Appointments</a>
    <a href="logout.php"><i class="bi bi-box-arrow-left"></i> Logout</a>
    <a href="generate_report.php"><i class="bi bi-graph-up"></i> Reports</a>
    
</div>

<!-- MAIN -->
<div class="main">

    <!-- TOP -->
    <div class="topbar">
        <h1>Admin Dashboard</h1>
        <div>Welcome, Admin</div>
    </div>

    <!-- STATS -->
    <div class="stats">

        <div class="card">
            <h4>Total Users</h4>
            <h2><?php echo $userCount['total_users']; ?></h2>
        </div>

        <div class="card">
            <h4>Total Documents</h4>
            <h2><?php echo $docCount['total_docs']; ?></h2>
        </div>

        <div class="card">
            <h4>Total Appointments</h4>
            <h2><?php echo $appCount['total_apps']; ?></h2>
        </div>

    </div>

    <div class="grid">

    <!-- USERS BY ROLE -->
    <div class="panel">
        <h3>User Distribution</h3>
        <canvas id="roleChart"></canvas>
    </div>

    <!-- APPOINTMENT TREND -->
    <div class="panel">
        <h3>Appointments Trend</h3>
        <canvas id="apptChart"></canvas>
    </div>

</div>

    <!-- PANELS -->
    <div class="grid">
        <div class="panel">
    <h3>Reports & Analytics</h3>
    <p>Generate system reports and summaries</p>

    <a href="generate_report.php" class="btn btn-primary">
        <i class="bi bi-file-earmark-bar-graph"></i>
        Generate Report
    </a>
</div>

        <div class="panel">
            <h3>User Management</h3>
            <a href="manage_users.php" class="btn btn-primary">Manage Users</a>
        </div>

        <div class="panel">
            <h3>System Monitoring</h3>
            <a href="my_documents.php" class="btn btn-outline">Documents</a>
            <a href="manage_appointments.php" class="btn btn-outline">Appointments</a>
        </div>

        <!-- ✅ FIXED: PROFESSOR SCHEDULE CONTROL -->
        <div class="panel">
            <h3>Professor Schedule Management</h3>
            <p>View and manage professor availability schedules</p>
            <a href="schedule_management.php" class="btn btn-primary">
                Open Schedule Management
            </a>
        </div>

    </div>

</div>

</div>
<script>
// ROLE PIE CHART
new Chart(document.getElementById('roleChart'), {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($roles); ?>,
        datasets: [{
            data: <?php echo json_encode($roleCounts); ?>,
        }]
    }
});

// APPOINTMENT LINE CHART
new Chart(document.getElementById('apptChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_reverse($dates)); ?>,
        datasets: [{
            label: 'Appointments',
            data: <?php echo json_encode(array_reverse($counts)); ?>,
            fill: true,
            tension: 0.3
        }]
    }
});
</script>
</body>
</html>
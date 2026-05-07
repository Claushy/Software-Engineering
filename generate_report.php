<?php
require 'auth.php';
require 'connector.php';
requireRole('admin');

// DOCUMENT STATUS COUNT
$sqlDocs = "
SELECT status, COUNT(*) as total 
FROM documents 
GROUP BY status
";
$stmtDocs = sqlsrv_query($conn, $sqlDocs);

$docStats = [];
while ($row = sqlsrv_fetch_array($stmtDocs, SQLSRV_FETCH_ASSOC)) {
    $docStats[$row['status']] = $row['total'];
}

// APPOINTMENTS
$sqlApps = "SELECT COUNT(*) as total FROM appointments";
$appTotal = sqlsrv_fetch_array(sqlsrv_query($conn, $sqlApps), SQLSRV_FETCH_ASSOC);

// USERS
$sqlUsers = "
SELECT role, COUNT(*) as total 
FROM ceatuser 
GROUP BY role
";
$stmtUsers = sqlsrv_query($conn, $sqlUsers);

$users = [];
while ($row = sqlsrv_fetch_array($stmtUsers, SQLSRV_FETCH_ASSOC)) {
    $users[$row['role']] = $row['total'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>System Report</title>

<style>
body{
    font-family: Arial;
    padding:40px;
}

h1{
    text-align:center;
}

.section{
    margin-top:30px;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

th, td{
    border:1px solid #ccc;
    padding:10px;
    text-align:left;
}

button{
    margin-top:20px;
    padding:10px 15px;
}
</style>
</head>

<body>

<h1>CEAT System Report</h1>
<p>Date Generated: <?php echo date("F d, Y"); ?></p>

<!-- DOCUMENT REPORT -->
<div class="section">
<h2>Documents Summary</h2>
<table>
<tr><th>Status</th><th>Total</th></tr>
<?php foreach ($docStats as $status => $count) { ?>
<tr>
<td><?php echo $status; ?></td>
<td><?php echo $count; ?></td>
</tr>
<?php } ?>
</table>
</div>

<!-- APPOINTMENTS -->
<div class="section">
<h2>Appointments</h2>
<p>Total Appointments: <?php echo $appTotal['total']; ?></p>
</div>

<!-- USERS -->
<div class="section">
<h2>User Distribution</h2>
<table>
<tr><th>Role</th><th>Total</th></tr>
<?php foreach ($users as $role => $count) { ?>
<tr>
<td><?php echo $role; ?></td>
<td><?php echo $count; ?></td>
</tr>
<?php } ?>
</table>
</div>

<button onclick="window.print()">Print / Save as PDF</button>

</body>
</html>
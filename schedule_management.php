<?php
require 'auth.php';
require 'connector.php';
requireLogin();

$userId = $_SESSION['user_id'];

// GET USER ROLE
$sqlUser = "SELECT role FROM ceatuser WHERE id = ?";
$stmtUser = sqlsrv_query($conn, $sqlUser, array($userId));
$user = sqlsrv_fetch_array($stmtUser, SQLSRV_FETCH_ASSOC);

$role = strtolower($user['role']);

// SAVE SCHEDULE (ONLY PROFESSOR)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'professor') {

    $day = $_POST['day'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];

    $sql = "INSERT INTO schedules (professor_id, day, start_time, end_time)
            VALUES (?, ?, ?, ?)";

    $stmt = sqlsrv_query($conn, $sql, array($userId, $day, $start, $end));

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
}

// FETCH DATA
if ($role === 'admin') {

    // ADMIN: SEE ALL PROFESSORS
    $sql = "
        SELECT s.*, u.full_name
        FROM schedules s
        JOIN ceatuser u ON s.professor_id = u.id
        ORDER BY s.id DESC
    ";

    $stmt = sqlsrv_query($conn, $sql);

} else {

    // PROFESSOR: OWN ONLY
    $sql = "SELECT * FROM schedules WHERE professor_id = ? ORDER BY id DESC";
    $stmt = sqlsrv_query($conn, $sql, array($userId));
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Schedule Management</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:#f4f6f9;
    font-family:"Segoe UI", sans-serif;
}

.box{
    max-width:900px;
    margin:40px auto;
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 5px 20px rgba(0,0,0,0.1);
}

h2{
    margin-bottom:20px;
}

</style>
</head>

<body>

<div class="box">

<h2> Schedule Management</h2>

<a href="
<?php
echo ($role === 'admin') ? 'admin_dashboard.php' : 'professor_dashboard.php';
?>
" class="btn btn-secondary mb-3">
Back
</a>

<?php if ($role === 'professor') { ?>

<!-- ONLY PROFESSOR CAN ADD -->
<form method="POST" class="mb-4">

    <select name="day" class="form-control mb-2" required>
        <option value="">Select Day</option>
        <option>Monday</option>
        <option>Tuesday</option>
        <option>Wednesday</option>
        <option>Thursday</option>
        <option>Friday</option>
        <option>Saturday</option>
    </select>

    <input type="time" name="start_time" class="form-control mb-2" required>
    <input type="time" name="end_time" class="form-control mb-2" required>

    <button class="btn btn-success">Add Schedule</button>
</form>

<?php } ?>

<!-- TABLE -->
<table class="table table-bordered">

<tr>
    <?php if ($role === 'admin') { ?>
        <th>Professor</th>
    <?php } ?>
    <th>Day</th>
    <th>Start</th>
    <th>End</th>
</tr>

<?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
<tr>

    <?php if ($role === 'admin') { ?>
        <td><?= htmlspecialchars($row['full_name']) ?></td>
    <?php } ?>

    <td><?= htmlspecialchars($row['day']) ?></td>
    <td><?= $row['start_time']->format('H:i') ?></td>
    <td><?= $row['end_time']->format('H:i') ?></td>

</tr>
<?php } ?>

</table>

</div>

</body>
</html>
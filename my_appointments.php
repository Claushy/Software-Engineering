<?php
require 'auth.php';
require 'connector.php';
requireLogin();

$userId = $_SESSION['user_id'];
$sql = "SELECT * FROM appointments WHERE user_id = ? ORDER BY appointment_date DESC, appointment_time DESC";
$stmt = sqlsrv_query($conn, $sql, array($userId));
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card p-4 shadow">
        <h2>My Appointments</h2>
        <a href="student_portal.php" class="btn btn-secondary mb-3">Back</a>
        <table class="table table-bordered">
            <tr>
                <th>Office</th>
                <th>Purpose</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
            <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['office_name']); ?></td>
                <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                <td><?php echo $row['appointment_date']->format('Y-m-d'); ?></td>
                <td><?php echo $row['appointment_time']->format('H:i:s'); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>
</body>
</html>
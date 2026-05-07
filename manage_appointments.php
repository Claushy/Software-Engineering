<?php
require 'auth.php';
require 'connector.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sqlsrv_query($conn, "UPDATE appointments SET status = ? WHERE id = ?", array($_POST['status'], (int) $_POST['appointment_id']));
}

// DELETE APPOINTMENT
if (isset($_GET['delete_id'])) {
    $deleteId = (int) $_GET['delete_id'];

    $sqlDelete = "DELETE FROM appointments WHERE id = ?";
    $stmtDelete = sqlsrv_query($conn, $sqlDelete, array($deleteId));

    if ($stmtDelete === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Refresh page after delete
    header("Location: manage_appointments.php");
    exit();
}

$sql = "SELECT a.*, u.full_name FROM appointments a JOIN ceatuser u ON a.user_id = u.id ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$stmt = sqlsrv_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card p-4 shadow">
        <h2>Manage Appointments</h2>
        <a href="admin_dashboard.php" class="btn btn-secondary mb-3">Back</a>
        <table class="table table-bordered">
            <tr><th>User</th><th>Office</th><th>Purpose</th><th>Date</th><th>Time</th><th>Status</th><th>Action</th><th>Delete</th></tr>
            <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['office_name']); ?></td>
                <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                <td><?php echo $row['appointment_date']->format('Y-m-d'); ?></td>
                <td><?php echo $row['appointment_time']->format('H:i:s'); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                    <form method="POST" class="d-flex gap-2">
                        <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                        <select name="status" class="form-select">
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                        <button class="btn btn-success">Update</button>
                    </form>
                </td>
                <td>
    <a href="manage_appointments.php?delete_id=<?php echo $row['id']; ?>"
       class="btn btn-danger btn-sm"
       onclick="return confirm('Are you sure you want to delete this appointment?');">
        Delete
    </a>
</td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>
</body>
</html>
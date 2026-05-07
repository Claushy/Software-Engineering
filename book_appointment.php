<?php
require 'auth.php';
require 'connector.php';
requireLogin();

$message = "";

// GET ALL PROFESSORS
$sqlProf = "SELECT id, full_name FROM ceatuser WHERE role = 'professor'";
$stmtProf = sqlsrv_query($conn, $sqlProf);

// BOOK APPOINTMENT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $userId = $_SESSION['user_id'];
    $professorId = $_POST['professor_id'];
    $purpose = trim($_POST['purpose']);
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    $getProf = "SELECT full_name FROM ceatuser WHERE id = ?";
$getProfStmt = sqlsrv_query($conn, $getProf, array($professorId));

$profData = sqlsrv_fetch_array($getProfStmt, SQLSRV_FETCH_ASSOC);

$professor = $profData['full_name'];

    // CHECK SLOT (based on professor)
    $checkSql = "
        SELECT id FROM appointments
        WHERE office_name = ?
        AND appointment_date = ?
        AND appointment_time = ?
        AND status IN ('Pending', 'Approved')
    ";

    $checkStmt = sqlsrv_query($conn, $checkSql, array(
        $professor,
        $appointment_date,
        $appointment_time
    ));

    $exists = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

    if ($exists) {
        $message = "<div class='alert alert-danger'>This professor is not available at this time.</div>";
    } else {

        $sql = "
            INSERT INTO appointments
(
    user_id,
    office_name,
    purpose,
    appointment_date,
    appointment_time,
    status,
    professor_id
)
VALUES
(
    ?, ?, ?, ?, ?, 'Pending', ?
)
        ";

        $stmt = sqlsrv_query($conn, $sql, array(
    $userId,
    $professor,
    $purpose,
    $appointment_date,
    $appointment_time,
    $professorId
));

        $message = $stmt
            ? "<div class='alert alert-success'>Appointment booked with $professor.</div>"
            : "<div class='alert alert-danger'>Booking failed.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Book Appointment</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

<div class="card p-4 shadow mx-auto" style="max-width:650px;">

    <h2>Book Appointment with Professor</h2>

    <?php echo $message; ?>

    <form method="POST">

        <!-- PROFESSOR DROPDOWN -->
        <select name="professor_id" class="form-control mb-3" required>

    <option value="">Select Professor</option>

    <?php while ($p = sqlsrv_fetch_array($stmtProf, SQLSRV_FETCH_ASSOC)) { ?>

        <option value="<?= $p['id'] ?>">

            <?= htmlspecialchars($p['full_name']) ?>

        </option>

    <?php } ?>

</select>

        <textarea name="purpose" class="form-control mb-3" placeholder="Purpose" required></textarea>

        <input type="date" name="appointment_date" class="form-control mb-3" required>

        <input type="time" name="appointment_time" class="form-control mb-3" required>

        <button class="btn btn-success">Book Appointment</button>
        <a href="student_portal.php" class="btn btn-secondary">Back</a>

    </form>

</div>

</div>

</body>
</html>
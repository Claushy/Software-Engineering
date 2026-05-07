<?php
require 'auth.php';
require 'connector.php';
requireLogin();

$userId = $_SESSION['user_id'];

// APPROVE
if (isset($_GET['approve'])) {

    $id = $_GET['approve'];

    $sql = "UPDATE appointments SET status='approved' WHERE id=?";
    sqlsrv_query($conn, $sql, array($id));

    header("Location: professor_appointments.php");
    exit();
}

// REJECT
if (isset($_GET['reject'])) {

    $id = $_GET['reject'];

    $sql = "UPDATE appointments SET status='rejected' WHERE id=?";
    sqlsrv_query($conn, $sql, array($id));

    header("Location: professor_appointments.php");
    exit();
}


// GET ALL APPOINTMENTS FOR THIS PROFESSOR
$sql = "
SELECT 
    a.*,
    u.full_name
FROM appointments a
INNER JOIN ceatuser u
    ON a.user_id = u.id
WHERE a.professor_id = ?
ORDER BY a.created_at DESC
";

$stmt = sqlsrv_query($conn, $sql, array($userId));

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<title>Appointment Requests</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f4f7fb;
    font-family:'Segoe UI', sans-serif;
}

.container-box{
    width:95%;
    max-width:1200px;
    margin:40px auto;
    background:white;
    padding:25px;
    border-radius:18px;
    box-shadow:0 5px 20px rgba(0,0,0,0.08);
}

.title{
    font-size:28px;
    font-weight:bold;
    margin-bottom:25px;
    color:#14532d;
}

.badge-pending{
    background:#fff3cd;
    color:#856404;
    padding:6px 12px;
    border-radius:8px;
}

.badge-approved{
    background:#d1e7dd;
    color:#0f5132;
    padding:6px 12px;
    border-radius:8px;
}

.badge-rejected{
    background:#f8d7da;
    color:#842029;
    padding:6px 12px;
    border-radius:8px;
}

.btn-approve{
    background:#198754;
    color:white;
    border:none;
    padding:7px 14px;
    border-radius:8px;
    text-decoration:none;
}

.btn-reject{
    background:#dc3545;
    color:white;
    border:none;
    padding:7px 14px;
    border-radius:8px;
    text-decoration:none;
}

.btn-back{
    background:#14532d;
    color:white;
    text-decoration:none;
    padding:10px 16px;
    border-radius:10px;
}

</style>

</head>

<body>

<div class="container-box">

<div style="display:flex; justify-content:space-between; align-items:center;">

    <div class="title">
        Appointment Requests
    </div>

    <a href="professor_dashboard.php" class="btn-back">
        Back Dashboard
    </a>

</div>

<table class="table table-hover align-middle">

<thead class="table-dark">

<tr>
    <th>Student</th>
    <th>Purpose</th>
    <th>Date</th>
    <th>Time</th>
    <th>Status</th>
    <th>Action</th>
</tr>

</thead>

<tbody>

<?php while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>

<tr>

    <td>
        <?php echo htmlspecialchars($row['full_name']); ?>
    </td>

    <td>
        <?php echo htmlspecialchars($row['purpose']); ?>
    </td>

    <td>
        <?php echo date_format($row['appointment_date'], 'M d, Y'); ?>
    </td>

    <td>
        <?php echo date_format($row['appointment_time'], 'h:i A'); ?>
    </td>

    <td>

        <?php
        $status = strtolower($row['status']);

        if($status == 'pending'){
            echo "<span class='badge-pending'>Pending</span>";
        }
        elseif($status == 'approved'){
            echo "<span class='badge-approved'>Approved</span>";
        }
        else{
            echo "<span class='badge-rejected'>Rejected</span>";
        }
        ?>

    </td>

    <td>

        <?php if($status == 'pending') { ?>

            <a class="btn-approve"
               href="?approve=<?php echo $row['id']; ?>">
               Approve
            </a>

            <a class="btn-reject"
               href="?reject=<?php echo $row['id']; ?>">
               Reject
            </a>

        <?php } else { ?>

            <span class="text-muted">
                Completed
            </span>

        <?php } ?>

    </td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</body>
</html>
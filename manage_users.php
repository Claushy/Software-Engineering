<?php
require 'auth.php';
require 'connector.php';
requireRole('admin');
$message = "";

// DELETE USER (ONLY if delete param exists)
if (isset($_GET['delete'])) {

    $deleteId = (int) $_GET['delete'];

    // delete dependencies first
    sqlsrv_query($conn, "DELETE FROM documents WHERE user_id = ?", array($deleteId));
    sqlsrv_query($conn, "DELETE FROM appointments WHERE user_id = ?", array($deleteId));
    sqlsrv_query($conn, "DELETE FROM messages WHERE sender_id = ? OR receiver_id = ?", array($deleteId, $deleteId));
    sqlsrv_query($conn, "DELETE FROM notifications WHERE user_id = ?", array($deleteId));

    // then delete user
    $sqlDelete = "DELETE FROM ceatuser WHERE id = ?";
    sqlsrv_query($conn, $sqlDelete, array($deleteId));

    // redirect to avoid re-delete on refresh
    header("Location: manage_users.php");
    exit();
}

// UPDATE ROLE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int) $_POST['user_id'];
    $role = $_POST['role'];

    $sql = "UPDATE ceatuser SET role = ? WHERE id = ?";
    $params = array($role, $userId);

    $stmtUpdate = sqlsrv_query($conn, $sql, $params);

    if ($stmtUpdate === false) {
        $message = "<div class='alert alert-danger'>Update failed.</div>";
    } else {
        $message = "<div class='alert alert-success'>Role updated successfully.</div>";
    }
}


// FETCH USERS
$stmt = sqlsrv_query($conn, "SELECT id, full_name, email, role FROM ceatuser ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card p-4 shadow">

        <h2>Manage Users</h2>
        <a href="admin_dashboard.php" class="btn btn-secondary mb-3">Back</a>

        <?php echo $message; ?>

        <table class="table table-bordered">

            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Action</th>
            </tr>

            <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>

            <tr>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['role']); ?></td>

                <td>
                    <form method="POST" class="d-flex gap-2">

                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">

                        <select name="role" class="form-select">

                            <option value="student" <?php if($row['role']=="student") echo "selected"; ?>>
                                student
                            </option>

                            <option value="professor" <?php if($row['role']=="professor") echo "selected"; ?>>
                                professor
                            </option>

                            <option value="admin" <?php if($row['role']=="admin") echo "selected"; ?>>
                                admin
                            </option>

                        </select>

                        <button class="btn btn-success">Update</button>

                    </form>
                    <a href="manage_users.php?delete=<?php echo $row['id']; ?>" 
       class="btn btn-danger mt-2"
       onclick="return confirm('Are you sure you want to delete this user?')">
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
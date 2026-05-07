<?php
require 'connector.php';


if (isset($_GET['id'])) {
    $id = $_GET['id'];
    

    $sql = "DELETE FROM notifications WHERE id = ?";
    $stmt = sqlsrv_query($conn, $sql, array($id));

    if ($stmt === false) {
        http_response_code(500);
        echo "Error";
    } else {
        echo "Success";
    }
}
?>
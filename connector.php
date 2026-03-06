<?php
// LAPTOP-0UHDLDRK\SQLEXPRESS connection
$serverName = "PATRICK-A\SQLEXPRESS"; 
$connectionOptions = array(
    "Database" => "DLSU",
    "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => true 
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die("<pre>" . print_r(sqlsrv_errors(), true) . "</pre>");
}
?>
<?php
if (!extension_loaded('sqlsrv')) {
    die('sqlsrv extension is NOT loaded in PHP.');
}

$serverName = "MSI\\SQLEXPRESS01";

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
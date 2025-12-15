<?php
$host = 'localhost';
$user = 'root';
$pass = '';          // Change if needed
$db   = 'vuln_crud';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}
?>

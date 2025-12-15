<?php
session_start();
require 'db.php';

// VULNERABLE: no authentication or authorization check
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $sql = "DELETE FROM users WHERE id = $id";
    mysqli_query($conn, $sql);
}

header('Location: index.php');
exit;

<?php
session_start();
require 'db.php';

// Require authentication and admin role
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0 && $id !== (int)$_SESSION['user_id']) {
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
    }
}

header('Location: index.php');
exit;

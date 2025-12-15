<?php
session_start();
require 'db.php';

// Require login for any access
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$current_id = (int)$_SESSION['user_id'];
$is_admin   = !empty($_SESSION['is_admin']);

// Admin: see all users. Normal user: only themselves.
if ($is_admin) {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, username, is_admin FROM users ORDER BY id"
    );
} else {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, username, is_admin FROM users WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'i', $current_id);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Users (Secure)</title>
</head>
<body>
    <h1>Users list (secure)</h1>

    <p>
        Logged in as:
        <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>
        (<?php echo $is_admin ? 'admin' : 'user'; ?>)
    </p>

    <p><a href="logout.php">Logout</a></p>

    <?php if ($is_admin): ?>
        <p><a href="user_create.php">Create new user</a></p>
    <?php endif; ?>

    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Is admin</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo (int)$row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo (int)$row['is_admin']; ?></td>
                <td>
                    <a href="user_edit.php?id=<?php echo (int)$row['id']; ?>">Edit</a>
                    <?php if ($is_admin): ?>
                        |
                        <a href="user_delete.php?id=<?php echo (int)$row['id']; ?>"
                           onclick="return confirm('Delete this user?');">
                            Delete
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

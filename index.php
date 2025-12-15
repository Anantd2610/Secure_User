<?php
session_start();
require 'db.php';

// VULNERABLE: anyone can see all users and passwords
$sql    = "SELECT * FROM users";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Users (Vulnerable)</title>
</head>
<body>
    <h1>Users list (VULNERABLE)</h1>

    <?php if (isset($_SESSION['username'])): ?>
        <p>Logged in as: <?php echo $_SESSION['username']; ?></p>
        <p><a href="logout.php">Logout</a></p>
    <?php else: ?>
        <p><a href="login.php">Login</a></p>
    <?php endif; ?>

    <p><a href="user_create.php">Create new user</a></p>

    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Password (plaintext)</th>
            <th>Is admin</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['password_plain']; ?></td>
                <td><?php echo $row['is_admin']; ?></td>
                <td>
                    <a href="user_edit.php?id=<?php echo $row['id']; ?>">Edit</a> |
                    <a href="user_delete.php?id=<?php echo $row['id']; ?>">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

<?php
session_start();
require 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO users (username, password_plain, is_admin)
         VALUES (?, ?, ?)"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ssi', $username, $password, $is_admin);

        if (mysqli_stmt_execute($stmt)) {
            $message = 'User created';
        } else {
            $message = 'Error: ' . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8');
        }
    } else {
        $message = 'Error preparing statement';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create user</title>
</head>
<body>
    <h1>Create user</h1>

    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Username:
            <input type="text" name="username">
        </label><br><br>

        <label>Password:
            <input type="text" name="password">
        </label><br><br>

        <label>
            <input type="checkbox" name="is_admin" value="1"> Is admin
        </label><br><br>

        <button type="submit">Create</button>
    </form>

    <p><a href="index.php">Back to list</a></p>
</body>
</html>

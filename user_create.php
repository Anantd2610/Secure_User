<?php
session_start();
require 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username']; // no validation
    $password = $_POST['password']; // stored as plaintext
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    // VULNERABLE: SQL injection and plaintext password
    $sql = "INSERT INTO users (username, password_plain, is_admin)
            VALUES ('$username', '$password', $is_admin)";

    if (mysqli_query($conn, $sql)) {
        $message = 'User created';
    } else {
        $message = 'Error: ' . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create user (Vulnerable)</title>
</head>
<body>
    <h1>Create user (VULNERABLE)</h1>

    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Username:
            <input type="text" name="username">
        </label><br><br>

        <label>Password:
            <input type="text" name="password">
        </label><br><br>

        <label>
            <input type="checkbox" name="is_admin" value="1">
            Is admin
        </label><br><br>

        <button type="submit">Create</button>
    </form>

    <p><a href="index.php">Back to list</a></p>
</body>
</html>

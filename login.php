<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username']; // no validation
    $password = $_POST['password']; // no validation

    // VULNERABLE: SQL injection + plaintext password comparison
    $sql = "SELECT * FROM users WHERE username = '$username' AND password_plain = '$password'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);

        $_SESSION['user_id']  = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['is_admin'] = $row['is_admin'];

        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login (Vulnerable)</title>
</head>
<body>
    <h1>Login (SQLi vulnerable)</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Username:
            <input type="text" name="username">
        </label><br><br>
        <label>Password:
            <input type="password" name="password">
        </label><br><br>
        <button type="submit">Login</button>
    </form>

    <p>Try SQLi: username <code>' OR '1'='1</code> and any password.</p>
    <p><a href="index.php">Back to list</a></p>
</body>
</html>

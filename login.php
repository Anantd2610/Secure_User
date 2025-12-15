<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Prepared statement: no more SQL injection
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, username, password_plain, password_hash, is_admin
         FROM users
         WHERE username = ?"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            $ok  = false;

            if (!empty($row['password_hash'])) {
                // New users or migrated users – use hash
                $ok = password_verify($password, $row['password_hash']);
            } else {
                // Legacy users – still plaintext, migrate on first successful login
                if ($password === $row['password_plain']) {
                    $ok = true;
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);

                    $update = mysqli_prepare(
                    $conn,
                    "UPDATE users SET password_hash = ? WHERE id = ?"
                    );
                    if ($update) {
                        mysqli_stmt_bind_param($update, 'si', $new_hash, $row['id']);
                        mysqli_stmt_execute($update);
                    }
                }
            }

            if ($ok) {
                $_SESSION['user_id']  = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['is_admin'] = $row['is_admin'];

                header('Location: index.php');
                exit;
            }
        }
    }

    $error = 'Invalid credentials';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login (Secure - SQLi fixed)</title>
</head>
<body>
    <h1>Login</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
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

    <p><a href="index.php">Back to list</a></p>
</body>
</html>

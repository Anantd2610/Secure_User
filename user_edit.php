<?php
session_start();
require 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch user
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, username, password_plain, is_admin
     FROM users
     WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user   = $result ? mysqli_fetch_assoc($result) : null;

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    if ($username === '') {
        $message = 'Username is required';
        } else {
            if ($password !== '') {
                // change plaintext + hash
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $update = mysqli_prepare(
                    $conn,
                    "UPDATE users
                     SET username = ?, password_plain = ?, password_hash = ?, is_admin = ?
                     WHERE id = ?"
                );
                mysqli_stmt_bind_param(
                    $update,
                    'sssii',
                    $username,
                    $password,
                    $hash,
                    $is_admin,
                    $id
                );
            } else {
                // only username / is_admin change
                $update = mysqli_prepare(
                    $conn,
                    "UPDATE users
                     SET username = ?, is_admin = ?
                     WHERE id = ?"
                );
                mysqli_stmt_bind_param(
                    $update,
                    'sii',
                    $username,
                    $is_admin,
                    $id
                );
            }   
    }
    if ($update && mysqli_stmt_execute($update)) {
        $message = 'User updated';

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = $result ? mysqli_fetch_assoc($result) : null;
    } else {
        $message = 'Error: ' . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit user</title>
</head>
<body>
    <h1>Edit user</h1>

    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <?php if ($user): ?>
        <form method="post">
            <label>Username:
                <input type="text" name="username"
                       value="<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>">
            </label><br><br>

            <label>Password:
                <input type="text" name="password"
                       value="<?php echo htmlspecialchars($user['password_plain'], ENT_QUOTES, 'UTF-8'); ?>">
            </label><br><br>

            <label>
                <input type="checkbox" name="is_admin" value="1"
                    <?php echo $user['is_admin'] ? 'checked' : ''; ?>>
                Is admin
            </label><br><br>

            <button type="submit">Save</button>
        </form>
    <?php else: ?>
        <p>User not found.</p>
    <?php endif; ?>

    <p><a href="index.php">Back to list</a></p>
</body>
</html>

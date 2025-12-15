<?php
session_start();
require 'db.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$current_id   = (int)$_SESSION['user_id'];
$is_admin     = !empty($_SESSION['is_admin']);
$requested_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// IDOR protection: only owner OR admin can edit this user
if (!$is_admin && $requested_id !== $current_id) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$id = $requested_id;

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

if (!$user) {
    echo 'User not found.';
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $new_is_admin = $user['is_admin'];

    // Only admin is allowed to change admin flag
    if ($is_admin) {
        $new_is_admin = isset($_POST['is_admin']) ? 1 : 0;
    }

    if ($username === '') {
        $message = 'Username is required';
    } else {
        if ($password !== '') {
            // Change password + hash
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
                $new_is_admin,
                $id
            );
        } else {
            // Only username / role
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
                $new_is_admin,
                $id
            );
        }

        if ($update && mysqli_stmt_execute($update)) {
            $message = 'User updated';

            // Refresh user
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user   = $result ? mysqli_fetch_assoc($result) : null;

            // Keep session in sync when editing own account
            if ($id === $current_id) {
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];
            }
        } else {
            $message = 'Error: ' . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8');
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit user (Secure)</title>
</head>
<body>
    <h1>Edit user (secure)</h1>

    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Username:
            <input type="text" name="username"
                   value="<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>">
        </label><br><br>

        <label>New password (leave blank to keep current):
            <input type="text" name="password">
        </label><br><br>

        <?php if ($is_admin): ?>
            <label>
                <input type="checkbox" name="is_admin" value="1"
                    <?php echo $user['is_admin'] ? 'checked' : ''; ?>>
                Is admin
            </label><br><br>
        <?php endif; ?>

        <button type="submit">Save</button>
    </form>

    <p><a href="index.php">Back to list</a></p>
</body>
</html>

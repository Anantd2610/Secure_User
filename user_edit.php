<?php
session_start();
require 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// VULNERABLE: anyone can edit any ID
$sql    = "SELECT * FROM users WHERE id = $id";
$result = mysqli_query($conn, $sql);
$user   = $result ? mysqli_fetch_assoc($result) : null;

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username']; // no validation
    $password = $_POST['password']; // plaintext
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    // VULNERABLE: no access control, SQL injection
    $update_sql = "UPDATE users
                   SET username = '$username',
                       password_plain = '$password',
                       is_admin = $is_admin
                   WHERE id = $id";

    if (mysqli_query($conn, $update_sql)) {
        $message = 'User updated';

        $result = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
        $user   = $result ? mysqli_fetch_assoc($result) : null;
    } else {
        $message = 'Error: ' . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit user (Vulnerable)</title>
</head>
<body>
    <h1>Edit user (VULNERABLE)</h1>

    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <?php if ($user): ?>
        <form method="post">
            <label>Username:
                <input type="text" name="username" value="<?php echo $user['username']; ?>">
            </label><br><br>

            <label>Password:
                <input type="text" name="password" value="<?php echo $user['password_plain']; ?>">
            </label><br><br>

            <label>
                <input type="checkbox" name="is_admin" value="1" <?php echo $user['is_admin'] ? 'checked' : ''; ?>>
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

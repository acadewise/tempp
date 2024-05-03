<?php
session_start();

// Include database connection
require_once('database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $first_char = $_POST['first_char'];
    $new_password = $_POST['new_password'];

    // Retrieve the user's current password hash from the database based on the username
    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($first_char, substr($user['password_hash'], 0, 1))) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update user's password in the database
        $update_stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $update_stmt->execute([$hashed_password, $user['id']]);

        // Display success message or redirect to login page
        echo "Password reset successful. <a href='login.php'>Login</a>";
        exit;
    } else {
        $error = "Invalid username or first character of current password. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Reset Password</h2>
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" id="username" name="username">
            </div>
            <div class="form-group">
                <label for="first_char">First Character of Current Password:</label>
                <input type="text" class="form-control" id="first_char" name="first_char" maxlength="1">
            </div>
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" class="form-control" id="new_password" name="new_password">
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
    </div>
</body>
</html>

<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once('database.php');

// Retrieve user information from session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Check if the user has confirmed the account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // Delete all posts associated with the user
    $delete_posts_stmt = $pdo->prepare("DELETE FROM posts WHERE user_id = ?");
    $delete_posts_stmt->execute([$user_id]);

    // Delete all comments associated with the user's posts
    $delete_comments_stmt = $pdo->prepare("DELETE FROM comments WHERE user_id = ?");
    $delete_comments_stmt->execute([$user_id]);

    // Delete the user account
    $delete_user_stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $delete_user_stmt->execute([$user_id]);

    // Redirect to the login page after successful deletion
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Account</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Delete Account</h2>
        <p>Are you sure you want to delete your account, <?php echo htmlspecialchars($username); ?>?</p>

        <form method="post">
            <input type="hidden" name="confirm_delete" value="true">
            <button type="submit" class="btn btn-danger">Confirm Delete</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>

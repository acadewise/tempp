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
$role = $_SESSION['role'];

// Retrieve user's posts from the database
$stmt = $pdo->prepare("SELECT id, content, created_at FROM posts WHERE user_id = ?");
$stmt->execute([$user_id]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Team Girish and saiesha</h1>
        <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        <p>Your role: <?php echo htmlspecialchars($role); ?></p>

        <hr>

        <h3>My Posts</h3>
        <?php if (count($posts) > 0) : ?>
            <ul class="list-group">
                <?php foreach ($posts as $post) : ?>
                    <li class="list-group-item">
                        <p><?php echo htmlspecialchars($post['content']); ?></p>
                        <small>Posted on <?php echo htmlspecialchars($post['created_at']); ?></small>
                        <?php if ($role === 'regular') : ?>
                            <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="delete_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-danger">Delete</a>
                            <a href="post_details.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-info">View Details</a>
                        <?php elseif ($role === 'superuser') : ?>
                            <!-- Superuser actions (if needed) -->
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p>No posts found.</p>
        <?php endif; ?>

        <hr>

        <?php if ($role === 'regular') : ?>
            <h3>Add New Post</h3>
            <form method="post" action="add_post.php">
                <div class="form-group">
                    <label for="post_content">Content:</label>
                    <textarea class="form-control" id="post_content" name="post_content" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>

            <hr>

            <h3>Account Actions</h3>
            <a href="change_password.php" class="btn btn-primary">Change Password</a>
            <a href="delete_account.php" class="btn btn-danger">Delete Account</a>
            <a href="logout.php" class="btn btn-secondary">Logout</a>
        <?php elseif ($role === 'superuser') : ?>
            <h3>Superuser Actions</h3>
            <!-- Display superuser-specific actions -->
            <a href="manage_users.php" class="btn btn-primary">Manage Users</a>
            <a href="logout.php" class="btn btn-secondary">Logout</a>
        <?php endif; ?>
    </div>
</body>
</html>

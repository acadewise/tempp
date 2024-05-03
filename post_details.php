<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once('database.php');

// Retrieve post ID from the query parameters
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$post_id = $_GET['id'];

// Retrieve post details from the database
$stmt = $pdo->prepare("SELECT id, content, created_at FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

// Retrieve comments associated with the post from the database
$stmt_comments = $pdo->prepare("SELECT c.id, c.content, c.created_at, u.username 
                                FROM comments c 
                                INNER JOIN users u ON c.user_id = u.id 
                                WHERE c.post_id = ?");
$stmt_comments->execute([$post_id]);
$comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Details</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Post Details</h2>

        <?php if ($post) : ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Post</h5>
                    <p class="card-text"><?php echo htmlspecialchars($post['content']); ?></p>
                    <p class="card-text"><small class="text-muted">Posted on <?php echo htmlspecialchars($post['created_at']); ?></small></p>
                </div>
            </div>

            <hr>

            <h3>Comments</h3>

            <?php if (count($comments) > 0) : ?>
                <ul class="list-group">
                    <?php foreach ($comments as $comment) : ?>
                        <li class="list-group-item">
                            <p><strong><?php echo htmlspecialchars($comment['username']); ?>:</strong> <?php echo htmlspecialchars($comment['content']); ?></p>
                            <small>Posted on <?php echo htmlspecialchars($comment['created_at']); ?></small>
                            <?php if ($_SESSION['user_id'] === $comment['user_id']) : ?>
                                <!-- Allow comment owner to edit/delete their comment -->
                                <a href="edit_comment.php?id=<?php echo $comment['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="delete_comment.php?id=<?php echo $comment['id']; ?>" class="btn btn-sm btn-danger">Delete</a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p>No comments yet.</p>
            <?php endif; ?>
        <?php else : ?>
            <p>Post not found.</p>
        <?php endif; ?>

        <hr>

        <!-- Form to add new comment -->
        <h3>Add Comment</h3>
        <form method="post" action="add_comment.php">
            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
            <div class="form-group">
                <label for="comment_content">Comment:</label>
                <textarea class="form-control" id="comment_content" name="comment_content" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>

        <hr>

        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html>

<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once('database.php');

// Process deletion request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    // Retrieve comment ID from the query parameters
    $comment_id = $_GET['id'];

    // Retrieve the associated post ID before deleting the comment
    $stmt = $pdo->prepare("SELECT post_id FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $post_id = $result['post_id'];

        // Delete the comment from the database
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$comment_id]);

        // Redirect back to the post details page after deleting the comment
        header("Location: post_details.php?id=$post_id");
        exit;
    }
}
?>

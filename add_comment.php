<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once('database.php');

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve user ID from session
    $user_id = $_SESSION['user_id'];

    // Retrieve post ID and comment content from the form
    $post_id = $_POST['post_id'];
    $content = $_POST['comment_content'];

    // Insert new comment into the database
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$post_id, $user_id, $content]);

    // Redirect back to the post details page after adding the comment
    header("Location: post_details.php?id=$post_id");
    exit;
}
?>

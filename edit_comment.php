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
    // Retrieve comment ID and updated content from the form
    $comment_id = $_POST['comment_id'];
    $content = $_POST['comment_content'];

    // Update comment content in the database
    $stmt = $pdo->prepare("UPDATE comments SET content = ? WHERE id = ?");
    $stmt->execute([$content, $comment_id]);

    // Redirect back to the post details page after editing the comment
    header("Location: post_details.php?id=$post_id");
    exit;
}
?>

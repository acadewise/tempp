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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    // Retrieve post ID from the form
    $post_id = $_POST['post_id'];

    // Retrieve post content from the form
    $content = $_POST['post_content'];

    // Update post content in the database
    $stmt = $pdo->prepare("UPDATE posts SET content = ? WHERE id = ?");
    $stmt->execute([$content, $post_id]);

    // Redirect back to the dashboard after editing the post
    header("Location: dashboard.php");
    exit;
} else {
    // Redirect to dashboard if post ID is not provided
    header("Location: dashboard.php");
    exit;
}
?>

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

    // Retrieve post content from the form
    $content = $_POST['post_content'];

    // Insert new post into the database
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
    $stmt->execute([$user_id, $content]);

    // Redirect back to the dashboard after adding the post
    header("Location: dashboard.php");
    exit;
}
?>

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
    // Retrieve post ID from the query parameters
    $post_id = $_GET['id'];

    // Delete the post from the database
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);

    // Redirect back to the dashboard after deleting the post
    header("Location: dashboard.php");
    exit;
} else {
    // Redirect to dashboard if post ID is not provided
    header("Location: dashboard.php");
    exit;
}
?>

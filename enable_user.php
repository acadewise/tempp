<?php
session_start();

// Check if user is logged in and is a superuser
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superuser') {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once('database.php');

// Retrieve user ID from the URL parameter
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Update user's `is_disabled` field to 0 (enabled)
    $stmt = $pdo->prepare("UPDATE users SET is_disabled = 0 WHERE id = ?");
    $stmt->execute([$user_id]);
}

// Redirect back to manage_users.php after enabling user
header("Location: manage_users.php");
exit;
?>

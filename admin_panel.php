<?php
session_start();

// Check if user is logged in and is a superuser
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superuser') {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once('database.php');

// Retrieve user information from session
$username = $_SESSION['username'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Welcome to Admin Panel, <?php echo htmlspecialchars($username); ?>!</h2>
        <hr>

        <h3>Manage Users</h3>
        <a href="manage_users.php" class="btn btn-primary">View Registered Users</a>
        <hr>

        <a href="logout.php" class="btn btn-secondary">Logout</a>
    </div>
</body>
</html>

<?php
session_start();

// Include database connection
require_once('database.php');

// Regex patterns for validation
$username_pattern = '/^[a-zA-Z0-9_]{3,20}$/'; // Alphanumeric characters and underscore, 3-20 characters long
$password_pattern = '/^.{8,}$/'; // At least 8 characters long

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username) || empty($password)) {
        $error = "Username and password are required.";
    } elseif (!preg_match($username_pattern, $username)) {
        $error = "Username must be alphanumeric and between 3 to 20 characters long.";
    } elseif (!preg_match($password_pattern, $password)) {
        $error = "Password must be at least 8 characters long.";
    } else {
        // Check if username is already taken
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            $error = "Username already exists. Please choose a different username.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into database
            $insert_stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
            if ($insert_stmt->execute([$username, $hashed_password])) {
                $_SESSION['username'] = $username;
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Failed to register user. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>User Registration</h2>
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" id="username" name="username" pattern="[a-zA-Z0-9_]{3,20}" required title="Username must be alphanumeric and between 3 to 20 characters long.">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" pattern=".{8,}" required title="Password must be at least 8 characters long.">
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>
</body>
</html>

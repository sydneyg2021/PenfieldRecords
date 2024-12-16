<?php
session_start(); // Start the session

require 'vendor/autoload.php'; // Include Composer's autoloader
use MongoDB\Client;

$uri = $_ENV['MONGO_URI'];
$client = new MongoDB\Client($uri);

// Connect to MongoDB
try {
    $client = new Client($uri);
    $db = $client->selectDatabase('Management'); // Switch to 'Management' database
    $collection = $db->Users; // The 'Users' collection in the 'Management' database
} catch (Exception $e) {
    die("Failed to connect to MongoDB: " . $e->getMessage());
}

// Handle form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query the 'Users' collection in 'Management' database by '_id' (username)
    $user = $collection->findOne(['_id' => $username]);

    // Check if user exists
    if ($user) {
        // Check the password using password_verify (password in database is hashed)
        if (password_verify($password, $user['password'])) {
            // Store user info in session
            $_SESSION['username'] = $username;
            $_SESSION['name'] = $user['name'];  
            $_SESSION['role'] = $user['role'];

            // Redirect to dashboard or other page
            header("Location: view/dashboard.php");
            exit();
        }
    }

    // If login fails
    $error = "Invalid username or password";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Penfield Records</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <!-- Error banner -->
    <?php if ($error): ?>
        <div class="error-banner">
            <p><?= htmlspecialchars($error) ?></p>
        </div>
    <?php endif; ?>

    <div class="image-container">
        <img src="assets/penfield.jpg" alt="Penfield Records" class="penfield-img">
    </div>

    <div class="main-container">
        <h1>Welcome to Penfield Records</h1> <br>
        <p>Penfield Records is your trusted resource for managing legal documents like Deeds, Wills, and Mortgages.</p> <br>

        <form method="POST" action="">
            <div class="nice-form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="nice-form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="small-btn">Login</button>
        </form>
    </div>
</body>
</html>

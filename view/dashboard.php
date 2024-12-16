<?php
session_start();
require '../vendor/autoload.php';
use MongoDB\Client;

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// MongoDB Connection
$uri = $_ENV['MONGO_URI'];
$client = new MongoDB\Client($uri);
$db = $client->selectDatabase('Management');

// Fetch the logged-in user's role from MongoDB
$usersCollection = $db->selectCollection('Users');
$user = $usersCollection->findOne(['_id' => $_SESSION['username']]);

// Check if the user is an Admin
$isAdmin = $user && isset($user['role']) && $user['role'] === 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Penfield Records</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>

    <!-- Error banner -->
    <?php if (isset($error) && $error): ?>
        <div class="error-banner">
            <p><?= htmlspecialchars($error) ?></p>
        </div>
    <?php endif; ?>

    <div class="image-container">
        <img src="../assets/penfield.jpg" alt="Penfield Records" class="penfield-img">
    </div>

    <div class="main-container">
        <h1>Penfield Records</h1>
        <p>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p> <br>
        <p>What would you like to do today?</p> <br>

        <!-- Button for Insert Records -->
        <form action="insert.php" method="get">
            <button type="submit" class="dashboard-btn">Insert Records</button> <br>
        </form>

        <!-- Button to manage Records -->
        <form action="manage.php" method="get">
            <button type="submit" class="dashboard-btn">View and Edit Records</button> <br>
        </form>

        <!-- Conditional Admin Button -->
        <?php if ($isAdmin): ?>
            <p>Database Management</p>
            <div class="btn-container">
                <form action="create_account.php" method="get">
                    <button type="submit" class="dashboard-btn">Create an Account</button> <br>
                </form>

                <form action="view_messages.php" method="get">
                    <button type="submit" class="dashboard-btn">View Messages</button> <br>
                </form>
            </div>
        <?php endif; ?>

        <!-- Logout and Help Buttons -->
        <div class="btn-container">
            <form action="../logout.php" method="get">
                <button type="submit" class="small-btn logout-btn">Logout</button>
            </form>
            <form action="help.php" method="get">
                <button type="submit" class="small-btn">Report an Issue</button>
            </form>
        </div>
    </div>

</body>
</html>




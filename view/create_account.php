<?php
// Start the session to maintain user data across pages
session_start();

// Include MongoDB client library
require '../vendor/autoload.php';
use MongoDB\Client;

// Check if the user is logged in; if not, redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// MongoDB Connection URI
$uri = $_ENV['MONGO_URI'];
$client = new MongoDB\Client($uri);

// Select the 'Management' database from the MongoDB cluster
$db = $client->selectDatabase('Management');

// Fetch the logged-in user's details from the 'Users' collection
$usersCollection = $db->selectCollection('Users');
$user = $usersCollection->findOne(['_id' => $_SESSION['username']]);

// If the user does not exist or isn't an Admin, redirect to the login page
if (!$user || $user['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

// Handle form submission when the form is posted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user input to prevent any unwanted characters
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role']; // Expected role is either "User" or "Admin"
    $errors = []; // Initialize an array to hold any validation errors

    // Validate that all fields are filled
    if (empty($username) || empty($name) || empty($email) || empty($password) || empty($role)) {
        $errors[] = "All fields are required."; // Add error if any field is empty
    }

    // Validate password complexity (at least 8 characters, with uppercase, lowercase, digit, and special character)
    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $errors[] = "Password must be at least 8 characters long and include one uppercase letter, one lowercase letter, one number, and one special character.";
    }

    // Check if username or email contains disallowed characters like quotes or apostrophes
    if (preg_match("/['\"]/", $username) || preg_match("/['\"]/", $email)) {
        $errors[] = "Apostrophes or quotes are not allowed in username or email."; // Add error if invalid characters are found
    }

    // Check if the username already exists in the database
    $existingUser = $usersCollection->findOne(['_id' => $username]);
    if ($existingUser) {
        $errors[] = "Username already exists."; // Add error if username already exists
    }

    // Check if the email already exists in the database
    $existingEmail = $usersCollection->findOne(['email' => $email]);
    if ($existingEmail) {
        $errors[] = "Email already exists."; // Add error if email already exists
    }

    // If no errors, proceed with creating the user account
    if (empty($errors)) {
        // Hash the password before storing it in the database
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the new user data to be inserted into the database
        $newUser = [
            '_id' => $username,  // MongoDB _id is the username
            'name' => $name,     // Name of the user
            'email' => $email,   // Email of the user
            'password' => $hashedPassword, // Hashed password
            'role' => $role      // Role of the user (either "User" or "Admin")
        ];

        // Insert the new user into the 'Users' collection
        $insertResult = $usersCollection->insertOne($newUser);

        // Check if the user was successfully inserted
        if ($insertResult->getInsertedCount() > 0) {
            // Set a success message in the session and redirect to the same page
            $_SESSION['message'] = "Account created successfully!";
            header('Location: create_account.php');
            exit();
        } else {
            // If there was an error inserting the user, add an error message
            $errors[] = "Error creating account.";
        }
    }

    // Store errors in the session to display on the form
    $_SESSION['errors'] = $errors;
    header('Location: create_account.php'); // Redirect back to the form to show errors
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an Account - Penfield Records</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <div class="main-container">
        <!-- Error Banner: Display any validation errors from the session -->
        <?php if (isset($_SESSION['errors']) && count($_SESSION['errors']) > 0): ?>
            <div class="error-banner">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p> <br> <!-- Display each error -->
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']); ?> <!-- Clear errors after displaying -->
            </div>
        <?php endif; ?>

        <!-- Success Banner: Display a success message after account creation -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="success-banner">
                <p><?php echo htmlspecialchars($_SESSION['message']); ?></p> <!-- Display success message -->
                <?php unset($_SESSION['message']); ?> <!-- Clear success message after displaying -->
            </div>
        <?php endif; ?>

        <h1>Create an Account</h1> <br>
        <small>Password must be at least 8 characters long, include one uppercase letter, one lowercase letter, one number, and one special character. </small> <br> <br>

        <!-- Account Creation Form -->
        <form method="POST" action="">
            <!-- Name Input -->
            <div class="nice-form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <!-- Username Input -->
            <div class="nice-form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <!-- Email Input -->
            <div class="nice-form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <!-- Password Input -->
            <div class="nice-form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <!-- Role Selection -->
            <div class="nice-form-group">
                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="User">User</option>
                    <option value="Admin">Admin</option>
                </select>
            </div>

            <!-- Button Container -->
            <div class="btn-container">
                <!-- Back to Dashboard Button -->
                <a href="dashboard.php" class="small-btn logout-btn">Back to Dashboard</a>

                <!-- Submit Button to Create Account -->
                <button type="submit" class="small-btn">Create Account</button>
            </div>
        </form>
    </div>
</body>
</html>

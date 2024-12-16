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

// MongoDB Connection URI for connecting to the cloud database
$uri = "mongodb+srv://Admin:wMl9JKCLzNS6zlmx@cluster0.wylus.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0";
$client = new Client($uri);

// Select the 'Management' database and 'Messages' collection from the MongoDB client
$db = $client->selectDatabase('Management');
$messagesCollection = $db->selectCollection('Messages');

// Initialize a variable to hold success messages for the user
$success = null; // This will hold success messages or be null if no success message is needed

// Handle form submission (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user input to remove extra spaces
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $urgency = $_POST['urgency'];  // Urgency level of the issue (e.g., 'Not Urgent', 'Kinda Bad', 'FIX NOW')
    $errors = []; // Initialize an empty array to collect any validation errors

    // Input validation to ensure all fields are filled and email is in the correct format
    if (empty($email) || empty($subject) || empty($message) || empty($urgency)) {
        $errors[] = "All fields are required.";  // Error if any required field is empty
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";  // Error if the email is not valid
    }

    // If there are no errors, insert the message into MongoDB
    if (empty($errors)) {
        // Prepare the message data to be stored in the MongoDB collection
        $newMessage = [
            'username' => $_SESSION['username'],  // Store the username of the user submitting the message
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'timestamp' => new MongoDB\BSON\UTCDateTime(),  // Store the current timestamp when the message is created
            'urgent' => $urgency,  // Store the urgency level
            'resolved' => false,  // Set the 'resolved' status to false initially (message not yet resolved)
        ];

        // Insert the new message into the 'Messages' collection
        $insertResult = $messagesCollection->insertOne($newMessage);

        // Check if the insert operation was successful
        if ($insertResult->getInsertedCount() > 0) {
            // Set a success message if the message was successfully submitted
            $success = "Your issue has been successfully submitted!";
        } else {
            // Add an error message if the submission failed
            $errors[] = "There was an error submitting your issue. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help - Report an Issue</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>

    <!-- Display Success/Error Banners -->
    <?php if ($success): ?>
        <!-- Success banner if message was successfully submitted -->
        <div class="success-banner">
            <p><?= htmlspecialchars($success) ?></p>  <!-- Display the success message safely -->
        </div>
    <?php elseif (!empty($errors)): ?>
        <!-- Error banner if there were validation errors or submission issues -->
        <div class="error-banner">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>  <!-- Display each error message safely -->
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="main-container">
        <h1>Report an Issue</h1> <br>
        <p>Please fill out the form below to report an issue. Our team will get back to you as soon as possible.</p> <br>

        <!-- Issue Reporting Form -->
        <form method="POST" action="">
            <!-- Email Input Field -->
            <div class="nice-form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required> <!-- Email input, required field -->
            </div>

            <!-- Subject Input Field -->
            <div class="nice-form-group">
                <label for="subject">Subject:</label>
                <input type="text" id="subject" name="subject" required> <!-- Subject input, required field -->
            </div>

            <!-- Message Input Field -->
            <div class="nice-form-group">
                <label for="message">Message:</label>
                <textarea id="message" name="message" rows="5" required></textarea> <!-- Message input, required field -->
            </div>

            <!-- Urgency Dropdown -->
            <div class="nice-form-group">
                <label for="urgency">Urgency:</label>
                <select id="urgency" name="urgency" required>
                    <option value="Not Urgent">Not Urgent</option>
                    <option value="Kinda Bad">Kinda Bad</option>
                    <option value="FIX NOW">FIX NOW</option> <!-- Urgency levels the user can select from -->
                </select>
            </div>

            <!-- Button Container with action buttons -->
            <div class="button-container">
                <!-- Button to navigate back to the dashboard -->
                <a href="dashboard.php" class="logout-btn">Back Home</a>
                <!-- Submit Button to submit the form -->
                <button type="submit" class="login-btn">Submit</button>
            </div>
        </form>
    </div>

</body>
</html>

<?php
// Start the session to maintain user data across pages
session_start();

// Include MongoDB client library
require '../vendor/autoload.php';
use MongoDB\Client;

// Check if the user is logged in and has the 'Admin' role. If not, redirect to login page
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

// MongoDB Connection URI for connecting to the cloud database
$uri = "mongodb+srv://Admin:wMl9JKCLzNS6zlmx@cluster0.wylus.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0";
$client = new Client($uri);

// Select the 'Management' database and 'Messages' collection from the MongoDB client
$db = $client->selectDatabase('Management');
$messagesCollection = $db->selectCollection('Messages');

// Initialize variables for success and error messages
$success = null;
$errors = [];

// Default filter to fetch unresolved messages
$filter = ['resolved' => false];

// Check if the urgency filter is set and valid, then apply it
if (isset($_GET['urgency']) && in_array($_GET['urgency'], ['Not Urgent', 'Kinda Bad', 'FIX NOW'])) {
    $filter['urgent'] = $_GET['urgency']; // Filter by urgency
}

// Handle message resolution submission (when admin resolves a message)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve'])) {
    // Get the message ID from the form (and cast it to ObjectId for MongoDB)
    $messageId = new MongoDB\BSON\ObjectId($_POST['message_id']);
    
    // Get the resolution text from the form input
    $resolution = trim($_POST['resolution']);

    // Check if resolution input is empty
    if (empty($resolution)) {
        // Add an error if the resolution text is empty
        $errors[] = "Resolution notes are required.";
    } else {
        // If resolution is provided, proceed to update the message in the database
        $updateResult = $messagesCollection->updateOne(
            ['_id' => $messageId],  // Find the message by its _id
            [
                '$set' => [
                    'resolved' => true,  // Mark the message as resolved
                    'resolution' => $resolution,  // Store the resolution notes
                    'resolved_by' => $_SESSION['username'],  // Store the username of the admin resolving it
                    'resolved_at' => new MongoDB\BSON\UTCDateTime(),  // Timestamp of when the message was resolved
                ]
            ]
        );

        // Check if the update was successful
        if ($updateResult->getModifiedCount() > 0) {
            // If successful, set a success message
            $success = "Message resolved successfully!";
        } else {
            // If no document was modified, show an error message
            $errors[] = "Failed to resolve the message.";
        }
    }
}

// Fetch unresolved messages based on the filter (urgency and unresolved status)
$messages = $messagesCollection->find($filter)->toArray();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Messages</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>

    <!-- Display Success/Error Banners -->
    <?php if ($success): ?>
        <div class="success-banner">
            <p><?= htmlspecialchars($success) ?></p>
        </div>
    <?php elseif (!empty($errors)): ?>
        <div class="error-banner">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p> <!-- Display each error message safely -->
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="customer-container">
        <h1>Manage Reported Issues</h1> <br>
        <p>Review and resolve unresolved messages below. Use the filters to narrow down by urgency.</p> <br>

        <!-- Filter Form for urgency -->
        <form method="GET" action="">
            <label for="urgency">Filter by Urgency:</label>
            <select id="urgency" name="urgency">
                <option value="">All</option>
                <option value="Not Urgent" <?= (isset($_GET['urgency']) && $_GET['urgency'] === 'Not Urgent') ? 'selected' : '' ?>>Not Urgent</option>
                <option value="Kinda Bad" <?= (isset($_GET['urgency']) && $_GET['urgency'] === 'Kinda Bad') ? 'selected' : '' ?>>Kinda Bad</option>
                <option value="FIX NOW" <?= (isset($_GET['urgency']) && $_GET['urgency'] === 'FIX NOW') ? 'selected' : '' ?>>FIX NOW</option>
            </select>
            <button type="submit" class="dashboard-btn">Apply Filter</button>
        </form>

        <hr>

        <!-- Display Messages -->
        <?php if (count($messages) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Urgency</th>
                        <th>Timestamp</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $message): ?>
                        <tr>
                            <!-- Display message data in a table row -->
                            <td><?= htmlspecialchars($message['username']) ?></td>
                            <td><?= htmlspecialchars($message['email']) ?></td>
                            <td><?= htmlspecialchars($message['subject']) ?></td>
                            <td><?= htmlspecialchars($message['message']) ?></td>
                            <td><?= htmlspecialchars($message['urgent']) ?></td>
                            <td><?= htmlspecialchars($message['timestamp']->toDateTime()->format('Y-m-d H:i:s')) ?></td>
                            <td>
                                <!-- Resolve Form -->
                                <form method="POST" action="">
                                    <textarea name="resolution" rows="3" placeholder="Add resolution notes..." required></textarea>
                                    <!-- Hidden input to pass the message ID for the resolution -->
                                    <input type="hidden" name="message_id" value="<?= htmlspecialchars($message['_id']) ?>">
                                    <button type="submit" name="resolve" class="small-btn">Resolve</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No unresolved messages found.</p>  <!-- Message if no unresolved messages are present -->
        <?php endif; ?>

        <hr>

        <!-- Back to Dashboard Button -->
        <form action="dashboard.php" method="get">
            <button type="submit" class="small-btn logout-btn">Back to Dashboard</button>
        </form>
    </div>

</body>
</html>
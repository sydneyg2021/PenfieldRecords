<?php
// Start the session to maintain user data across pages
session_start();

// Include MongoDB client library
require '../vendor/autoload.php';
use MongoDB\Client;

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// MongoDB Connection URI for connecting to the cloud database
$uri = "mongodb+srv://Admin:wMl9JKCLzNS6zlmx@cluster0.wylus.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0";
$client = new Client($uri);

// Select the 'Penfield' database from the MongoDB client
$db = $client->selectDatabase('Penfield');

// Fetch available collections and remove `Schemas`
$collections = array_filter(
    iterator_to_array($db->listCollectionNames(), false),
    fn($collection) => $collection !== 'Schemas'
);

// Initialize variables
$selectedCollection = $_POST['collection'] ?? ($_GET['collection'] ?? '');
$currentIndex = isset($_GET['index']) ? intval($_GET['index']) : 0;
$records = [];
$searchField = $_POST['search_field'] ?? $_GET['search_field'] ?? '';
$searchText = $_POST['search_text'] ?? $_GET['search_text'] ?? '';
$searchInitiated = isset($_POST['search']); // Check if search is initiated

// Function to handle nested fields in MongoDB
function renderRecord($record, $indent = 0) {
    $output = '<ul style="margin-left:' . ($indent * 20) . 'px;">';
    foreach ($record as $key => $value) {
        if (is_array($value) || is_object($value)) {
            $output .= '<li><strong>' . htmlspecialchars($key) . ':</strong></li>';
            $output .= renderRecord((array)$value, $indent + 1);
        } else {
            $output .= '<li><strong>' . htmlspecialchars($key) . ':</strong> ' . htmlspecialchars($value) . '</li>';
        }
    }
    $output .= '</ul>';
    return $output;
}

// Fetch records if a collection is selected
if ($selectedCollection) {
    $collection = $db->$selectedCollection;

    // Execute search only when explicitly initiated by the user
    if ($searchInitiated && $searchField && $searchText !== '') {
        $query = [$searchField => new \MongoDB\BSON\Regex($searchText, 'i')];
        $records = $collection->find($query)->toArray();
    } else {
        // Fetch all records if no search is performed
        $records = $collection->find()->toArray();
    }

    // Loop records if navigating
    $currentIndex = $currentIndex % max(count($records), 1); // Prevent division by 0
    if ($currentIndex < 0) {
        $currentIndex += count($records); // Loop back to last record
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage MongoDB Records</title>
    <link rel="stylesheet" href="../assets/styles.css">

    <script>
        // JavaScript for navigation and validation
        document.addEventListener("DOMContentLoaded", function () {
            const searchInput = document.getElementById("search-text");
            if (searchInput) {
                searchInput.addEventListener("keydown", function (event) {
                    if (event.key === "Enter") {
                        event.preventDefault();
                    }
                });
            }

            const searchForm = document.getElementById("search-form");
            if (searchForm) {
                searchForm.addEventListener("submit", function (event) {
                    const searchText = document.getElementById("search-text").value.trim();
                    if (!searchText) {
                        event.preventDefault();
                        alert("Please enter a search term.");
                    }
                });
            }
        });

        function navigateRecords(event) {
            const currentIndex = parseInt(document.getElementById('current-index').value);
            const totalRecords = parseInt(document.getElementById('total-records').value);
            let nextIndex = currentIndex;

            if (event.key === 'ArrowRight') {
                nextIndex = (currentIndex + 1) % totalRecords;
            } else if (event.key === 'ArrowLeft') {
                nextIndex = (currentIndex - 1 + totalRecords) % totalRecords;
            }

            const collection = document.getElementById('collection-name').value;
            const searchField = document.getElementById('search-field').value;
            const searchText = document.getElementById('search-text').value;

            const url = `?collection=${encodeURIComponent(collection)}&index=${nextIndex}&search_field=${encodeURIComponent(searchField)}&search_text=${encodeURIComponent(searchText)}`;
            window.location.href = url;
        }

        document.addEventListener('keydown', navigateRecords);
    </script>
</head>
<body>
<h1>Manage MongoDB Records</h1>

<a href="dashboard.php" class="button">Back to Dashboard</a>

<form method="POST" action="">
    <label for="collection">Select Collection:</label>
    <select name="collection" id="collection">
        <option value="">-- Select a Collection --</option>
        <?php foreach ($collections as $collection): ?>
            <option value="<?= htmlspecialchars($collection) ?>" <?= $collection === $selectedCollection ? 'selected' : '' ?>>
                <?= htmlspecialchars($collection) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Fetch Records</button>
</form>

<hr>

<?php if ($selectedCollection): ?>
    <h2>Search in Collection: <?= htmlspecialchars($selectedCollection) ?></h2>
    <form method="POST" action="" id="search-form">
        <input type="hidden" name="collection" value="<?= htmlspecialchars($selectedCollection) ?>">
        <label for="search_field">Select Field:</label>
        <select name="search_field" id="search-field">
            <?php
            $sampleDoc = $db->$selectedCollection->findOne();
            if ($sampleDoc) {
                $flatFields = flattenFields($sampleDoc);
                foreach ($flatFields as $field) {
                    echo "<option value=\"" . htmlspecialchars($field) . "\" " . ($field === $searchField ? 'selected' : '') . ">" . htmlspecialchars($field) . "</option>";
                }
            }
            ?>
        </select>
        <label for="search_text">Search Term:</label>
        <input type="text" name="search_text" id="search-text" value="<?= htmlspecialchars($searchText) ?>" required>
        <button type="submit" name="search">Search</button>
    </form>

    <?php if ($searchText): ?>
        <form method="GET" action="">
            <input type="hidden" name="collection" value="<?= htmlspecialchars($selectedCollection) ?>">
            <button type="submit">Show All Records</button>
        </form>
    <?php endif; ?>
<?php endif; ?>

<hr>

<?php if ($selectedCollection && count($records) > 0): ?>
    <h2>Records from Collection: <?= htmlspecialchars($selectedCollection) ?></h2>
    <div class="record-container">
        <?php foreach ($records as $index => $record): ?>
            <div class="record-box">
                <h3>Record <?= $index + 1 ?></h3>
                <?= renderRecord($record) ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php elseif ($selectedCollection): ?>
    <p>No records found in the selected collection.</p>
<?php endif; ?>
</body>
</html>
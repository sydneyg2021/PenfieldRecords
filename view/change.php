<?php
session_start();
require '../vendor/autoload.php';
use MongoDB\Client;
use MongoDB\BSON\ObjectId;

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// MongoDB Connection
$uri = $_ENV['MONGO_URI'];
$client = new MongoDB\Client($uri);
$db = $client->selectDatabase('Penfield');

// Fetch available collections excluding 'Users' and 'Messages'
$collections = iterator_to_array($db->listCollectionNames(), false);

// Initialize variables
$successMessage = '';
$errorMessage = '';
$existingData = null;
$documents = [];
$selectedCollection = null; // Store the selected collection
$schemaInfo = null; // Store the schema information for the selected collection

// Fetch schema information for the selected collection
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['collection'])) {
    $selectedCollection = $_GET['collection'];
    try {
        $schemaCollection = $db->Schemas;
        $schemaInfo = $schemaCollection->findOne(['schema_name' => $selectedCollection]);

        // If schema info exists, fetch documents for the selected collection
        if ($schemaInfo) {
            $collection = $db->$selectedCollection;
            $documents = $collection->find([]); // Fetch documents for the selected collection
        } else {
            $errorMessage = "Schema for this collection not found.";
        }
    } catch (Exception $e) {
        $errorMessage = "Error fetching schema information: " . $e->getMessage();
    }
}

// Fetch fields and documents for editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $selectedCollection = $_POST['collection'] ?? null;
    $searchValue = $_POST['search_value'] ?? null;

    if ($selectedCollection && $searchValue) {
        try {
            $collection = $db->$selectedCollection;
            $existingData = $collection->findOne(
                ['record_id' => $searchValue]
            );

            if ($existingData) {
                // Prepare fields for rendering
            } else {
                $errorMessage = "No document found with the specified record_id.";
            }
        } catch (Exception $e) {
            $errorMessage = "Error fetching document: " . $e->getMessage();
        }
    } else {
        $errorMessage = "Please select a collection and provide a valid record ID.";
    }
}

// Update document
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $selectedCollection = $_POST['collection'] ?? null;
    $documentId = $_POST['_id'] ?? null;

    if ($selectedCollection && $documentId) {
        try {
            $collection = $db->$selectedCollection;

            // Prepare and sanitize input data
            $data = $_POST;
            unset($data['collection'], $data['_id']); // Remove unwanted fields

            // Update the document in the collection
            $collection->updateOne(['_id' => new ObjectId($documentId)], ['$set' => $data]);
            $successMessage = "Document updated successfully!";
        } catch (Exception $e) {
            $errorMessage = "Error updating document: " . $e->getMessage();
        }
    } else {
        $errorMessage = "Please provide a valid collection and document ID.";
    }
}

// Utility to extract nested fields and structure them in a user-friendly format
function renderFieldInput($name, $fieldInfo, $value = null) {
    $inputValue = htmlspecialchars((string)($value ?? ''));
    $fieldType = $fieldInfo['type'] ?? 'string';

    $label = $fieldInfo['label'] ?? ucwords(str_replace('_', ' ', $name));
    $example = $fieldInfo['example'] ?? '';
    $explanation = $fieldInfo['explanation'] ?? '';

    $inputHtml = "<div class='nice-form-group'>
                    <div class='form-left'>
                        <label for='$name'>$label</label>
                        <p class='explanation'>$explanation</p>
                        <p class='example'>Example: $example</p>
                    </div>
                    <div class='form-right'>";
    
    if ($fieldType == 'string' || $fieldType == 'number' || $fieldType == 'boolean') {
        $inputHtml .= "<input type='text' id='$name' name='$name' value='$inputValue'>";
    } elseif ($fieldType == 'ObjectId') {
        $inputHtml .= "<input type='text' id='$name' name='$name' value='$inputValue' readonly>";
    } elseif ($fieldType == 'array') {
        $inputHtml .= "<textarea id='$name' name='$name'>$inputValue</textarea>";
    }

    $inputHtml .= "</div></div>";

    return $inputHtml;
}

// Recursively render nested fields
function renderNestedFields($prefix, $data, $schema) {
    $html = '';
    foreach ($data as $key => $value) {
        $fieldName = $prefix . '.' . $key;
        if (is_array($value) || is_object($value)) {
            // If nested, recursively render fields
            $html .= "<fieldset><legend>" . ucwords(str_replace('_', ' ', $fieldName)) . "</legend>";
            $html .= renderNestedFields($fieldName, $value, $schema);
            $html .= "</fieldset>";
        } else {
            $fieldInfo = $schema['fields'][$key] ?? [];
            $html .= renderFieldInput($fieldName, $fieldInfo, $value);
        }
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Records - Penfield Records</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <div class="insert-container">
        <h1>Penfield Records</h1>
        <h2>Edit Existing Record</h2>

        <?php if ($successMessage): ?>
            <div class="success-banner">
                <p><?= htmlspecialchars($successMessage) ?></p>
            </div>
        <?php elseif ($errorMessage): ?>
            <div class="error-banner">
                <p><?= htmlspecialchars($errorMessage) ?></p>
            </div>
        <?php endif; ?>

        <form method="GET"> 
            <div class="nice-form-group">
                <label for="collection">Select Collection:</label>
                <select id="collection" name="collection" required>
                    <option value="">-- Select Collection --</option>
                    <?php foreach ($collections as $collection): ?>
                        <option value="<?= htmlspecialchars($collection) ?>" <?= isset($selectedCollection) && $selectedCollection === $collection ? 'selected' : '' ?>>
                            <?= htmlspecialchars($collection) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="button-container">
                <button type="submit" name="submit_collection" class="btn">Load Collection</button> 
            </div>
        </form>

        <?php if ($selectedCollection): ?>
            <form method="POST">
                <input type="hidden" name="collection" value="<?= htmlspecialchars($selectedCollection) ?>"> 

                <div class="nice-form-group">
                    <label for="search_value">Search Record ID:</label>
                    <select name="search_value" id="search_value" required>
                        <option value="">-- Select Record ID --</option>
                        <?php foreach ($documents as $doc): ?>
                            <option value="<?= htmlspecialchars($doc['record_id']) ?>" <?= isset($existingData) && $existingData['record_id'] === $doc['record_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($doc['record_id']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="button-container">
                    <button type="submit" name="search" class="btn">Search</button>
                </div>
            </form>
        <?php endif; ?>

        <?php if ($existingData && $schemaInfo): ?>
            <form method="POST">
                <input type="hidden" name="collection" value="<?= htmlspecialchars($selectedCollection) ?>">
                <input type="hidden" name="_id" value="<?= htmlspecialchars($existingData['_id']) ?>">

                <?php
                // Render top-level fields
                foreach ($existingData as $field => $value) {
                    $fieldInfo = $schemaInfo['fields'][$field] ?? [];
                    echo renderFieldInput($field, $fieldInfo, $value);
                }

                // Render nested fields (if any)
                echo renderNestedFields('', $existingData, $schemaInfo);
                ?>

                <div class="button-container">
                    <button type="submit" name="update" class="btn">Save Changes</button>
                </div>
            </form>
        <?php endif; ?>

        <div class="button-container">
            <a href="dashboard.php" class="logout-btn">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>

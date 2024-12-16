<?php
require '../vendor/autoload.php'; // Load MongoDB library
use MongoDB\Client;

session_start();

// MongoDB Setup
// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Retrieve the MongoDB URI from the environment
$uri = $_ENV['MONGO_URI'];
$client = new Client($uri);
$db = $client->selectDatabase('Penfield');

// Utility: Flatten nested fields for schema-based field selection
function flattenFields($array, $prefix = '') {
    $fields = [];
    foreach ($array as $key => $value) {
        $fullKey = $prefix ? "{$prefix}.{$key}" : $key;
        if (is_array($value) || is_object($value)) {
            $fields = array_merge($fields, flattenFields((array)$value, $fullKey));
        } else {
            $fields[] = $fullKey;
        }
    }
    return $fields;
}

// API Logic
$action = $_GET['action'] ?? null;
$response = [];

try {
    switch ($action) {
        case 'getCollections':
            // Fetch available collections
            $collections = iterator_to_array($db->listCollectionNames(), false);
            $response = ['collections' => array_filter($collections, fn($col) => $col !== 'Schemas')];
            break;

        case 'getSchemaFields':
            // Fetch fields from the schema of the selected collection
            $collectionName = $_GET['collection'] ?? null;
            if ($collectionName) {
                $schema = $db->Schemas->findOne(['collection' => $collectionName]);
                $response = ['fields' => $schema ? flattenFields((array)$schema) : []];
            }
            break;

        case 'searchRecords':
            // Perform a search within a collection
            $collectionName = $_GET['collection'] ?? null;
            $field = $_GET['field'] ?? null;
            $text = $_GET['text'] ?? '';
            $skip = intval($_GET['skip'] ?? 0);
            $limit = intval($_GET['limit'] ?? 10);

            if ($collectionName && $field && $text) {
                $query = [$field => new MongoDB\BSON\Regex($text, 'i')];
                $records = $db->$collectionName->find($query, ['skip' => $skip, 'limit' => $limit])->toArray();
                $response = ['records' => $records];
            }
            break;

        case 'getRecords':
            // Fetch all records with pagination
            $collectionName = $_GET['collection'] ?? null;
            $skip = intval($_GET['skip'] ?? 0);
            $limit = intval($_GET['limit'] ?? 10);

            if ($collectionName) {
                $records = $db->$collectionName->find([], ['skip' => $skip, 'limit' => $limit])->toArray();
                $response = ['records' => $records];
            }
            break;

        default:
            $response = ['error' => 'Invalid action'];
    }
} catch (Exception $e) {
    $response = ['error' => $e->getMessage()];
}

header('Content-Type: application/json');
echo json_encode($response);
?>
<?php
header('Content-Type: application/json');
require '../vendor/autoload.php';
use MongoDB\Client;

// MongoDB Connection
$uri = $_ENV['MONGO_URI'];
$client = new MongoDB\Client($uri);
$db = $client->selectDatabase('Penfield');

// Fetch the schema (including required fields) for the selected collection
$collection = $_GET['collection'] ?? '';
$fields = getFieldsSchema($collection);

// Debugging: Check if fields were fetched successfully
if (!$fields) {
  echo json_encode([
    'success' => false,
    'message' => 'No fields defined for this collection or collection not found.'
  ]);
  exit();
}

$html = '';
try {
  foreach ($fields['fields'] as $section => $sectionFields) {
    // ... (rest of the code for generating HTML) 
  }
} catch (Exception $e) {
  // Log the exception for debugging
  error_log("fetch_fields.php: Exception: " . $e->getMessage()); 

  // Return an error response with more details
  echo json_encode([
    'success' => false,
    'message' => 'An error occurred while processing the request: ' . $e->getMessage()
  ]);
  exit();
}

echo json_encode([
  'success' => true,
  'html' => $html
]);

// Fetch fields schema dynamically from the database or other source
function getFieldsSchema($collection) {
  global $db; 

  // Ensure collection is not empty
  if (empty($collection)) {
    return false;
  }

  try {
    // Get schema from the "Schemas" collection
    $schemaCollection = $db->selectCollection('Schemas'); 
    $schema = $schemaCollection->findOne(['schema_name' => $collection]); 

    // Check if schema was found
    if (!$schema) {
      return false; 
    }

    // Ensure the required field exists and is an array
    $requiredFields = isset($schema['required']) && is_array($schema['required']) ? $schema['required'] : [];

    // Add a new field to the schema to explicitly handle the required fields
    $schema['required'] = $requiredFields;

    return $schema;
  } catch (Exception $e) {
    // Log the exception for debugging
    error_log("getFieldsSchema(): Exception: " . $e->getMessage()); 

    // Return false to indicate an error
    return false;
  }
}
<?php
header('Content-Type: application/json');
require '../vendor/autoload.php';
use MongoDB\Client;

// MongoDB Connection
$uri = "mongodb+srv://Admin:wMl9JKCLzNS6zlmx@cluster0.wylus.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0";
$client = new Client($uri);
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
foreach ($fields['fields'] as $section => $sectionFields) {
  // Check if the field is an object (nested fields)
  if (is_array($sectionFields)) {
    // Display section title for objects
    $html .= "<div class='section-container'>
                    <h3>" . ucwords(str_replace('_', ' ', $section)) . "</h3>";

    // Loop through nested fields of the object
    foreach ($sectionFields as $fieldName => $fieldDetails) {
      // Handle nested fields like grantee/grantor subfields
      if (is_array($fieldDetails) && isset($fieldDetails['label'])) {
        $required = in_array($fieldName, $fields['required']) ? 'required' : ''; // Check if field is required
        $label = isset($fieldDetails['label']) ? $fieldDetails['label'] : 'No label provided';
        $explanation = isset($fieldDetails['explanation']) ? $fieldDetails['explanation'] : 'No explanation provided';
        $example = isset($fieldDetails['example']) ? $fieldDetails['example'] : 'No example provided';

        // Determine appropriate input type based on field type (example)
        $inputType = isset($fieldDetails['type']) && $fieldDetails['type'] === 'boolean' ? 'checkbox' : 'text';

        // Display the nested field input
        $html .= "<div class='nice-form-group'>
                        <div class='form-left'>
                          <label for='$fieldName'>" . htmlspecialchars($label) . "</label>
                          <small>" . htmlspecialchars($explanation) . "</small>
                        </div>
                        <div class='form-right'>";

        if ($inputType === 'checkbox') {
          $html .= "<input type='$inputType' id='$fieldName' name='$fieldName' value='1' $required>";
        } else {
          $html .= "<input type='$inputType' id='$fieldName' name='$fieldName' placeholder='" . htmlspecialchars($example) . "' $required>";
        }

        $html .= "</div></div>"; 
      }
    }

    $html .= "</div><br>"; // Close section container
  } else {
    // Handle top-level individual fields like _id or record_id
    $required = in_array($section, $fields['required']) ? 'required' : ''; // Check if field is required
    $label = isset($sectionFields['label']) ? $sectionFields['label'] : 'No label provided';
    $explanation = isset($sectionFields['explanation']) ? $sectionFields['explanation'] : 'No explanation provided';
    $example = isset($sectionFields['example']) ? $sectionFields['example'] : 'No example provided';

    $html .= "<div class='nice-form-group'>
                  <div class='form-left'>
                    <label for='$section'>" . htmlspecialchars($label) . "</label>
                    <small>" . htmlspecialchars($explanation) . "</small>
                  </div>
                  <div class='form-right'>
                    <input type='text' id='$section' name='$section' placeholder='" . htmlspecialchars($example) . "' $required>
                  </div>
                </div>";
  }
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
}
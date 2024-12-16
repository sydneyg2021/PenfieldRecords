<?php

// Function to sanitize and process data based on the collection schema
function sanitizeData($postData, $collection) {
    global $db;

    // Fetch the schema for the given collection
    $schema = getCollectionSchema($collection);
    if (!$schema) {
        throw new Exception("Schema not found for collection: $collection");
    }

    $sanitizedData = [];

    // Loop through each field in the schema and sanitize the input data
    foreach ($schema['fields'] as $field => $fieldDetails) {
        if (isset($postData[$field])) {
            $value = $postData[$field];

            // Handle based on field type
            $sanitizedData[$field] = sanitizeField($value, $fieldDetails);
        }
    }

    // If there's a linked_record_id, ensure it's sanitized as a string or null
    if (isset($postData['linked_record_id'])) {
        $sanitizedData['linked_record_id'] = sanitizeLinkedRecordId($postData['linked_record_id']);
    }

    // Sanitize record_id (if it's provided in the post data)
    if (isset($postData['record_id'])) {
        $sanitizedData['record_id'] = sanitizeRecordId($postData['record_id']);
    }

    return $sanitizedData;
}


// Function to get the schema for a specific collection from the "Schemas" collection
function getCollectionSchema($collection) {
    global $db;

    $schemaCollection = $db->Schemas;
    return $schemaCollection->findOne(['collection' => $collection]);
}

// Function to sanitize each field based on its type and other properties
function sanitizeField($value, $fieldDetails) {
    // Check field type
    $type = $fieldDetails['type'] ?? 'string'; // Default to string if type is not specified

    // Sanitize based on type
    switch ($type) {
        case 'string':
            return filter_var($value, FILTER_SANITIZE_STRING);

        case 'int':
        case 'integer':
            return filter_var($value, FILTER_VALIDATE_INT) !== false ? (int)$value : null;

        case 'float':
            return filter_var($value, FILTER_VALIDATE_FLOAT) !== false ? (float)$value : null;

        case 'boolean':
            return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        case 'array':
            return sanitizeArray($value, $fieldDetails); // If it's an array, handle it

        case 'object':
            return sanitizeObject($value, $fieldDetails); // If it's an object, handle it

        default:
            // For unsupported or unknown types, return as a sanitized string by default
            return filter_var($value, FILTER_SANITIZE_STRING);
    }
}

// Function to sanitize arrays (for nested fields or multi-values)
function sanitizeArray($value, $fieldDetails) {
    // Assume the value is a JSON string or array that needs to be converted
    if (is_string($value)) {
        $value = json_decode($value, true); // Decode JSON to array if it's a string
    }

    if (is_array($value)) {
        // Recursively sanitize array values
        foreach ($value as $key => $subValue) {
            $value[$key] = sanitizeField($subValue, $fieldDetails); // Sanitize each subvalue
        }
    }

    return $value;
}

// Function to sanitize objects (can be used for sub-fields or nested structures)
function sanitizeObject($value, $fieldDetails) {
    if (is_string($value)) {
        // Decode JSON string into an object
        $value = json_decode($value, true); // Assuming the object is represented as a JSON string
    }

    if (is_array($value)) {
        // Iterate through object properties and sanitize each field
        foreach ($value as $key => $subValue) {
            $value[$key] = sanitizeField($subValue, $fieldDetails); // Sanitize nested fields
        }
    }

    return $value;
}


// Function to sanitize the record_id (ensure it's a valid string or null)
function sanitizeRecordId($value) {
    // Ensure the record_id is a valid string (could apply stricter validation here)
    return filter_var($value, FILTER_SANITIZE_STRING);
}

// Function to sanitize linked record ID
function sanitizeLinkedRecordId($value) {
    // Ensure the linked record ID is a valid string or null
    return filter_var($value, FILTER_SANITIZE_STRING);
}

?>

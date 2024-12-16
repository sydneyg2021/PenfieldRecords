<?php
require 'vendor/autoload.php';

use MongoDB\Client;

// Load .env variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$mongoClient = new Client($_ENV['MONGO_URI']);
$db = $mongoClient->selectDatabase($_ENV['DB_NAME']);
?>

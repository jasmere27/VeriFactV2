<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/ApiService.php'; // adjust if in subfolder

$api = new ApiService();
$news = $api->getTrendingNews('ph');

echo "<pre>";
print_r($news);

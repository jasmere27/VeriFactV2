<?php
header("Content-Type: application/json");
require_once '../ApiService.php'; // adjust path if needed

$category = $_GET['category'] ?? 'all';
$api = new ApiService();

try {
    $news = $api->getTrendingNews($category);
    echo json_encode($news, JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => 'Failed to fetch news: ' . $e->getMessage()
    ]);
}
?>

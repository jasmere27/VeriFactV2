<?php
header("Content-Type: application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database config
$host = 'localhost';
$db   = 'verifact_auth';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Connect
$dsn = "mysql:host=$host;dbname=$db;charset=$charset"; // FIXED
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
    exit;
}

// Get category
$category = $_GET['category'] ?? 'ph';

// Fetch news
$stmt = $pdo->prepare("SELECT title, link, date_published, image FROM news WHERE category = :category ORDER BY date_published DESC LIMIT 10");
$stmt->execute(['category' => $category]);
$news = $stmt->fetchAll();

echo json_encode($news, JSON_UNESCAPED_SLASHES);

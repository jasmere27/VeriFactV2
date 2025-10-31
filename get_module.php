<?php
require 'db.php'; // your database connection

$id = $_GET['id'] ?? null;
$table = $_GET['table'] ?? null;

$allowed_tables = ['cybersecurity_modules', 'cybersecurity_modules1', 'cybersecurity_modules2'];
if (!$id || !$table || !in_array($table, $allowed_tables)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM `$table` WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($data) {
    echo json_encode($data);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Module not found']);
}
?>
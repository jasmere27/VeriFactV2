<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $table = $_POST['table'] ?? null;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $type = $_POST['type'] ?? '';
    $video_url = $_POST['video_url'] ?? '';

    if (!$id || !$table) {
        echo "Update Failed! Missing data.";
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE $table SET title=?, description=?, type=?, video_url=?, updated_at=NOW() WHERE id=?");
        $success = $stmt->execute([$title, $description, $type, $video_url, $id]);

        echo $success ? "Update Successful!" : "Update Failed!";
    } catch (PDOException $e) {
        echo "Update Failed! " . $e->getMessage(); // <-- ADD THIS to see the exact SQL error
    }
}

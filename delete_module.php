<?php
session_start();
require 'db.php';

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM cybersecurity_modules WHERE id = ?");
$stmt->execute([$id]);

header("Location: dashboard.php");
exit;
?>

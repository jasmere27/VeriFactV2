<?php
session_start();
require 'db.php';

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$id = $_GET['id'];

// Fetch existing module
$stmt = $pdo->prepare("SELECT * FROM cybersecurity_modules WHERE id = ?");
$stmt->execute([$id]);
$module = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$module) {
    echo "Module not found!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $content = trim($_POST['content']);

    $stmt = $pdo->prepare("UPDATE cybersecurity_modules SET title=?, description=?, content=? WHERE id=?");
    $stmt->execute([$title, $description, $content, $id]);

    header("Location: dashboard.php");
    exit;
}
?>

<form method="POST">
  <label>Title:</label><br>
  <input type="text" name="title" value="<?= htmlspecialchars($module['title']) ?>" required><br><br>

  <label>Description:</label><br>
  <textarea name="description" required><?= htmlspecialchars($module['description']) ?></textarea><br><br>

  <label>Content:</label><br>
  <textarea name="content" required><?= htmlspecialchars($module['content']) ?></textarea><br><br>

  <button type="submit">Update</button>
</form>

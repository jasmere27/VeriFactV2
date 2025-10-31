<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    try {

        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->execute([$email]);

        if ($checkStmt->rowCount() > 0) {
            echo "Email already registered. Please login.";
        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword]);

            echo "Signup successful! Redirecting to login...";
            header("Location: index.php"); 
            exit;
        }
    } catch (PDOException $e) {
        echo "Signup failed: " . $e->getMessage();
    }
}
?>


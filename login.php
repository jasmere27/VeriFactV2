<?php
session_start();
require 'db.php'; // uses $pdo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Use PDO to prepare and execute
    $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Store user info in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $user['role']; // ✅ Store role for access control

        header("Location: index.php");
        exit;
    } else {
        echo "Invalid email or password.";
    }
}
?>

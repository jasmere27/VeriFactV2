<?php
session_start();
include 'db.php';

// Get user ID from session (you should store this at login)
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("User not logged in.");
}

// Get form input
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$new_password = $_POST['new_password'] ?? '';

// Sanitize input (basic)
$username = trim($username);
$email = trim($email);

// Update query
if (!empty($new_password)) {
    // If password field is filled, update all
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password_hash = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $email, $password_hash, $user_id);
} else {
    // Update only username and email
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $email, $user_id);
}

if ($stmt->execute()) {
    // Update session data if changed
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    header("Location: settings.php?status=success");
exit();
} else {
    echo "❌ Error updating profile: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

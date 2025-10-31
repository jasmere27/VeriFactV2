<?php
session_start();
$_SESSION['history'] = [];
echo json_encode(['success' => true]);
?>

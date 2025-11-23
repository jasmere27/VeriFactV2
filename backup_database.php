<?php
// Database credentials
$host = 'localhost';      // usually 'localhost'
$username = 'root';       // your MySQL username
$password = '';           // your MySQL password (empty if none)
$database = 'verifact_auth'; // your database name

// File name with current timestamp
$backupFile = 'backup_' . $database . '_' . date('Y-m-d_H-i-s') . '.sql';

// Folder where backups will be saved
$backupDir = __DIR__ . '/backups'; // creates a "backups" folder beside this file

// Make sure backup folder exists
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$backupPath = $backupDir . '/' . $backupFile;

// Command to create the database dump
$command = "mysqldump --user=$username --password=$password --host=$host $database > \"$backupPath\"";

// Execute command
exec($command, $output, $result);

// Check if backup succeeded
if ($result === 0) {
    echo "✅ Backup successful! File saved as: $backupPath";
} else {
    echo "❌ Backup failed. Please check your database credentials or permissions.";
}
?>

<?php
// API Configuration
define('API_BASE_URL', 'http://localhost:8080/api/v1');

// Database Configuration (if needed for future features)
define('DB_HOST', 'localhost');
define('DB_NAME', 'verifact');
define('DB_USER', 'root');
define('DB_PASS', '');

// File Upload Configuration
define('MAX_IMAGE_SIZE', 10 * 1024 * 1024); // 10MB
define('MAX_AUDIO_SIZE', 25 * 1024 * 1024); // 25MB
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// Allowed file types
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']);
define('ALLOWED_AUDIO_TYPES', ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/mp4', 'audio/m4a']);

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_HISTORY_ITEMS', 100);

// Error reporting (set to false in production)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('UTC');

// CORS headers for API requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}
?>

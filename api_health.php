<?php
header('Content-Type: application/json');
require_once 'services/ApiService.php';

try {
    $apiService = new ApiService();
    $healthStatus = $apiService->checkHealth();
    
    // Add additional health information
    $healthStatus['frontend_status'] = 'operational';
    $healthStatus['timestamp'] = date('Y-m-d H:i:s');
    $healthStatus['version'] = '1.0.0';
    
    // Check if uploads directory is writable
    $uploadsWritable = is_writable(__DIR__ . '/uploads/');
    $healthStatus['uploads_writable'] = $uploadsWritable;
    
    if (!$uploadsWritable) {
        $healthStatus['warnings'][] = 'Uploads directory is not writable';
    }
    
    // Check PHP extensions
    $requiredExtensions = ['curl', 'json', 'fileinfo'];
    $missingExtensions = [];
    
    foreach ($requiredExtensions as $ext) {
        if (!extension_loaded($ext)) {
            $missingExtensions[] = $ext;
        }
    }
    
    if (!empty($missingExtensions)) {
        $healthStatus['warnings'][] = 'Missing PHP extensions: ' . implode(', ', $missingExtensions);
    }
    
    echo json_encode($healthStatus, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Health check failed: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?>

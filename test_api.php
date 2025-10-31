<?php
require_once 'services/ApiService.php';

echo "<h1>VeriFact API Test Suite</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .success { background-color: #d4edda; border-color: #c3e6cb; }
    .error { background-color: #f8d7da; border-color: #f5c6cb; }
    .info { background-color: #d1ecf1; border-color: #bee5eb; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

$apiService = new ApiService();

// Test 1: Health Check
echo "<div class='test info'>";
echo "<h2>🏥 Health Check Test</h2>";
try {
    $health = $apiService->checkHealth();
    echo "<p><strong>Status:</strong> " . $health['status'] . "</p>";
    echo "<p><strong>Message:</strong> " . $health['message'] . "</p>";
    echo "<pre>" . json_encode($health, JSON_PRETTY_PRINT) . "</pre>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Health check failed: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test 2: Text Analysis
echo "<div class='test'>";
echo "<h2>📝 Text Analysis Test</h2>";
try {
    $testText = "Breaking: Scientists discover that the Earth is actually flat and NASA has been lying to us for decades!";
    echo "<p><strong>Test Text:</strong> " . htmlspecialchars($testText) . "</p>";
    
    $result = $apiService->analyzeText($testText);
    
    if (isset($result['error']) && $result['error']) {
        echo "<div class='error'>❌ Error: " . $result['message'] . "</div>";
    } else {
        echo "<div class='success'>✅ Text analysis successful!</div>";
        echo "<p><strong>Result:</strong> " . ($result['is_fake'] ? 'Likely Fake' : 'Likely Legit') . "</p>";
        echo "<p><strong>Confidence:</strong> " . $result['confidence'] . "%</p>";
        echo "<p><strong>Explanation:</strong> " . htmlspecialchars($result['explanation']) . "</p>";
    }
    
    echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Text analysis failed: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 3: API Endpoints Availability
echo "<div class='test info'>";
echo "<h2>🔗 API Endpoints Test</h2>";

$endpoints = [
    'Text Analysis' => '/isFakeNews?news=test',
    'Image Analysis' => '/analyzeImage',
    'Audio Analysis' => '/analyzeAudio'
];

foreach ($endpoints as $name => $endpoint) {
    echo "<h3>$name</h3>";
    
    try {
        $url = 'http://localhost:8080/api/v1' . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            echo "<p class='error'>❌ Connection failed: $error</p>";
        } elseif ($httpCode == 200 || $httpCode == 400) { // 400 is expected for some endpoints without proper data
            echo "<p class='success'>✅ Endpoint accessible (HTTP $httpCode)</p>";
        } else {
            echo "<p class='error'>❌ Unexpected response (HTTP $httpCode)</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Test failed: " . $e->getMessage() . "</p>";
    }
}
echo "</div>";

// Test 4: Configuration Check
echo "<div class='test info'>";
echo "<h2>⚙️ Configuration Check</h2>";

echo "<h3>PHP Extensions</h3>";
$extensions = ['curl', 'json', 'fileinfo', 'gd'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "<p>" . ($loaded ? "✅" : "❌") . " $ext: " . ($loaded ? "Loaded" : "Not loaded") . "</p>";
}

echo "<h3>File Permissions</h3>";
$uploadDir = __DIR__ . '/uploads/';
$writable = is_writable($uploadDir);
echo "<p>" . ($writable ? "✅" : "❌") . " Uploads directory writable: " . ($writable ? "Yes" : "No") . "</p>";

echo "<h3>Configuration</h3>";
echo "<p><strong>API Base URL:</strong> " . (defined('API_BASE_URL') ? API_BASE_URL : 'Not configured') . "</p>";
echo "<p><strong>Max Image Size:</strong> " . (defined('MAX_IMAGE_SIZE') ? number_format(MAX_IMAGE_SIZE / 1024 / 1024, 1) . 'MB' : 'Not configured') . "</p>";
echo "<p><strong>Max Audio Size:</strong> " . (defined('MAX_AUDIO_SIZE') ? number_format(MAX_AUDIO_SIZE / 1024 / 1024, 1) . 'MB' : 'Not configured') . "</p>";

echo "</div>";

echo "<div class='test info'>";
echo "<h2>📋 Next Steps</h2>";
echo "<ol>";
echo "<li>Make sure your Spring Boot application is running on <code>http://localhost:8080</code></li>";
echo "<li>Verify all endpoints are accessible and returning expected responses</li>";
echo "<li>Test file uploads with actual image and audio files</li>";
echo "<li>Check the browser console for any JavaScript errors</li>";
echo "<li>Monitor the Spring Boot application logs for any errors</li>";
echo "</ol>";
echo "</div>";
?>

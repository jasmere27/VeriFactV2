<?php
// Debug script to test response parsing
require_once 'services/ApiService.php';

echo "<h1>Response Parsing Debug</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .debug { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #f8f9fa; }
    .result { margin: 10px 0; padding: 10px; border-radius: 3px; }
    .fake { background: #f8d7da; border: 1px solid #f5c6cb; }
    .real { background: #d4edda; border: 1px solid #c3e6cb; }
    pre { background: #e9ecef; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

// Sample response that matches your screenshot
$sampleResponse = 'Corrected Statement: "In a recent incident during the Liverpool FC victory parade, a car drove into a crowd, resulting in serious injuries to a child and an adult, with dozens more injured. The police have arrested a 53-year-old man who is believed to be the driver, and they are not treating the incident as terrorism."

Analysis: Likely Real. This statement reflects a tragic event that occurred during a public celebration. Current reports confirm that a car did drive into a crowd at the Liverpool FC victory parade on May 26, 2025, injuring many people, including serious injuries to at least one child and one adult. The driver has been arrested, and authorities have stated that they are not considering this incident as a terrorist act.

The credible sources used for verification include:
- BBC News: "Dozens injured after driver plows through pedestrians at Liverpool victory parade" [https://www.bbc.co.uk/news/uk-england-merseyside-65509224] (https://www.bbc.co.uk/news/uk-england-merseyside-65509224) (published May 27, 2025)
- Reuters: "Car plows into crowd of Liverpool soccer fans; dozens hurt; officials" [https://www.reuters.com/world/uk/car-plows-into-crowd-liverpool-soccer-fans-dozens-hurt-officials-2025-05-26/](https://www.reuters.com/world/uk/car-plows-into-crowd-liverpool-soccer-fans-dozens-hurt-officials-2025-05-26/)';

echo "<div class='debug'>";
echo "<h2>Sample Response (from your screenshot)</h2>";
echo "<pre>" . htmlspecialchars($sampleResponse) . "</pre>";
echo "</div>";

// Test the parsing
$apiService = new ApiService();

// Use reflection to access private method for testing
$reflection = new ReflectionClass($apiService);
$method = $reflection->getMethod('parseAnalysisResponse');
$method->setAccessible(true);

$result = $method->invoke($apiService, $sampleResponse);

echo "<div class='debug'>";
echo "<h2>Parsed Result</h2>";
echo "<div class='result " . ($result['is_fake'] ? 'fake' : 'real') . "'>";
echo "<strong>Status:</strong> " . ($result['is_fake'] ? 'FAKE' : 'REAL') . "<br>";
echo "<strong>Confidence:</strong> " . $result['confidence'] . "%<br>";
echo "<strong>Should show:</strong> " . $result['confidence'] . "% LIKELY " . ($result['is_fake'] ? 'FAKE' : 'REAL');
echo "</div>";
echo "</div>";

// Test different response patterns
$testCases = [
    'Analysis: Likely Fake. This is misinformation.' => 'Should be FAKE',
    'Analysis: Likely Real. This is verified information.' => 'Should be REAL',
    'This news appears to be fake and misleading.' => 'Should be FAKE',
    'The information is authentic and verified.' => 'Should be REAL',
];

echo "<div class='debug'>";
echo "<h2>Test Cases</h2>";
foreach ($testCases as $testResponse => $expected) {
    $testResult = $method->invoke($apiService, $testResponse);
    $status = $testResult['is_fake'] ? 'FAKE' : 'REAL';
    $correct = (strpos($expected, $status) !== false) ? '✅' : '❌';
    
    echo "<div class='result " . ($testResult['is_fake'] ? 'fake' : 'real') . "'>";
    echo "$correct <strong>Input:</strong> " . htmlspecialchars($testResponse) . "<br>";
    echo "<strong>Expected:</strong> $expected<br>";
    echo "<strong>Got:</strong> {$testResult['confidence']}% LIKELY $status";
    echo "</div>";
}
echo "</div>";
?>

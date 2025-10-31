
<?php

class ApiService {
    private $baseUrl;
    private $tesseractPath;

    public function __construct() {
        require_once __DIR__ . '/../config.php';
        $this->baseUrl = API_BASE_URL;

        // Path to Tesseract OCR on your system
        $this->tesseractPath = "C:\\Program Files\\Tesseract-OCR\\tesseract.exe";

        // Optional: Log PHP errors to a file (create /logs folder)
        ini_set('log_errors', 1);
        ini_set('error_log', __DIR__ . '/../logs/api_errors.log');
    }

    /**
     * Analyze text content for fake news
     */
    public function analyzeText($text) {
        try {
            $url = $this->baseUrl . '/isFakeNews?' . http_build_query(['news' => $text]);
            $response = $this->makeRequest($url, 'GET');

            if ($response === false) {
                throw new Exception('Failed to connect to AI service');
            }

            return $this->parseResponse($response, 'text');
        } catch (Exception $e) {
            error_log('Text analysis error: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Failed to analyze text: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Analyze image by extracting text using OCR (Tesseract), then analyzing it
     */
    public function analyzeImageWithLocalOCR($imagePath) {
        try {
            if (!file_exists($imagePath)) {
                throw new Exception('Image file not found.');
            }

            // Extract text using Tesseract
            $command = "\"{$this->tesseractPath}\" " . escapeshellarg($imagePath) . " stdout 2>&1";
            $ocrText = shell_exec($command);
            $ocrText = trim($ocrText);

            if (empty($ocrText)) {
                throw new Exception('No text detected in image.');
            }

            // Use the extracted text to analyze
            $analysisResult = $this->analyzeText($ocrText);

            // Include extracted text in result
            $analysisResult['extracted_text'] = $ocrText;
            return $analysisResult;

        } catch (Exception $e) {
            error_log('OCR image analysis error: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Failed to analyze image: ' . $e->getMessage()
            ];
        }
    }

    

    /**
     * (Optional) Use remote API to analyze image if backend handles OCR
     */
    public function analyzeImage($imageFile) {
        try {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!in_array($imageFile['type'], $allowedTypes)) {
                throw new Exception('Invalid image type. Please upload JPG, PNG, or WebP files.');
            }

            if ($imageFile['size'] > 10 * 1024 * 1024) {
                throw new Exception('Image file too large. Maximum size is 10MB.');
            }

            $url = $this->baseUrl . '/analyzeImage';

            $curlFile = new CURLFile(
                $imageFile['tmp_name'],
                $imageFile['type'],
                $imageFile['name']
            );

            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => $curlFile]);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: multipart/form-data'
            ]);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new Exception("cURL Error: " . $error);
            }

            curl_close($ch);

            return $this->parseResponse($response, 'image');

        } catch (Exception $e) {
            error_log('Image analysis error: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Failed to analyze image: ' . $e->getMessage()
            ];
        }
    }
    /**
     * Analyze audio content for fake news
     */
    public function analyzeAudio($audioFile) {
        try {
            // Validate audio file
            $allowedTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/mp4', 'audio/m4a'];
            if (!in_array($audioFile['type'], $allowedTypes)) {
                throw new Exception('Invalid audio type. Please upload MP3, WAV, or M4A files.');
            }
            
            // Check file size (25MB limit)
            if ($audioFile['size'] > 25 * 1024 * 1024) {
                throw new Exception('Audio file too large. Maximum size is 25MB.');
            }
            
            $url = $this->baseUrl . '/analyzeAudio';
            
            // Create multipart form data
            $postData = [
                'file' => new CURLFile(
                    $audioFile['tmp_name'],
                    $audioFile['type'],
                    $audioFile['name']
                )
            ];
            
            $response = $this->makeRequest($url, 'POST', $postData, true);
            
            if ($response === false) {
                throw new Exception('Failed to connect to AI service');
            }
            
            return $this->parseResponse($response, 'audio');
            
        } catch (Exception $e) {
            error_log('Audio analysis error: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Failed to analyze audio: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check API health status
     */
    public function checkHealth() {
        try {
            // Try to make a simple request to check if the service is running
            $url = $this->baseUrl . '/isFakeNews?' . http_build_query(['news' => 'test']);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($response === false || !empty($error)) {
                return [
                    'status' => 'unhealthy',
                    'message' => 'Service unavailable: ' . $error
                ];
            }
            
            if ($httpCode >= 200 && $httpCode < 300) {
                return [
                    'status' => 'healthy',
                    'message' => 'Service operational'
                ];
            } else {
                return [
                    'status' => 'unhealthy',
                    'message' => 'Service returned HTTP ' . $httpCode
                ];
            }
            
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Health check failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Make HTTP request to the API
     */
    private function makeRequest($url, $method = 'GET', $data = null, $isMultipart = false) {
        $ch = curl_init();
        
        // Basic cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Increased timeout for AI processing
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        // Set headers
        $headers = [
            'User-Agent: VeriFact-Frontend/1.0',
            'Accept: application/json, text/plain, */*'
        ];
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            
            if ($data !== null) {
                if ($isMultipart) {
                    // For file uploads, don't set Content-Type header
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                } else {
                    // For JSON data
                    $headers[] = 'Content-Type: application/json';
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
            }
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        // Handle cURL errors
        if ($response === false || !empty($error)) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        // Handle HTTP errors
        if ($httpCode >= 400) {
            throw new Exception('HTTP Error ' . $httpCode . ': ' . $response);
        }
        
        return $response;
    }
    
    /**
     * Parse API response and format for frontend
     */
    private function parseResponse($response, $type) {
        try {
            // Try to decode as JSON first
            $jsonData = json_decode($response, true);
            
            if ($jsonData !== null) {
                // Handle structured JSON response
                return $this->formatStructuredResponse($jsonData, $type);
            } else {
                // Handle plain text response from your current controller
                return $this->formatTextResponse($response, $type);
            }
            
        } catch (Exception $e) {
            throw new Exception('Failed to parse API response: ' . $e->getMessage());
        }
    }
    
    /**
     * Format structured JSON response
     */
    private function formatStructuredResponse($data, $type) {
        // If your API returns structured data, handle it here
        return [
            'is_fake' => $data['is_fake'] ?? false,
            'confidence' => $data['confidence'] ?? 0,
            'explanation' => $data['explanation'] ?? 'Analysis completed.',
            'sources' => $data['sources'] ?? $this->getDefaultSources(),
            'cybersecurity_tips' => $this->getCybersecurityTips($type),
            'analysis_type' => $type,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Format plain text response from current controller
     */
    private function formatTextResponse($response, $type) {
        // Parse your current text-based response
        $response = trim($response);
        
        // Determine if content is fake based on response
        $isFake = $this->determineFakeStatus($response);
        $confidence = $this->extractConfidence($response);
        $explanation = $this->formatExplanation($response, $type);
        
        return [
            'is_fake' => $isFake,
            'confidence' => $confidence,
            'explanation' => $explanation,
            'sources' => $this->getDefaultSources(),
            'cybersecurity_tips' => $this->getCybersecurityTips($type),
            'analysis_type' => $type,
            'timestamp' => date('Y-m-d H:i:s'),
            'raw_response' => $response
        ];
    }
    
    /**
     * Determine if content is fake based on response text
     */
    private function determineFakeStatus($response) {
    $response = strtolower($response);

    if (strpos($response, 'likely fake') !== false) {
        return true;
    } elseif (strpos($response, 'likely real') !== false) {
        return false;
    } elseif (strpos($response, 'uncertain') !== false) {
        return null; // Handle uncertainty gracefully
    }

    // Fallback using keywords (optional)
    $fakeKeywords = ['fake', 'false', 'misleading', 'misinformation', 'fabricated', 'unverified'];
    $legitKeywords = ['true', 'verified', 'legitimate', 'authentic', 'factual', 'accurate'];

    $fakeScore = 0;
    $legitScore = 0;

    foreach ($fakeKeywords as $keyword) {
        if (strpos($response, $keyword) !== false) {
            $fakeScore++;
        }
    }

    foreach ($legitKeywords as $keyword) {
        if (strpos($response, $keyword) !== false) {
            $legitScore++;
        }
    }

    if ($fakeScore === 0 && $legitScore === 0) {
        return null; // truly undetermined
    }

    return $fakeScore >= $legitScore;
}


    
/**
 * Extract confidence percentage from AI response
 */
private function extractConfidence($response, $userInput = '') {
    // Normalize response
    $lowerResp = strtolower($response);

    // 1. Extract exact accuracy percentage if provided
    if (preg_match('/accuracy percentage:\s*(\d+)%/i', $response, $matches)) {
        $percentage = intval($matches[1]);

        // Force 100% if AI conclusion says "Likely Real"
        if (strpos($lowerResp, 'likely real') !== false) {
            return 100;
        }

        // Force 100% if AI conclusion says "Likely Fake"
        if (strpos($lowerResp, 'likely fake') !== false) {
            return 100;
        }

        // Force 50% if AI conclusion says "Likely Mixed"
        if (strpos($lowerResp, 'likely mixed') !== false) {
            return 50;
        }

        return $percentage;
    }

    // ✅ 2. Detect "Uncertain"
    if (strpos($lowerResp, 'uncertain') !== false) {
        return 0; // or set to 50 if you prefer
    }

    // 3. Fallback keyword-based estimation
    if (strpos($lowerResp, 'likely real') !== false) {
        return 100;
    } elseif (strpos($lowerResp, 'likely fake') !== false) {
        return 100;
    } elseif (strpos($lowerResp, 'likely mixed') !== false) {
        return 50;
    } elseif (strpos($lowerResp, 'highly') !== false || strpos($lowerResp, 'very') !== false) {
        return 90;
    } elseif (strpos($lowerResp, 'likely') !== false || strpos($lowerResp, 'probably') !== false) {
        return 75;
    } elseif (strpos($lowerResp, 'possibly') !== false || strpos($lowerResp, 'might') !== false) {
        return 60;
    }

    // Default fallback
    return 70;
}



    /**
     * Format explanation text
     */
    private function formatExplanation($response, $type) {
        $typeLabels = [
            'text' => 'text content',
            'image' => 'image content',
            'audio' => 'audio content'
        ];
        
        $typeLabel = $typeLabels[$type] ?? 'content';
        
        // Clean up the response
        $explanation = trim($response);
        
        // Add context if response is too short
        if (strlen($explanation) < 50) {
            $explanation = "Our AI analysis of the {$typeLabel} indicates: " . $explanation . 
                          " This assessment is based on various factors including content patterns, source verification, and linguistic analysis.";
        }
        
        return $explanation;
    }
    
    private function getSummary($text, $length = 100) {
    $text = strip_tags($text); // Remove HTML tags if any
    if (strlen($text) <= $length) {
        return $text;
    }
    // Cut off at last space before length limit to avoid breaking words
    $truncated = substr($text, 0, $length);
    return substr($truncated, 0, strrpos($truncated, ' ')) . '...';
}

    /**
     * Get default trusted sources
     */
    private function getDefaultSources() {
        return [
            [
                'name' => 'Reuters Fact Check',
                'url' => 'https://www.reuters.com/fact-check/'
            ],
            [
                'name' => 'AP Fact Check',
                'url' => 'https://apnews.com/hub/ap-fact-check'
            ],
            [
                'name' => 'Snopes',
                'url' => 'https://www.snopes.com/'
            ],
            [
                'name' => 'PolitiFact',
                'url' => 'https://www.politifact.com/'
            ],
            [
                'name' => 'BBC Reality Check',
                'url' => 'https://www.bbc.com/news/reality_check'
            ]
        ];
    }
    
    /**
     * Get cybersecurity tips based on content type
     */

        /**
     * Get cybersecurity tips based on content type
     */
    private function getCybersecurityTips($type) {
        $tips = [
            'text' => [
                'Always verify news from multiple trusted sources before sharing',
                'Check the publication date and author credentials',
                'Look for emotional language that might indicate bias',
                'Cross-reference claims with fact-checking websites',
                'Be skeptical of sensational headlines or claims'
            ],
            'image' => [
                'Reverse image search to find the original source',
                'Check image metadata for manipulation signs',
                'Verify the context and date of the image',
                'Look for inconsistencies in lighting or shadows',
                'Be cautious of images with emotional captions'
            ],
            'audio' => [
                'Verify the speaker\'s identity and credentials',
                'Check if the audio has been edited or manipulated',
                'Look for background noise inconsistencies',
                'Cross-reference spoken claims with written sources',
                'Be aware of deepfake audio technology'
            ]
        ];

        // Compatible fallback for older PHP versions and safe access
        return isset($tips[$type]) ? $tips[$type] : $tips['text'];
    }
}

// End of class - DO NOT add a PHP close tag here (recommended)
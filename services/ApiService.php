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

    /** Analyze text content **/
    public function analyzeText($text) {
    try {
        $url = $this->baseUrl . '/api/v1/isFakeNews';


        // Backend requires: { "news": "string" }
        $data = json_encode(['news' => $text]);

        $headers = [
            "Content-Type: application/json",
            "Accept: application/json"
        ];

        // Force POST + JSON body
        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => implode("\r\n", $headers),
                'content' => $data,
                'ignore_errors' => true 
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

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
 * Analyze image using local OCR and optional user instruction 
 */
public function analyzeImageWithLocalOCR($imagePath, $userInstruction = null) {
    try {
        if (!file_exists($imagePath)) {
            throw new Exception('Image file not found.');
        }

        // Extract text using Tesseract OCR
        $command = "\"{$this->tesseractPath}\" " . escapeshellarg($imagePath) . " stdout 2>&1";
        $ocrText = trim(shell_exec($command));

        if (empty($ocrText)) {
            throw new Exception('No text detected in image.');
        }

        // Combine OCR text with user instruction (if any)
        $aiInput = "User Instruction Result: " . ($userInstruction ?? "") . "\n\nNews Analysis Result: $ocrText";

        // Analyze combined input using text analysis method
        $analysisResult = $this->analyzeText($aiInput);

        // Keep extracted text separately for reference
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
 * Analyze uploaded image remotely with optional user instruction 
 */
public function analyzeImage($imageFile, $instruction = '') {
    try {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!in_array($imageFile['type'], $allowedTypes)) {
            throw new Exception('Invalid image type. Please upload JPG, PNG, or WebP files.');
        }

        if ($imageFile['size'] > 10 * 1024 * 1024) {
            throw new Exception('Image file too large. Maximum size is 10MB.');
        }

        $url = $this->baseUrl . '/api/v1/analyzeImage';
        $curlFile = new CURLFile($imageFile['tmp_name'], $imageFile['type'], $imageFile['name']);

        // Always send instruction, even if empty
        $postData = [
            'file' => $curlFile,
            'instruction' => $instruction
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => ['Content-Type: multipart/form-data'],
            CURLOPT_TIMEOUT => 180,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TCP_KEEPALIVE => 1,
            CURLOPT_TCP_KEEPIDLE => 30,
            CURLOPT_TCP_KEEPINTVL => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL Error: " . $error);
        }

        curl_close($ch);

        // Parse the AI response and handle User Instruction section
        $parsed = $this->parseResponse($response, 'image');

        // Ensure the explanation is clean and separated
        if (!empty($parsed['explanation']) && strpos($parsed['explanation'], 'User Instruction Result:') !== false) {
            $parts = explode('News Analysis Result:', $parsed['explanation']);
            $instructionResult = trim(str_replace('User Instruction Result:', '', $parts[0]));
            $analysisText = isset($parts[1]) ? trim($parts[1]) : '';

            // Clean unnecessary symbols
            $instructionResult = implode("\n", array_filter(array_map(fn($line) => trim(str_replace(['*','#'], '', $line)), explode("\n", $instructionResult))));
            $analysisText = implode("\n", array_filter(array_map(fn($line) => trim(str_replace(['*','#'], '', $line)), explode("\n", $analysisText))));

            $parsed['instruction_result'] = $instructionResult;
            $parsed['analysis_result'] = $analysisText;
        }

        return $parsed;

    } catch (Exception $e) {
        error_log('Image analysis error: ' . $e->getMessage());
        return [
            'error' => true,
            'message' => 'Failed to analyze image: ' . $e->getMessage()
        ];
    }
}

    /** Analyze audio **/
    public function analyzeAudio($audioFile) {
        try {
            $allowedTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/mp4', 'audio/m4a'];
            if (!in_array($audioFile['type'], $allowedTypes)) {
                throw new Exception('Invalid audio type. Please upload MP3, WAV, or M4A files.');
            }

            if ($audioFile['size'] > 25 * 1024 * 1024) {
                throw new Exception('Audio file too large. Maximum size is 25MB.');
            }

            $url = $this->baseUrl . '/api/v1/analyzeAudio';
            $postData = [
                'file' => new CURLFile($audioFile['tmp_name'], $audioFile['type'], $audioFile['name'])
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

    /** Fetch RSS feed safely **/
    private function loadRSS($url) {
        $content = @file_get_contents($url);

        if (!$content) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
            $content = curl_exec($ch);
            curl_close($ch);
        }

        return $content ? simplexml_load_string($content) : false;
    }

    /** Get news from Google RSS based on a search query **/
    private function getNews($query, $limit = 10) {
        $rssURL = "https://news.google.com/rss/search?q=" . urlencode($query) . "&hl=en-PH&gl=PH&ceid=PH:en";
        $rss = $this->loadRSS($rssURL);

        $items = [];
        if ($rss && isset($rss->channel->item)) {
            foreach ($rss->channel->item as $item) {
                $namespaces = $item->getNameSpaces(true);
                $media = isset($namespaces['media']) ? $item->children($namespaces['media']) : null;
                $image = (string)($media->thumbnail->attributes()->url ?? '');

                if (!$image) {
                    preg_match('/<img[^>]+src=["\']([^"\']+)/', $item->description, $img);
                    $image = $img[1] ?? "assets/default-news.jpg";
                }

                $items[] = [
                    "title" => (string)$item->title,
                    "link"  => (string)$item->link,
                    "date"  => date("M d, Y", strtotime($item->pubDate)),
                    "image" => $image
                ];

                if (count($items) >= $limit) break;
            }
        }

        if (empty($items)) {
            $items[] = [
                "title" => "No news available",
                "link" => "#",
                "date" => "",
                "image" => "assets/default-news.jpg"
            ];
        }

        return $items;
    }

    /** Public method to get trending news by category **/
    public function getTrendingNews($category = 'all') {
        $categories = [
            "all" => ["Philippines", "world news", "technology news", "politics"],
            "ph" => ["Philippines"],
            "world" => ["world news"],
            "technology" => ["technology news"],
            "politics" => ["politics"]
        ];

        $result = [];
        if (!isset($categories[$category])) {
            $category = 'all';
        }

        foreach ($categories[$category] as $cat) {
            $result = array_merge($result, $this->getNews($cat));
        }

        return $result;
    }

    /** Check API health **/
    public function checkHealth() {
        try {
            $url = $this->baseUrl . '/isFakeNews?' . http_build_query(['news' => 'test']);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 8,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false || !empty($error)) {
                return ['status' => 'unhealthy', 'message' => 'Service unavailable: ' . $error];
            }

            if ($httpCode >= 200 && $httpCode < 300) {
                return ['status' => 'healthy', 'message' => 'Service operational'];
            } else {
                return ['status' => 'unhealthy', 'message' => 'Service returned HTTP ' . $httpCode];
            }

        } catch (Exception $e) {
            return ['status' => 'unhealthy', 'message' => 'Health check failed: ' . $e->getMessage()];
        }
    }

    /** Make HTTP Request (core method) **/
    private function makeRequest($url, $method = 'GET', $data = null, $isMultipart = false) {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 180,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TCP_KEEPALIVE => 1,
            CURLOPT_TCP_KEEPIDLE => 30,
            CURLOPT_TCP_KEEPINTVL => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_FAILONERROR => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $headers = [
            'User-Agent: VeriFact-Frontend/1.0',
            'Accept: application/json, text/plain, */*'
        ];

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data !== null) {
                if ($isMultipart) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                } else {
                    $headers[] = 'Content-Type: application/json';
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || !empty($error)) {
            throw new Exception('cURL Error: ' . $error);
        }

        if ($httpCode >= 400) {
            throw new Exception('HTTP Error ' . $httpCode . ': ' . $response);
        }

        return $response;
    }

    /** Response parsing and formatting **/
    private function parseResponse($response, $type) {
        try {
            $jsonData = json_decode($response, true);
            if ($jsonData !== null) {
                return $this->formatStructuredResponse($jsonData, $type);
            } else {
                return $this->formatTextResponse($response, $type);
            }
        } catch (Exception $e) {
            throw new Exception('Failed to parse API response: ' . $e->getMessage());
        }
    }

    private function formatStructuredResponse($data, $type) {
        return [
            'is_fake' => $data['is_fake'] ?? false,
            'confidence' => $data['confidence'] ?? 0,
            'explanation' => $this->cleanMarkdown($data['explanation'] ?? 'Analysis completed.'),
            'sources' => $data['sources'] ?? $this->getDefaultSources(),
            'cybersecurity_tips' => $this->getCybersecurityTips($type),
            'analysis_type' => $type,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    private function formatTextResponse($response, $type) {
        $response = trim($response);
        $cleanedResponse = $this->cleanMarkdown($response);

        return [
            'classification' => $this->determineClassification($cleanedResponse),
            'is_fake' => $this->determineFakeStatus($cleanedResponse),
            'confidence' => $this->extractConfidence($cleanedResponse),
            'explanation' => $this->formatExplanation($cleanedResponse, $type),
            'sources' => $this->getDefaultSources(),
            'cybersecurity_tips' => $this->getCybersecurityTips($type),
            'analysis_type' => $type,
            'timestamp' => date('Y-m-d H:i:s'),
            'raw_response' => $cleanedResponse
        ];
    }

    /** Helper: Clean Markdown and symbols **/
    private function cleanMarkdown($text) {
        // Remove headers (###, ##, #)
        $text = preg_replace('/^#+\s*/m', '', $text);

        // Remove bold/italic markers **, __, *, _
        $text = str_replace(['**', '__', '*', '_'], '', $text);

        // Convert numbered lists to dash
        $text = preg_replace('/^\s*\d+\.\s*/m', '- ', $text);

        // Remove excessive whitespace
        $text = preg_replace('/\n{2,}/', "\n\n", $text);

        return trim($text);
    }

    /** Helper: Fake detection **/
    private function determineFakeStatus($response) {
        $response = strtolower($response);
        if (strpos($response, 'likely fake') !== false) return true;
        if (strpos($response, 'likely real') !== false) return false;
        if (strpos($response, 'uncertain') !== false) return null;

        $fakeKeywords = ['fake', 'false', 'misleading', 'fabricated', 'unverified'];
        $realKeywords = ['true', 'verified', 'legitimate', 'authentic', 'factual', 'accurate'];

        $fakeScore = count(array_filter($fakeKeywords, fn($w) => strpos($response, $w) !== false));
        $realScore = count(array_filter($realKeywords, fn($w) => strpos($response, $w) !== false));

        return $fakeScore >= $realScore;
    }

    /** Helper: Confidence extraction **/
    private function extractConfidence($response) {
        $lower = strtolower($response);
        if (preg_match('/accuracy percentage:\s*(\d+)%/i', $response, $m)) return intval($m[1]);
        if (strpos($lower, 'uncertain') !== false) return 0;
        if (strpos($lower, 'likely real') !== false) return 100;
        if (strpos($lower, 'likely fake') !== false) return 100;
        if (strpos($lower, 'likely mixed') !== false) return 50;
        return 100;
    }

    /** Helper: Explanation formatter **/
    private function formatExplanation($response, $type) {
        $typeLabels = ['text' => 'text', 'image' => 'image', 'audio' => 'audio'];
        $label = $typeLabels[$type] ?? 'content';
        $resp = trim($response);
        if (strlen($resp) < 50) {
            $resp = "Our AI analysis of the {$label} indicates: {$resp}. This assessment is based on content patterns, source credibility, and factual consistency.";
        }
        return $resp;
    }

    private function determineClassification($response) {
        $response = strtolower($response);

        if (strpos($response, 'mixed') !== false) return 'mixed';
        if (strpos($response, 'unverified') !== false) return 'unverified';
        if (strpos($response, 'likely real') !== false) return 'real';
        if (strpos($response, 'likely fake') !== false) return 'fake';
        if (strpos($response, 'true') !== false) return 'real';


        $fakeKeywords = ['fake', 'false', 'misleading', 'fabricated'];
        $realKeywords = ['true', 'verified', 'authentic', 'accurate'];

        $fakeScore = count(array_filter($fakeKeywords, fn($w) => strpos($response, $w) !== false));
        $realScore = count(array_filter($realKeywords, fn($w) => strpos($response, $w) !== false));

        if ($fakeScore > 0 && $realScore > 0) return 'mixed';
        if ($fakeScore > $realScore) return 'fake';
        if ($realScore > $fakeScore) return 'real';

        return 'unverified';
    }

    private function getDefaultSources() {
        return [
            ['name' => 'Reuters Fact Check', 'url' => 'https://www.reuters.com/fact-check/'],
            ['name' => 'AP Fact Check', 'url' => 'https://apnews.com/hub/ap-fact-check'],
            ['name' => 'Snopes', 'url' => 'https://www.snopes.com/'],
            ['name' => 'PolitiFact', 'url' => 'https://www.politifact.com/'],
            ['name' => 'BBC Reality Check', 'url' => 'https://www.bbc.com/news/reality_check']
        ];
    }

    private function getCybersecurityTips($type) {
        $tips = [
            'text' => [
                'Verify news from multiple trusted sources before sharing.',
                'Check publication date and author credentials.',
                'Watch for emotional or manipulative language.',
                'Cross-reference claims with fact-checking sites.',
                'Be skeptical of overly sensational headlines.'
            ],
            'image' => [
                'Use reverse image search to find the original source.',
                'Check metadata for signs of manipulation.',
                'Verify date, location, and context of the image.',
                'Look for lighting or shadow inconsistencies.',
                'Be wary of emotionally charged captions.'
            ],
            'audio' => [
                'Verify the speaker’s identity and source credibility.',
                'Be cautious of deepfake or altered audio clips.',
                'Cross-check spoken claims with reputable sources.',
                'Check for background noise or edit inconsistencies.'
            ]
        ];
        return $tips[$type] ?? $tips['text'];
    }
}

// End of class

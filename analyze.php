<?php
session_start();
require_once 'services/ApiService.php';

// Restore result after redirect
$result = $_SESSION['result'] ?? null;
$fileType = $_SESSION['fileType'] ?? null;
$content = $_SESSION['content'] ?? null;
$type = $_SESSION['type'] ?? null;

$uploaded_image_path = $_SESSION['last_uploaded_image'] ?? null;
unset($_SESSION['result'], $_SESSION['fileType'], $_SESSION['content'], $_SESSION['type']);

// Initialize API service
$apiService = new ApiService();

// Initialize history if result is set
if ($result && isset($type, $content)) {

    // -----------------------------------------
    // ✔ 1. Format the values for saving
    // -----------------------------------------
    $date       = date('Y-m-d H:i:s');
    $resultText = $result['is_fake'] ? 'Fake' : 'Legit';
    $confidence = $result['confidence'] ?? null;
    $summary    = $result['explanation'] ?? null;
    $contentClean = $type === 'image'
        ? '[Image Analysis]'
        : substr($content, 0, 100) . (strlen($content) > 100 ? '...' : '');

    // -----------------------------------------
    // ✔ 2. Save to database if logged in
    // -----------------------------------------
    if (isset($_SESSION['users_id'])) {
        $userId = $_SESSION['users_id'];

        $stmt = $conn->prepare("
            INSERT INTO analysis_history (user_id, date, type, content, result, confidence, summary, image_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "issssdss",
            $userId,
            $date,
            $type,
            $contentClean,
            $resultText,
            $confidence,
            $summary,
            $uploaded_image_path
        );

        $stmt->execute();
    }

    // -----------------------------------------
    // ✔ 3. (Optional) Keep storing in session
    // -----------------------------------------
    if (!isset($_SESSION['history'])) {
        $_SESSION['history'] = [];
    }

    $_SESSION['history'][] = [
        'date'       => $date,
        'type'       => $type,
        'content'    => $contentClean,
        'result'     => $resultText,
        'confidence' => $confidence,
        'explanation'=> $summary,
        'image_path' => $type === 'image' ? ($uploaded_image_path ?? null) : null
    ];

    // Clear last uploaded image for text or audio analysis
    if ($type !== 'image') {
        unset($_SESSION['last_uploaded_image']);
        $uploaded_image_path = null;
    }
}


// Handle form submission
$active_tab = $_POST['active_tab'] ?? 'text'; 
$error_message = null;
$uploaded_image_path = $_SESSION['last_uploaded_image'] ?? null;

if ($_POST) {
    $type = $_POST['type'] ?? '';

    try {
        switch ($type) {
            case 'text':
                $content = $_POST['content'] ?? '';
                if (empty(trim($content))) {
                    throw new Exception('Please enter some text or URL to analyze.');
                }

                // Check if input is a valid URL
                if (filter_var($content, FILTER_VALIDATE_URL)) {
                    $html = @file_get_contents($content);
                    if ($html === false) {
                        throw new Exception('Failed to fetch content from the provided URL.');
                    }

                    libxml_use_internal_errors(true);
                    $doc = new DOMDocument();
                    $doc->loadHTML($html);
                    libxml_clear_errors();

                    $xpath = new DOMXPath($doc);
                    $nodes = $xpath->query("//p");
                    $extractedText = '';
                    foreach ($nodes as $node) {
                        $extractedText .= ' ' . trim($node->textContent);
                    }

                    $content = trim($extractedText);
                    if (empty($content)) {
                        throw new Exception('No article text found at the provided URL.');
                    }
                }

                $result = $apiService->analyzeText($content);
                unset($_SESSION['last_uploaded_image']); // clear image
                break;

        
    case 'image':
    try {
        // Check file upload
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Please upload a valid image file.');
        }

        // Ensure upload directory exists
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $uploaded_image_path = $upload_dir . uniqid('img_') . '.' . $file_extension;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploaded_image_path)) {
            throw new Exception('Failed to save uploaded image.');
        }

        // Store path in session
        $_SESSION['last_uploaded_image'] = $uploaded_image_path;

        // OCR using Tesseract
        $tesseractPath = "C:\\Program Files\\Tesseract-OCR\\tesseract.exe";
        $command = "\"$tesseractPath\" " . escapeshellarg($uploaded_image_path) . " stdout 2>&1";
        $ocrText = shell_exec($command);
        $content = trim($ocrText);

        if (empty($content)) {
            throw new Exception('No text detected in image.');
        }

        // Send extracted text to API service
        $result = $apiService->analyzeText($content);

        // --- CLEAN USER INSTRUCTION RESULT ---
        $aiOutput = $result['explanation'] ?? '';
        $instructionResult = '';
        $analysisResult = $aiOutput;

        if (strpos($aiOutput, 'User Instruction Result:') !== false) {
            $parts = explode('News Analysis Result:', $aiOutput);
            $instructionResult = trim(str_replace('User Instruction Result:', '', $parts[0]));
            $analysisResult = isset($parts[1]) ? trim($parts[1]) : '';

            // Remove asterisks, hyphens, and empty lines
            $lines = explode("\n", $instructionResult);
            $lines = array_filter(array_map(fn($line) => trim(str_replace(['*','-'],'',$line)), $lines));
            $instructionResult = implode("\n", $lines);
        }

        // Fallback: if user provided instruction but AI output empty
        if (empty($instructionResult) && !empty($_POST['instruction'])) {
            $instructionResult = "Instruction executed: " . htmlspecialchars($_POST['instruction']);
        }

    } catch (Exception $e) {
        $result = [
            'error' => true,
            'message' => 'Failed to analyze image: ' . $e->getMessage()
        ];
        $instructionResult = '';
        $analysisResult = '';
    }
    break;


            case 'audio':
                if (!isset($_FILES['audio']) || $_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('Please upload a valid audio file.');
                }

                $result = $apiService->analyzeAudio($_FILES['audio']);
                $content = 'Audio: ' . $_FILES['audio']['name'];
                unset($_SESSION['last_uploaded_image']); // clear image
                break;

            default:
                throw new Exception('Invalid analysis type.');
        }

        if (isset($result['error']) && $result['error']) {
            $error_message = $result['message'];
            $result = null;
        } else {
            // Save result for redirect
            $_SESSION['result'] = $result;
            $_SESSION['type'] = $type;
            $_SESSION['content'] = $content;

            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }

    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Check API health status
$healthStatus = $apiService->checkHealth();
?>


<?php


$active_tab = $_POST['active_tab'] ?? 'text'; // Default to text if not set

// ✅ Also keep the last uploaded image if available
$uploaded_image_path = $_SESSION['last_uploaded_image'] ?? null;

// Handle image upload when type = image
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['type'] ?? '') === 'image') {
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = basename($_FILES["image"]["name"]);
        $target_file = $upload_dir . time() . "_" . $file_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $_SESSION['last_uploaded_image'] = $target_file;
            $uploaded_image_path = $target_file;
        }
    }
}
?>

<?php
require_once 'db.php'; // Ensure this is the correct path to your db file

$ip = $_SERVER['REMOTE_ADDR'];
$visitedAt = date('Y-m-d H:i:s');
$userAgent = basename($_SERVER['HTTP_USER_AGENT']) ?? 'Unknown';
$pageUrl = basename($_SERVER['REQUEST_URI']) ?? 'Unknown';
$email = $_SESSION['email'] ?? 'Guest'; // Adjust if your session uses a different key

$stmt = $pdo->prepare("INSERT INTO visitor_logs (ip_address, visited_at, user_agent, page_url, email) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$ip, $visitedAt, $userAgent, $pageUrl, $email]);
?>
<?php
// Handle "Continue without signing in"
if (isset($_GET['guest']) && $_GET['guest'] == 1) {
  $_SESSION['guest'] = true;
}

// Handle "Sign In" clicked while guest
if (isset($_GET['remove_guest']) && $_GET['remove_guest'] == 1) {
  unset($_SESSION['guest']);
  header("Location: index.php"); // Clean URL
  exit;
}

$is_logged_in = isset($_SESSION['user_id']);
$is_guest = isset($_SESSION['guest']) && $_SESSION['guest'] === true;
$show_login = !$is_logged_in && !$is_guest;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyze Content - VeriFact</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        .result-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
            font-size: 14px;
        }

        .action-btn:hover {
            background: var(--primary-blue);
            border-color: var(--primary-blue);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .action-btn.speaking {
            background: var(--gradient-primary);
            color: white;
            animation: pulse 1s infinite;
        }

        .action-btn i {
            font-size: 16px;
        }
        .navbar,
.menu-toggle {
  display: none;
}

.nav-menu {
  display: flex;
  gap: 20px;
}

/* Mobile view */
@media (max-width: 768px) {
  .menu-toggle {
    display: block;
    background: none;
    border: none;
    font-size: 28px;
    color: white;
  }

  .navbar {
    display: block;
    position: absolute;
    top: 70px;
    right: 20px;
    background-color: #111;
    padding: 10px;
    border-radius: 8px;
    z-index: 999;
  }

  .nav-links {
    display: none;
    flex-direction: column;
    list-style: none;
    margin: 0;
    padding: 0;
  }

  .nav-links.show {
    display: flex;
  }

  .nav-links li a {
    color: white;
    padding: 10px;
    text-decoration: none;
  }

  .nav-menu {
    display: none; /* hide desktop nav on mobile */
  }
}
</style>
</head>
<body>
<header class="header">
  <div class="container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; padding: 16px;">
    
    <!-- Logo -->
    <div class="nav-brand">
      <a href="index.php" class="logo-link" style="display: flex; align-items: center; text-decoration: none; color: inherit;">
        <div class="logo-icon" style="color: white; font-size: 24px; margin-right: 8px;">
          <i class="fas fa-shield-alt"></i>
        </div>
        <h1 class="logo" style="margin: 0; font-size: 24px;">VeriFact</h1>
      </a>
    </div>

    <!-- Mobile Hamburger -->
    <button class="menu-toggle" onclick="toggleMenu()">☰</button>

<!-- Mobile Nav -->
<nav class="navbar">
  <ul class="nav-links" id="navMenu">
    <li><a href="index.php">Home</a></li>
    <li><a href="analyze.php">Fact-Checker</a></li>
    <li><a href="cyberSecurity.php">CyberSecurity</a></li>
    <li><a href="#contact">Contact</a></li>
  </ul>
</nav>

<!-- Desktop Nav -->
<nav class="nav-menu">
  <a href="index.php" class="nav-link ">
    <i class="fas fa-home"></i> Home
  </a>

  <a href="analyze.php" class="nav-link active">
    <i class="fas fa-search"></i> Fact-Checker
  </a>

  <a href="cyberSecurity.php" class="nav-link">
    <i class="fas fa-shield-alt"></i> CyberSecurity
  </a>

  <?php if (!empty($_SESSION['role']) && $_SESSION['role'] == 1): ?>
    <a href="dashboard.php" class="nav-link ">
      <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
  <?php endif; ?>

  <a href="#contact" class="nav-link">
    <i class="fas fa-envelope"></i> Contact
  </a>

  <?php if (!$is_logged_in): ?>
    <a href="index.php?remove_guest=1" class="nav-link" style="margin-left:auto; font-weight: bold;">Sign In</a>
  <?php else: ?>
    <!-- Logged-in user -->
    <div class="profile-dropdown" style="position: relative; margin-left: auto;">
      <div class="profile-icon" onclick="toggleDropdown()" style="
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #6c63ff;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        cursor: pointer;
        font-size: 16px;
      ">
        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
      </div>
      <div id="dropdownMenu" class="dropdown-menu" style="
        display: none;
        position: absolute;
        right: 0;
        top: 50px;
        background-color: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        min-width: 200px;
        z-index: 999;
      ">
        <!-- User email (non-clickable) -->
        <div style="
          padding: 12px 16px;
          font-size: 14px;
          color: #555;
          border-bottom: 1px solid #eee;
        ">
          <?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'No email available'; ?>
        </div>
        <a href="settings.php" class="dropdown-item" style="
          display: block;
          padding: 12px 16px;
          color: #333;
          text-decoration: none;
          border-bottom: 1px solid #eee;
        ">Settings</a>
        <a href="logout.php" class="dropdown-item" style="
          display: block;
          padding: 12px 16px;
          color: #e74c3c;
          text-decoration: none;
        ">Logout</a>
      </div>
    </div>
  <?php endif; ?>
</nav>
  </header>

    <main class="analyze-page">
        <div class="container">
            <div class="page-header">
                <h1>Verify Content</h1>
                <p>Check the truth behind text, images, or audio with our intelligent AI verification system</p>
            </div>

            <?php if ($error_message): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

   
<div class="analyze-container">
    <div class="analyzer-tabs">
        <button type="button" class="tab-btn <?= $active_tab === 'text' ? 'active' : '' ?>" onclick="switchTab('text')">
            <i class="fas fa-file-text"></i>
            Text Checker
        </button>
        <button type="button" class="tab-btn <?= $active_tab === 'image' ? 'active' : '' ?>" onclick="switchTab('image')">
            <i class="fas fa-image"></i>
            Image Checker
        </button>
        <button type="button" class="tab-btn <?= $active_tab === 'history' ? 'active' : '' ?>" onclick="switchTab('history')">
            <i class="fas fa-history"></i>
            History
        </button>
    </div>
</div>

<!-- Text Checker Tab -->
<div id="text-tab" class="tab-content active">
    <form method="POST" class="analyzer-form">
        <input type="hidden" name="type" value="text">
        <div class="input-group">
            <label for="text-content">
                <i class="fas fa-edit"></i> 
                Paste news article, headline, or URL
            </label>
            <div class="textarea-container">
                <textarea 
                    id="text-content" 
                    name="content" 
                    placeholder="Paste the news article, headline, URL, or social media post you want to verify..."
                    rows="8"
                    required
                ><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                <button type="button" class="voice-btn" onclick="startVoiceInput()" title="Voice Input">
                    <i class="fas fa-microphone"></i>
                </button>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-search"></i>
            Analyze Text
        </button>
    </form>
</div>

<!-- Image Checker Tab -->
<div id="image-tab" class="tab-content">
    <form method="POST" enctype="multipart/form-data" class="analyzer-form">
        <!-- Important: This tells PHP that this request is for image analysis -->
        <input type="hidden" name="type" value="image">
        
        <div class="input-group">
            <label>
                <i class="fas fa-images"></i> 
                Upload image-based news content
            </label>

            <div class="file-upload-area" onclick="document.getElementById('image-upload').click()">
                <div class="upload-content" id="upload-text">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <h3>Drag & drop or click to upload</h3>
                    <p>Support for JPG, PNG, WebP files up to 10MB</p>
                </div>
                <input type="file" id="image-upload" name="image" accept="image/*" style="display: none;" required>
            </div>

            <!-- Image Preview -->
            <div id="image-preview" style="display: none; text-align: center; margin-top: 15px;">
                <img id="preview-img" src="" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
            </div>
        </div>

        <!-- User Instruction -->
<div class="input-group" style="margin-top: 20px;">
    <label>
        <i class="fas fa-edit"></i> Optional Instructions for the AI
    </label>

    <textarea name="instruction" 
        placeholder="Example: Extract the text only and summarize it... 
Or: Translate the detected text to English...
Or: Check if the image was modified..."
        style="
            width: 100%;
            height: 140px;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            resize: vertical;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        "></textarea>

    <small style="color: #6b7280; margin-top: 4px; display: block;">
        This is optional – tell the AI what to do with the uploaded image.
    </small>
</div>

        <button type="submit" class="btn btn-primary btn-lg" style="margin-top: 10px;">
            <i class="fas fa-image"></i>
            Analyze Image
        </button>
    </form>
</div>

<script>
document.getElementById('image-upload').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').style.display = 'block';
            document.getElementById('upload-text').style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
});
</script>




<script>
let mediaRecorder;
let recordedChunks = [];
const recordBtn = document.getElementById("record-btn");
const recordStatus = document.getElementById("record-status");
const audioForm = document.getElementById("audio-form");

recordBtn.addEventListener("click", async () => {
    if (!mediaRecorder || mediaRecorder.state === "inactive") {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            recordedChunks = [];

            mediaRecorder.ondataavailable = (event) => {
                if (event.data.size > 0) recordedChunks.push(event.data);
            };

            mediaRecorder.onstop = () => {
                const audioBlob = new Blob(recordedChunks, { type: "audio/webm" });
                const file = new File([audioBlob], "recording.webm", { type: "audio/webm" });

                // Attach to form as if uploaded
                const fileInput = document.getElementById("audio-upload");
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;

                recordStatus.textContent = "Recording saved! You can now submit.";
                recordBtn.innerHTML = '<i class="fas fa-microphone"></i> Record Again';
            };

            mediaRecorder.start();
            recordStatus.textContent = "🎙 Recording... click again to stop.";
            recordBtn.innerHTML = '<i class="fas fa-stop"></i> Stop';

        } catch (error) {
            console.error("Microphone access denied:", error);
            recordStatus.textContent = "Microphone access denied.";
        }
    } else {
        mediaRecorder.stop();
        recordStatus.textContent = "Processing recording...";
    }
});
</script>

<?php $isLoggedIn = isset($_SESSION['user_id']); ?>
<!-- History Tab -->
<?php if ($isLoggedIn): ?>
    <div id="history-tab" class="tab-content">
        <div class="history-container">
            <div class="history-header">
                <h3><i class="fas fa-history"></i> Analysis History</h3>
                <button class="btn btn-outline" onclick="clearHistory()">
                    <i class="fas fa-trash"></i>
                    Clear History
                </button>
            </div>
            <?php if (empty($_SESSION['history'])): ?>
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <p>No analysis history yet. Start by checking some content!</p>
                </div>
            <?php else: ?>
                <div class="history-table">
                    <table>
                        <thead>
                            <tr>
                                <th><i class="fas fa-calendar"></i> Date</th>
                                <th><i class="fas fa-tag"></i> Type</th>
                                <th><i class="fas fa-file-alt"></i> Content</th>
                                <th><i class="fas fa-check-circle"></i> Result</th>
                                <th><i class="fas fa-percentage"></i> Confidence</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_reverse($_SESSION['history']) as $index => $item): ?>
                                    <tr class="history-row"
                                        onclick='showSummaryModal(<?php echo htmlspecialchars(json_encode($item)); ?>)'>
                                        <td><?php echo date('M j, Y H:i', strtotime($item['date'])); ?></td>
                                        <td><span class="type-badge <?php echo $item['type'] ?? 'unknown'; ?>"><?php echo ucfirst($item['type'] ?? 'unknown'); ?></span></td>
                                        <td class="content-preview"><?php echo htmlspecialchars($item['content'] ?? ''); ?></td>
                                        <td><span class="result-badge <?php echo strtolower($item['result']); ?>"><?php echo $item['result']; ?></span></td>
                                        <td><?php echo $item['confidence']; ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                   </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div id="history-tab" class="tab-content disabled">
        <div class="empty-state">
            <i class="fas fa-user-lock" style="font-size: 3rem; color: #999;"></i>
            <h3>You're not signed in</h3>
            <p>Please sign in to view your analysis history.</p>
            <a href="index.php?remove_guest=1" class="btn btn-primary mt-2">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </a>
        </div>
    </div>
<?php endif; ?>


<style>
.history-table tr.history-row {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.history-table tr.history-row:hover {
    background-color: #94a5f3ff;
}
</style>

<script>
function showSummaryModal(item) {
    // Create modal content dynamically
    let modalHtml = `
        <div class="modal-overlay" onclick="closeModal()"></div>
        <div class="modal">
            <div class="modal-header">
                <h3><i class="fas fa-info-circle"></i> Analysis Details</h3>
                <button onclick="closeModal()" class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <p><strong>Date:</strong> ${item.date}</p>
                <p><strong>Type:</strong> ${item.type}</p>
                <p><strong>Result:</strong> ${item.result}</p>
                <p><strong>Confidence:</strong> ${item.confidence}%</p>
                <p><strong>Explanation:</strong> ${item.explanation ?? 'No explanation available.'}</p>
    `;

    // If it's an image analysis and has a saved image path
    if (item.type === 'image' && item.image_path) {
        modalHtml += `
            <div style="margin-top:15px; text-align:center;">
                <img src="${item.image_path}" alt="Uploaded Image" style="max-width:100%; border-radius:8px;">
            </div>
        `;
    }

    modalHtml += `
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeModal() {
    document.querySelectorAll('.modal, .modal-overlay').forEach(el => el.remove());
}
</script>

<?php
// Clear image session when analyzing text
if (isset($_POST['type']) && $_POST['type'] === 'text') {
    unset($_SESSION['last_uploaded_image']);
}

// Handle image upload
if (isset($_POST['type']) && $_POST['type'] === 'image' && isset($_FILES['image'])) {
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_name = time() . '_' . basename($_FILES['image']['name']);
    $saved_image_path = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $saved_image_path)) {
        $_SESSION['last_uploaded_image'] = $saved_image_path;
        // Here you call your AI image analysis logic and generate $result
    } else {
        $result = ['error' => 'Failed to upload image. Please try again.'];
    }
}
?>



<?php if ($result && !isset($result['error'])): ?>
<!-- RESULTS MODAL -->
<div id="resultsModal" class="modal-overlay" style="
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.35);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
">

    <div class="modal-content" id="modalContent" style="
        background: #ffffff; /* PURE WHITE */
        border-radius: 20px;
        width: 95%;
        max-width: 900px;
        max-height: 90%;
        overflow-y: auto;

        /* CLEAN MODERN CARD LOOK */
        box-shadow: 
            0 4px 12px rgba(0,0,0,0.08),
            0 8px 24px rgba(0,0,0,0.12);

        padding: 28px 34px;
        position: relative;
        animation: fadeInScale 0.25s ease;
        font-family: 'Inter', sans-serif;
    ">

        <!-- Close Button -->
        <button onclick="closeResultsModal()" style="
            position: absolute;
            top: 14px;
            right: 14px;
            background: #f3f4f6;
            border: none;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            color: #374151;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .2s ease;
        " 
        onmouseover="this.style.background='#e5e7eb'"
        onmouseout="this.style.background='#f3f4f6'">
            <i class="fas fa-times"></i>
        </button>

        <!-- Header -->
        <div class="result-header" style="
            margin-bottom: 1.4rem; 
            display: flex; 
            align-items: center; 
            gap: 10px;
        ">
            <h2 style="
                margin: 0;
                font-size: 1.6rem;
                font-weight: 700;
                color: #111827;
                display: flex;
                align-items: center;
                gap: 10px;
            ">
                <i class="fas fa-brain" style="color:#3b82f6;"></i>
                Analysis Complete
            </h2>
        </div>


        <!-- IMAGE PREVIEW -->
        <?php if (!empty($_SESSION['last_uploaded_image'])): ?>
        <div style="text-align:center; margin-bottom:20px;">
            <h3>Uploaded Image</h3>
            <img src="<?= htmlspecialchars($_SESSION['last_uploaded_image']) ?>" 
                alt="Uploaded Image" 
                style="max-width: 300px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor:pointer;"
                onclick="openImageModal(this)">
        </div>
        <?php endif; ?>

<?php
$confidence = isset($result['confidence']) ? $result['confidence'] : 0;
$label = isset($result['label']) ? strtolower($result['label']) : '';
$isFake = !empty($result['is_fake']); 

if ($isFake) {
    $class = 'fake';
} elseif ($confidence == 50) {
    $class = 'mixed';
} elseif (strpos($label, 'uncertain') !== false || $confidence == 0) {
    $class = 'uncertain';
} else {
    $class = 'legit';
}

$radius = 54;
$circumference = 2 * M_PI * $radius;
$offset = $circumference - ($confidence / 100 * $circumference);
?>

<div class="confidence-display" style="display:flex; justify-content:center; margin: 1rem 0;">
    <div class="confidence-circle <?php echo $class; ?>">
        <svg class="progress-ring" width="120" height="120">
            <defs>
                <linearGradient id="half-red-green" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="red" />
                    <stop offset="50%" stop-color="red" />
                    <stop offset="50%" stop-color="green" />
                    <stop offset="100%" stop-color="green" />
                </linearGradient>
            </defs>

            <circle class="progress-ring-bg" cx="60" cy="60" r="<?php echo $radius; ?>"></circle>
            <circle class="progress-ring-circle"
                cx="60"
                cy="60"
                r="<?php echo $radius; ?>"
                stroke-dasharray="<?php echo $circumference; ?>"
                stroke-dashoffset="<?php echo $offset; ?>"
                stroke="<?php echo ($class === 'mixed') ? 'url(#half-red-green)' : 'currentColor'; ?>">
            </circle>
        </svg>
        <div class="confidence-text">
            <?php if ($confidence == 50): ?>
                <span class="label">Mixed</span>
            <?php elseif ($confidence == 0): ?>
                <span class="label">Uncertain</span>
            <?php else: ?>
                <span class="percentage"><?php echo $confidence; ?>%</span>
                <span class="label"><?php echo ucfirst($label); ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.confidence-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    font-weight: bold;
    text-align: center;
    color: black;
    flex-direction: column;
    background: black;
}
.confidence-circle.fake { color: red; }
.confidence-circle.legit { color: green; }
.confidence-circle.mixed { color: transparent; }
.confidence-circle.uncertain { color: gray; }
.progress-ring-bg {
    fill: transparent;
    stroke: #ddd;
    stroke-width: 8;
}
.progress-ring-circle {
    fill: transparent;
    stroke-width: 8;
    transform: rotate(-90deg);
    transform-origin: 50% 50%;
    transition: stroke-dashoffset 1s ease-out;
}
.confidence-text {
    position: absolute;
    text-align: center;
    color: #000; /* ✅ Make all text inside (percentage + label) black */
}
.percentage { 
    font-size: 1.2rem; 
    display: block; 
}
.label { 
    font-size: 0.9rem; 
    /* color removed here because .confidence-text sets it */ 
}
</style>


<?php
$aiOutput = $result['explanation'] ?? '';
$instructionResult = '';
$analysisResult = $aiOutput;

// Detect and separate instruction part
if (strpos($aiOutput, 'User Instruction Result:') !== false && strpos($aiOutput, 'News Analysis Result:') !== false) {
    $parts = explode('News Analysis Result:', $aiOutput);
    $instructionResult = trim(str_replace('User Instruction Result:', '', $parts[0]));
    $analysisResult = isset($parts[1]) ? trim($parts[1]) : '';

    // --- CLEAN INSTRUCTION RESULT ---
    $lines = explode("\n", $instructionResult);
    $lines = array_map(fn($line) => trim(str_replace('*','',$line)), $lines);
    $lines = array_filter($lines, fn($line) => !empty($line));
    $instructionResult = implode("\n", $lines);

    // --- CLEAN ANALYSIS RESULT ---
    $lines = explode("\n", $analysisResult);
    $lines = array_map(fn($line) => trim(str_replace('*','',$line)), $lines);
    $lines = array_filter($lines, fn($line) => !empty($line));
    $analysisResult = implode("\n", $lines);
}
?>


<!-- 🧭 USER INSTRUCTION RESULT (only shown if available) -->
<?php if (!empty($instructionResult)): ?>
<div class="instruction-result" style="margin-top: 20px; font-family: 'Inter', sans-serif;">
  <h3 style="
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #059669;
    font-weight: 600;
  ">
    <i class="fas fa-lightbulb"></i> User Instruction Result
  </h3>
  <div style="overflow-x: hidden; white-space: pre-wrap; word-wrap: break-word; line-height: 1.6; color: #064e3b;">
    <?php 
        // Render HTML links properly without escaping
        echo $instructionResult; 
    ?>
  </div>
</div>
<?php endif; ?>


<!-- 🧠 AI ANALYSIS RESULT -->
<div class="result-explanation" id="aiAnalysis" style="margin-top: 25px; font-family: 'Inter', 'Roboto', sans-serif;">
  <h3 style="
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #1e40af;
    font-weight: 600;
  ">
    <i class="fas fa-brain"></i> AI Analysis
  </h3>
  <div id="analysisText" style="
  white-space: pre-wrap;
  word-wrap: break-word;
  overflow-x: hidden;
  background: #f9fafb;
  padding: 18px;
  border-radius: 10px;
  max-height: 400px;
  overflow-y: auto;
  font-size: 1rem;
  line-height: 1.6;
  color: #111827;
  border-left: 4px solid #2563eb;
  font-family: 'Inter', 'Roboto Mono', monospace;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
">
  <?php echo $analysisResult; ?>
  </div>
</div>

<!-- 🔈 SPEAK BUTTON -->
<?php if(!empty($analysisResult)): ?>
<div style="margin-top: 10px;">
  <button id="speakBtn" onclick="speakResult()" style="
    background: #2563eb;
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background 0.3s;
  " onmouseover="this.style.background='#1e3a8a'" onmouseout="this.style.background='#2563eb'">
    <i class="fas fa-volume-up"></i> Read Aloud
  </button>
</div>
<?php endif; ?>

<!-- 🔒 CYBERSECURITY TIPS -->
<?php if (!empty($result['cybersecurity_tips'])): ?>
<div class="cybersecurity-tips" style="margin-top: 20px; font-family: 'Inter', sans-serif;">
  <button onclick="toggleTips()" class="dropdown-toggle" style="
    display: flex;
    align-items: center;
    gap: 10px;
    background: none;
    border: none;
    font-size: 1.2rem;
    color: #000000ff;
    cursor: pointer;
    font-weight: 600;
  ">
    <i class="fas fa-shield-alt" style="color: #f59e0b;"></i>
    <span>Cybersecurity Tips (<?= ucfirst($result['type'] ?? 'General') ?>)</span>
    <i id="arrowIcon" class="fas fa-chevron-down" style="transition: transform 0.3s;"></i>
  </button>

  <div id="tipsContent" style="
    margin-top: 16px;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.5s ease, opacity 0.3s ease;
    opacity: 0;
  ">
    <div class="tips-grid" style="
      display: grid;
      gap: 16px;
      background: rgba(245, 158, 11, 0.08);
      border: 1px solid #f59e0b;
      border-radius: 12px;
      padding: 24px;
    ">
      <?php foreach ($result['cybersecurity_tips'] as $index => $tip): ?>
      <div class="tip-card" style="
        display: flex;
        gap: 12px;
        padding: 16px;
        background: #fff8e1;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      ">
        <div class="tip-number" style="
          width: 26px;
          height: 26px;
          background: linear-gradient(135deg, #f59e0b, #d97706);
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 13px;
          font-weight: 700;
          color: white;
          flex-shrink: 0;
        ">
          <?php echo $index + 1; ?>
        </div>
        <div class="tip-content" style="color: #374151; font-size: 0.95rem;">
          <p style="margin: 0;"><?php echo htmlspecialchars($tip); ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ✅ TRUSTED SOURCES -->
<div class="trusted-sources" style="margin-top: 20px; font-family: 'Inter', sans-serif;">
  <h3 style="
    display: flex;
    align-items: center;
    gap: 8px;
    color: #1e40af;
    font-size: 1.4rem;
    font-weight: 700;
  ">
    <i class="fas fa-check-circle" style="color: #22c55e;"></i> Trusted Sources
  </h3>

  <div class="sources-list" style="
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 14px;
    margin-top: 16px;
  ">
    <?php foreach ($result['sources'] as $source): ?>
    <a href="<?php echo htmlspecialchars($source['url']); ?>" target="_blank" rel="noopener noreferrer" class="source-link" style="
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 14px;
      background: #e0f2fe;
      border: 1.5px solid #0284c7;
      border-radius: 10px;
      color: #000000ff;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    " onmouseover="this.style.background='#bae6fd'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='#e0f2fe'; this.style.transform='none';">
      <i class="fas fa-external-link-alt"></i>
      <span><?php echo htmlspecialchars($source['name']); ?></span>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<!-- Optional Hover Effect -->
<style>
    .source-link:hover {
        background: var(--primary-blue);
        color: white;
        transform: translateY(-2px);
    }
    .source-link:hover i {
        color: white;
    }

    
</style>

<!-- ✅ DOWNLOAD BUTTON -->
<div style="margin-top: 24px; text-align: right;">
    <button onclick="downloadResult()" style="
        background: blue;
        border: none;
        color: white;
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 1rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    ">
        <i class="fas fa-download"></i> Download Result
    </button>
</div>



<!-- IMAGE ZOOM MODAL -->
<div id="imageModal" class="image-modal" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background-color: rgba(0,0,0,0.8); justify-content:center; align-items:center;">
    <span onclick="closeImageModal()" style="position:absolute; top:20px; right:40px; font-size:32px; color:#fff; cursor:pointer;">&times;</span>
    <img id="modalImage" style="max-width:90%; max-height:90%; border-radius: 12px;" />
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function closeResultsModal() {
        document.getElementById('resultsModal').style.display = 'none';
    }
    function toggleTips() {
        const content = document.getElementById("tipsContent");
        const arrow = document.getElementById("arrowIcon");
        if (content.style.maxHeight && content.style.maxHeight !== "0px") {
            content.style.maxHeight = "0px";
            content.style.opacity = "0";
            arrow.style.transform = "rotate(0deg)";
        } else {
            content.style.maxHeight = content.scrollHeight + "px";
            content.style.opacity = "1";
            arrow.style.transform = "rotate(180deg)";
        }
    }
    function openImageModal(img) {
        document.getElementById("modalImage").src = img.src;
        document.getElementById("imageModal").style.display = "flex";
    }
    function closeImageModal() {
        document.getElementById("imageModal").style.display = "none";
    }
    document.getElementById("imageModal").addEventListener("click", function(e) {
        if (e.target.id === "imageModal") {
            closeImageModal();
        }
    });
</script>

<script>
    function downloadResult() {
        const modal = document.getElementById("modalContent");
        const options = {
            margin: 0.5,
            filename: 'VeriFact_Analysis_Result.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 3 },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
        };
        html2pdf().set(options).from(modal).save();
    }
</script>

<style>
@keyframes fadeInScale {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}
</style>
<?php endif; ?>
</div>
</main>

    <footer id="contact" class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>VeriFact</h3>
                    <p>Empowering truth through AI-powered verification. Join the fight against fake news with cutting-edge technology.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Product</h4>
                    <ul>
                        <li><a href="analyze.php">Text Checker</a></li>
                        <li><a href="analyze.php">Image Verification</a></li>
                        <li><a href="analyze.php">Audio Analysis</a></li>
                        <li><a href="#">API Access</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Press</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Cookie Policy</a></li>
                        <li><a href="#">GDPR</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 VeriFact. All rights reserved. Fighting fake news with AI.</p>
            </div>
        </div>
    </footer>


    <script src="script.js"></script>
    <script>
    // Initialize confidence circle animation
    <?php if ($result && !isset($result['error'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            animateConfidenceCircle(<?php echo $result['confidence']; ?>);
        });
    <?php endif; ?>

    // Copy result function
    function copyResult() {
        const explanationEl = document.querySelector('.explanation-content');
        const percentageEl = document.querySelector('.percentage');
        const labelEl = document.querySelector('.label');

        const explanation = explanationEl ? explanationEl.innerText : 'No analysis available';
        const confidence = percentageEl ? percentageEl.innerText : 'N/A';
        const label = labelEl ? labelEl.innerText : 'Unknown';

        const textToCopy = `VeriFact Analysis Result\n\nConfidence: ${confidence} ${label}\n\nAnalysis:\n${explanation}`;

        navigator.clipboard.writeText(textToCopy).then(() => {
            const copyBtn = document.querySelector('.copy-btn');
            if (copyBtn) {
                const originalIcon = copyBtn.innerHTML;
                copyBtn.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => {
                    copyBtn.innerHTML = originalIcon;
                }, 2000);
            }
        });
    }
</script>


<script>
  function toggleMenu() {
    document.getElementById("navMenu").classList.toggle("show");
  }
</script>
<script>
function clearHistory() {
    fetch('clear_history.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload the page to reflect the cleared history
        }
    });
}
</script>

<script>
function toggleDropdown() {
  const menu = document.getElementById('dropdownMenu');
  menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
}

// Optional: close dropdown if user clicks outside
window.addEventListener('click', function(e) {
  const menu = document.getElementById('dropdownMenu');
  const icon = document.querySelector('.profile-icon');
  if (!icon.contains(e.target) && !menu.contains(e.target)) {
    menu.style.display = 'none';
  }
});
</script>
<script>
function speakResult() {
    const textElement = document.getElementById('analysisText');
    if (!textElement) return;

    const text = textElement.innerText.trim();
    if (!text) return;

    // Stop any previous speech
    if (window.speechSynthesis.speaking) {
        window.speechSynthesis.cancel();
    }

    const utterance = new SpeechSynthesisUtterance(text);
    utterance.rate = 1;      // normal speed
    utterance.pitch = 1;     // normal pitch
    utterance.lang = 'en-US'; // change if needed

    window.speechSynthesis.speak(utterance);
}

// Optional: stop speaking when modal closes
function closeResultsModal() {
    if (window.speechSynthesis.speaking) {
        window.speechSynthesis.cancel();
    }
    document.getElementById('resultsModal').style.display = 'none';
}

// Cybersecurity tips toggle
function toggleTips() {
    const tips = document.getElementById('tipsContent');
    const arrow = document.getElementById('arrowIcon');
    if (!tips) return;

    if (tips.style.maxHeight && tips.style.maxHeight !== '0px') {
        tips.style.maxHeight = '0';
        tips.style.opacity = '0';
        arrow.style.transform = 'rotate(0deg)';
    } else {
        tips.style.maxHeight = tips.scrollHeight + 'px';
        tips.style.opacity = '1';
        arrow.style.transform = 'rotate(180deg)';
    }
}

// Optional: click outside modal to close
window.addEventListener('click', function(e) {
    const modal = document.getElementById('resultsModal');
    if (modal && e.target === modal) {
        closeResultsModal();
    }
});
</script>



</body>
</html>
<?php
session_start();
include 'db.php';


if (isset($_GET['guest']) && $_GET['guest'] == 1) {
  $_SESSION['guest'] = true;
}


if (isset($_GET['remove_guest']) && $_GET['remove_guest'] == 1) {
  unset($_SESSION['guest']);
  header("Location: index.php"); 
  exit;
}

$is_logged_in = isset($_SESSION['user_id']);
$is_guest = isset($_SESSION['guest']) && $_SESSION['guest'] === true;
$show_login = !$is_logged_in && !$is_guest;
?>
<?php
require_once 'db.php'; 

$ip = $_SERVER['REMOTE_ADDR'];
$visitedAt = date('Y-m-d H:i:s');
$userAgent = basename($_SERVER['HTTP_USER_AGENT']) ?? 'Unknown';
$pageUrl = basename($_SERVER['REQUEST_URI']) ?? 'Unknown';
$email = $_SESSION['email'] ?? 'Guest'; 

$stmt = $pdo->prepare("INSERT INTO visitor_logs (ip_address, visited_at, user_agent, page_url, email) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$ip, $visitedAt, $userAgent, $pageUrl, $email]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VeriFact - AI-Powered Fake News Detection</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    .section {
      margin: 80px auto;
      padding: 0 16px;
    }
    .section-title {
      font-size: 32px;
      margin-bottom: 24px;
      text-align: center;
      background: var(--gradient-text);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 24px;
    }
    .feature-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      padding: 24px;
      border-radius: var(--border-radius);
      text-align: center;
    }
    .feature-card img {
      width: 100%;
      border-radius: 12px;
      margin-bottom: 12px;
    }
     .custom-popup {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    padding: 24px;
    border-radius: var(--border-radius);
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
    color: var(--text-primary);
    z-index: 2000;
    text-align: center;
    max-width: 300px;
    display: none;
  }
  .popup-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    z-index: 1999;
    display: none;
  }
  .drag-label {
  background:rgb(181, 135, 219);
  border: 1px solid #ccc;
  padding: 8px 16px;
  border-radius: 10px;
  cursor: grab;
  font-size: 16px;
}
.drag-label:active {
  cursor: grabbing;
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

.active-tab {
    font-size: 1.1rem;
    padding: 12px 20px;
    background-color: #6c63ff;
    color: white !important;
    border-color: #6c63ff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transform: scale(1.05);
    transition: all 0.2s ease;
  }

  .small-tab {
    font-size: 0.85rem;
    padding: 8px 14px;
    opacity: 0.7;
    transition: all 0.2s ease;
  }

  .small-tab:hover {
    opacity: 1;
    transform: scale(1.02);
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
  <a href="index.php" class="nav-link">
    <i class="fas fa-home"></i> Home
  </a>

  <a href="analyze.php" class="nav-link">
    <i class="fas fa-search"></i> Fact-Checker
  </a>

  <a href="cyberSecurity.php" class="nav-link">
    <i class="fas fa-shield-alt active"></i> CyberSecurity
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

<div id="popupOverlay" class="popup-overlay" onclick="closePopup()"></div>
<div id="customPopup" class="custom-popup"></div>

<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="cyber-tabs-wrapper"
     style="
       position: fixed;
       top: 100px; /* 👈 adjust this to match your navbar height */
       left: 0;
       width: 100%;
       padding: 10px 0;
       z-index: 999; /* slightly lower than navbar if navbar has higher z-index */
       display: flex;
       justify-content: center;
       align-items: center;
       gap: 12px;
       flex-wrap: wrap;
     ">
    <!-- Spotting Phishing Emails -->
    <a href="spotting-phishing.php"
       class="btn <?= $currentPage === 'spotting-phishing.php' ? 'btn-primary active-tab' : 'btn-outline small-tab' ?>"
       style="display: flex; align-items: center; gap: 8px;">
      <i class="fas fa-envelope-open-text"></i>
      <span>Spotting Phishing Emails</span>
    </a>

    <!-- Recognizing Deepfakes -->
    <a href="recognizing-deepfakes.php"
       class="btn <?= $currentPage === 'recognizing-deepfakes.php' ? 'btn-primary active-tab' : 'btn-outline small-tab' ?>"
       style="display: flex; align-items: center; gap: 8px;">
      <i class="fas fa-user-secret"></i>
      <span>Recognizing Deepfakes</span>
    </a>

    <!-- Securing Online Presence -->
    <a href="online-presence.php"
       class="btn <?= $currentPage === 'online-presence.php' ? 'btn-primary active-tab' : 'btn-outline small-tab' ?>"
       style="display: flex; align-items: center; gap: 8px;">
      <i class="fas fa-shield-alt"></i>
      <span>Securing Online Presence</span>
    </a>
</div>

<!-- Add spacing so page content doesn't overlap -->
<div style="margin-top: 120px;"></div>


<?php
// Assume you have a PDO connection $pdo already set up

// Fetch videos grouped by subtype (educational, password, comparison)
function getVideosBySubtype($pdo) {
    $sql = "SELECT * FROM cybersecurity_modules1 WHERE type = 'video' ORDER BY order_index";
    $stmt = $pdo->query($sql);
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $grouped = [];
    foreach ($videos as $video) {
        $subtype = $video['subtype'] ?? 'general';
        $grouped[$subtype][] = $video;
    }
    return $grouped;
}

function getModulesByType($pdo, $type) {
    $sql = "SELECT * FROM cybersecurity_modules1 WHERE type = :type ORDER BY order_index";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':type' => $type]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$videosBySubtype = getVideosBySubtype($pdo);
$deepfakeSigns = getModulesByType($pdo, 'feature');
$timelineEvents = getModulesByType($pdo, 'timeline');
$caseStudies = getModulesByType($pdo, 'case_study');
$faqs = getModulesByType($pdo, 'faq');

$videoSubtypesTitles = [
    'educational' => '🎥 Understanding Deepfakes',
    'password' => '🔐 Password Security Videos',
    'comparison' => '📊 Real vs Deepfake: Key Differences',
    'general' => '🎥 Videos',
];
?>

<!-- === ONLY SHOW EDUCATIONAL VIDEO SECTION === -->
<?php if (!empty($videosBySubtype['educational'])): ?>
<section class="container" style="margin: 80px auto;">
  <h3 class="section-title"><?= htmlspecialchars($videoSubtypesTitles['educational']) ?></h3>
  <?php foreach ($videosBySubtype['educational'] as $video): ?>
    <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 20px; margin-bottom: 24px;">
      <iframe
        src="<?= htmlspecialchars($video['video_url']) ?>"
        title="<?= htmlspecialchars($video['title']) ?>"
        frameborder="0"
        allowfullscreen
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 20px;">
      </iframe>
    </div>
  <?php endforeach; ?>
</section>
<?php endif; ?>

<!-- === DEEPFAKE SIGNS === -->
<section class="container" style="margin: 80px auto;">
  <h2 class="section-title">Common Signs of a Deepfake Video</h2>
  <div class="features-grid">
    <?php foreach ($deepfakeSigns as $sign): ?>
      <div class="feature-card">
        <div class="feature-icon" style="font-size: 48px;">
          <i 
            class="<?= htmlspecialchars($sign['icon'] ?? 'fas fa-question-circle') ?>" 
            style="color: <?= htmlspecialchars($sign['color'] ?? '#3498db') ?>;"
          ></i>
        </div>
        <h3 class="feature-title"><?= htmlspecialchars($sign['title']) ?></h3>
        <p class="feature-description"><?= htmlspecialchars($sign['description']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>


<!-- === TIMELINE === -->
<section class="container" style="margin: 80px auto;">
  <h2 class="section-title">🕒 Evolution of Deepfake Technology</h2>
  <div class="features-grid">
    <?php foreach ($timelineEvents as $event): ?>
      <div class="feature-card">
        <h3><?= htmlspecialchars($event['title']) ?></h3>
        <p><?= htmlspecialchars($event['description']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- === DRAG & DROP QUIZ (Keep hardcoded for best control) === -->
<section class="container" style="margin: 80px auto; text-align: center;">
  <h2 class="section-title">🧪 Test Your Detection Skills</h2>
  <p style="color: var(--text-secondary); margin-bottom: 16px;">Drag the correct label to each video snapshot.</p>

  <div class="features-grid" style="display: flex; justify-content: center; gap: 40px;">
    <!-- Video A -->
    <div class="feature-card" ondrop="drop(event, 'A')" ondragover="allowDrop(event)" style="border: 2px dashed #ccc; padding: 10px; border-radius: 12px;">
      <img src="images/realest.png" alt="Video A" style="width: 100%; max-width: 250px; border-radius: 12px;">
      <div><strong>Video Snapshot A</strong></div>
      <div id="dropA" style="margin-top: 8px; min-height: 30px;"></div>
    </div>

    <!-- Video B -->
    <div class="feature-card" ondrop="drop(event, 'B')" ondragover="allowDrop(event)" style="border: 2px dashed #ccc; padding: 10px; border-radius: 12px;">
      <img src="images/fakest.png" alt="Video B" style="width: 100%; max-width: 250px; border-radius: 12px;">
      <div><strong>Video Snapshot B</strong></div>
      <div id="dropB" style="margin-top: 8px; min-height: 30px;"></div>
    </div>
  </div>

  <!-- Draggable Labels -->
  <div style="margin-top: 30px;">
    <div style="display: inline-block; margin: 0 10px;" draggable="true" ondragstart="drag(event)" id="realLabel" class="drag-label">🟢 Real</div>
    <div style="display: inline-block; margin: 0 10px;" draggable="true" ondragstart="drag(event)" id="fakeLabel" class="drag-label">🔴 Fake</div>
  </div>

  <!-- Check Button -->
  <div style="margin-top: 20px;">
    <button class="btn btn-primary" onclick="checkAnswer()">Reveal Answers</button>
  </div>

  <!-- Feedback -->
  <div id="feedback" style="margin-top: 20px; font-weight: bold;"></div>
</section>

<!-- === COMPARISON VIDEO SECTION === -->
<?php if (!empty($videosBySubtype['comparison'])): ?>
<section class="container" style="margin: 80px auto;">
  <h2 class="section-title">📊 Real vs Deepfake: Key Differences</h2>
  <div class="features-grid">
    <?php foreach ($videosBySubtype['comparison'] as $video): ?>
      <div class="feature-card">
        <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 12px;">
          <iframe 
            src="<?= htmlspecialchars($video['video_url']) ?>" 
            title="<?= htmlspecialchars($video['title']) ?>"
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen
            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border-radius: 12px;">
          </iframe>
        </div>
        <br>
        <p class="feature-description"><?= htmlspecialchars($video['description']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- === CASE STUDY === -->
<?php if (!empty($caseStudies)): ?>
<section class="container" style="margin: 80px auto;">
  <h2 class="section-title">🔍 Case Study: Deepfake Incident</h2>
  <?php foreach ($caseStudies as $case): ?>
  <div class="feature-card">
    <p><strong>Scenario:</strong> <?= htmlspecialchars($case['title']) ?></p>
    <p><strong>Question:</strong> <?= htmlspecialchars($case['description']) ?></p>
    <div style="margin-top: 16px;">
      <button class="btn btn-outline" onclick="showPopup('📰 Good start! Verifying through trusted news outlets is a great first step.')">Check reputable sources</button>
      <button class="btn btn-outline" onclick="showPopup('🔍 Helpful! Deepfake detection tools can help confirm your suspicion.')">Use a deepfake detector</button>
      <button class="btn btn-outline" onclick="showPopup('⚠️ Risky move! Always verify before sharing.')">Share immediately</button>
    </div>
  </div>
  <?php endforeach; ?>
</section>
<?php endif; ?>

<!-- === TOOLS SECTION (Hardcoded for links) === -->
<section class="container" style="margin: 80px auto;">
  <h2 class="section-title">🛠️ Try Real Tools</h2>
  <div class="features-grid">
    <div class="feature-card">
      <h3>Deepware Scanner</h3>
      <p>Upload a video to scan for deepfake traces.</p>
      <a href="https://www.deepware.ai/" target="_blank" class="btn btn-primary">Launch Deepware</a>
    </div>
    <div class="feature-card">
      <h3>Microsoft Video Authenticator</h3>
      <p>Analyzes videos frame-by-frame for signs of manipulation.</p>
      <a href="https://www.microsoft.com/en-us/ai/ai-lab-video-authenticator" target="_blank" class="btn btn-primary">Try Authenticator</a>
    </div>
  </div>
</section>

<!-- === FAQ SECTION === -->
<section class="container" style="margin: 80px auto;">
  <h2 class="section-title">❓ FAQ About Deepfakes</h2>
  <div class="features-grid">
    <?php foreach ($faqs as $faq): ?>
      <div class="feature-card">
        <h3 onclick="toggleAnswer(this)" style="cursor: pointer;"><?= htmlspecialchars($faq['title']) ?></h3>
        <p class="feature-description" style="display: none;"><?= htmlspecialchars($faq['description']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Your existing JS for FAQ accordion, popup, and drag/drop interaction -->
<script>
  function toggleAnswer(element) {
    const answer = element.nextElementSibling;
    answer.style.display = answer.style.display === "none" ? "block" : "none";
  }

  function showPopup(message) {
    alert(message); // simplified popup alert for demo; replace with your custom popup if you have it
  }

  // Drag and drop quiz JS
  let draggedLabelId = "";

  function allowDrop(ev) {
    ev.preventDefault();
  }

  function drag(ev) {
    draggedLabelId = ev.target.id;
  }

  function drop(ev, target) {
    ev.preventDefault();
    const label = document.getElementById(draggedLabelId);
    const dropZone = document.getElementById("drop" + target);
    dropZone.innerHTML = "";
    dropZone.appendChild(label);
  }

  function checkAnswer() {
    const dropA = document.getElementById("dropA").textContent.trim();
    const dropB = document.getElementById("dropB").textContent.trim();
    const feedback = document.getElementById("feedback");

    if (dropA === "🟢 Real" && dropB === "🔴 Fake") {
      feedback.textContent = "✅ Correct! Video A is real, Video B is fake!";
      feedback.style.color = "green";
    } else if (dropA === "" || dropB === "") {
      feedback.textContent = "⚠️ Please drag both labels to the videos before checking.";
      feedback.style.color = "orange";
    } else {
      feedback.textContent = "❌ Incorrect. Hint: One video might be AI-generated.";
      feedback.style.color = "red";
    }
  }
</script>


  <!-- Footer -->
  <footer id="contact" class="footer">
    <div class="container">
      <div class="footer-content">
        <div class="footer-section">
          <h3>VeriFact</h3>
          <p>Empowering truth through AI-powered verification. Join the fight against misinformation with cutting-edge technology.</p>
          <div class="social-links">
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-facebook"></i></a>
            <a href="#"><i class="fab fa-linkedin"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
          </div>
        </div>
        <div class="footer-section">
          <h4>Resources</h4>
          <ul>
            <li><a href="analyze.php">Deepfake Checker</a></li>
            <li><a href="index.php">Phishing Education</a></li>
            <li><a href="cyberSecurity.php">Cybersecurity Guide</a></li>
            <li><a href="#">AI Trends</a></li>
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
        <p>&copy; 2025 VeriFact. All rights reserved. Fighting deepfake misinformation with AI.</p>
      </div>
    </div>
  </footer>

  <script>
  function toggleMenu() {
    document.getElementById("navMenu").classList.toggle("show");
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

<script src="script.js"></script>

</body>
</html>

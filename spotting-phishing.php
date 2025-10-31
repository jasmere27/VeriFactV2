<?php
session_start();
include 'db.php';

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
    /* === Modal Styles === */
    .quiz-modal {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: var(--bg-glass);
      backdrop-filter: blur(20px);
      padding: 32px;
      border-radius: var(--border-radius-lg);
      border: 1px solid var(--border-color);
      z-index: 1001;
      box-shadow: var(--shadow-glow);
      width: 90%;
      max-width: 500px;
      color: var(--text-primary);
    }

    .quiz-modal.hidden,
    .modal-overlay.hidden {
      display: none;
    }

    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(4px);
      z-index: 1000;
    }

    .quiz-content h3 {
      font-size: 24px;
      margin-bottom: 16px;
      background: var(--gradient-text);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .quiz-content p {
      font-size: 16px;
      color: var(--text-secondary);
      margin-bottom: 16px;
    }

    .quiz-content ul {
      list-style: none;
      margin-bottom: 24px;
    }

    .quiz-content li {
      margin-bottom: 12px;
      font-weight: 500;
      color: var(--text-primary);
    }

    .quiz-content input[type="radio"] {
      margin-right: 8px;
    }
    .quiz-options {
  list-style: none;
  padding: 0;
}

.quiz-options li {
  margin-bottom: 12px;
  background: var(--bg-card);
  padding: 12px 16px;
  border-radius: var(--border-radius);
  cursor: pointer;
  border: 1px solid var(--border-color);
  transition: background 0.2s ease;
}

.quiz-options li:hover {
  background: var(--bg-accent);
}


    /* === Carousel Styles === */
    .carousel-section {
      text-align: center;
      margin: 80px auto;
      padding: 0 16px;
    }

    .carousel-section h2 {
      font-size: 32px;
      margin-bottom: 24px;
      background: var(--gradient-text);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .carousel-container {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 16px;
      flex-wrap: wrap;
    }

    .carousel-card {
      background: var(--bg-card);
      color: var(--text-primary);
      border: 1px solid var(--border-color);
      padding: 24px 32px;
      border-radius: var(--border-radius-lg);
      width: 90%;
      max-width: 600px;
      min-height: 100px;
      transition: all 0.3s ease;
      box-shadow: var(--shadow-card);
    }

    .carousel-btn {
      background: var(--gradient-primary);
      border: none;
      color: white;
      font-size: 24px;
      padding: 12px 20px;
      border-radius: 50%;
      cursor: pointer;
      transition: transform 0.2s ease;
    }

    .carousel-btn:hover {
      transform: scale(1.1);
    }

    iframe {
      border-radius: var(--border-radius-lg);
    }

    img {
      width: 100%;
      border-radius: 12px;
    }
    .zoom-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(15, 23, 42, 0.9);
  backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

.zoom-overlay img {
  max-width: 90%;
  max-height: 90%;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-glow);
  animation: fadeInZoom 0.3s ease;
}

.hidden {
  display: none;
}

@keyframes fadeInZoom {
  from {
    transform: scale(0.8);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
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

  .cyber-tabs a {
    transition: transform 0.4s ease, opacity 0.4s ease;
  }

  .active-tab {
    font-size: 1.1rem;
    padding: 12px 20px;
    background-color: #6c63ff;
    color: white !important;
    border-color: #6c63ff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transform: scale(1.1) translateY(-3px);
    z-index: 1;
  }

  .small-tab {
    font-size: 0.85rem;
    padding: 8px 14px;
    opacity: 0.7;
    transform: scale(0.95);
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
// === FETCH MODULES ===
function getModulesByType($pdo, $type) {
    $stmt = $pdo->prepare("SELECT * FROM cybersecurity_modules2 WHERE type = :type ORDER BY order_index");
    $stmt->execute([':type' => $type]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$videoModules = getModulesByType($pdo, 'video');
$signModules = getModulesByType($pdo, 'sign');
$comparisonModules = getModulesByType($pdo, 'comparison');
$imageGridModules = getModulesByType($pdo, 'image_grid');
$gameModules = getModulesByType($pdo, 'game'); 
?>

<!-- === VIDEO SECTION === -->
<section class="container" style="margin: 80px auto;">
  <h2 class="section-title"><?= htmlspecialchars($videoModules[0]['title'] ?? 'Watch & Learn') ?></h2>
  <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;">
    <iframe
      src="<?= htmlspecialchars($videoModules[0]['video_url']) ?>"
      title="<?= htmlspecialchars($videoModules[0]['title']) ?>"
      frameborder="0"
      allowfullscreen
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
      style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
    </iframe>
  </div>
</section>

<!-- === SIGNS SECTION === -->
<section class="container" style="margin: 80px auto;">
  <h2 class="section-title">Common Signs of a Phishing Attempt</h2>
  <div class="features-grid">
    <?php foreach ($signModules as $sign): ?>
    <div class="feature-card">
      <div class="feature-icon" style="font-size: 48px; color: <?= htmlspecialchars($sign['color'] ?? '#3498db') ?>;">
        <i class="<?= htmlspecialchars($sign['icon'] ?? 'fas fa-question-circle') ?>"></i>
      </div>
      <h3 class="feature-title"><?= htmlspecialchars($sign['title']) ?></h3>
      <p class="feature-description"><?= htmlspecialchars($sign['description']) ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- === MINI GAME SECTION (OPTIONAL) === -->
<?php if (!empty($gameModules)): ?>
<section class="container" style="margin: 80px auto; text-align: center;">
  <h2 class="section-title">🎮 <?= htmlspecialchars($gameModules[0]['title']) ?></h2>
  <p style="color: var(--text-secondary); margin-bottom: 24px;">
    <?= htmlspecialchars($gameModules[0]['description']) ?>
  </p>

  <div class="feature-card" style="max-width: 700px; margin: 0 auto;">
    <div id="phishScenario" style="font-size: 18px; margin-bottom: 24px;"></div>
    <div style="display: flex; justify-content: center; gap: 16px;">
      <button class="btn btn-outline" onclick="checkAnswer(true)">Phish</button>
      <button class="btn btn-outline" onclick="checkAnswer(false)">Legit</button>
    </div>
    <div id="phishFeedback" style="margin-top: 24px; font-weight: bold;"></div>
    <button class="btn btn-secondary" style="margin-top: 24px;" onclick="nextScenario()">Next</button>
  </div>
</section>
<?php endif; ?>

<!-- === COMPARISON SECTION === -->
<section class="container" style="margin: 80px auto;">
  <h2 class="section-title">Can You Spot the Difference?</h2>
  <div class="features-grid">
    <?php foreach ($comparisonModules as $item): ?>
    <div class="feature-card">
      <h3 class="feature-title"><?= htmlspecialchars($item['title']) ?></h3>
      <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" onclick="zoomImage(this.src, this.alt)" />
      <p class="feature-description"><?= htmlspecialchars($item['description']) ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- === IMAGE GRID SECTION === -->
<section class="container" style="margin: 80px auto;">
  <h2 class="section-title">Phishing Techniques in Action</h2>
  <div class="features-grid">
    <?php foreach ($imageGridModules as $item): ?>
    <div class="feature-card">
      <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" onclick="zoomImage(this.src, this.alt)" />
      <p class="feature-description"><?= htmlspecialchars($item['description']) ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

  <script>
  const scenarios = [
    {
      text: "Your account has been locked. Click here to verify your identity immediately.",
      isPhish: true
    },
    {
      text: "We noticed a new login to your account. If this wasn't you, please change your password.",
      isPhish: false
    },
    {
      text: "Congratulations! You've won a $500 gift card. Click to claim now.",
      isPhish: true
    },
    {
      text: "Your delivery is on the way. View tracking details in your account.",
      isPhish: false
    },
    {
      text: "This is Microsoft Support. Please call us immediately to avoid suspension.",
      isPhish: true
    },
    {
      text: "Your monthly billing statement is ready. Log in to view it securely.",
      isPhish: false
    }
  ];

  let currentIndex = 0;

  function showScenario(index) {
    document.getElementById("phishScenario").textContent = scenarios[index].text;
    document.getElementById("phishFeedback").textContent = "";
  }

  function checkAnswer(guess) {
    const correct = scenarios[currentIndex].isPhish;
    const feedback = document.getElementById("phishFeedback");

    if (guess === correct) {
      feedback.textContent = "✅ Correct!";
      feedback.style.color = "var(--accent-green)";
    } else {
      feedback.textContent = "❌ Not quite. That was " + (correct ? "a phishing attempt." : "a legitimate message.");
      feedback.style.color = "#ef4444";
    }
  }

  function nextScenario() {
    currentIndex = Math.floor(Math.random() * scenarios.length);
    showScenario(currentIndex);
  }

  // Init
  nextScenario();
  </script>

  <!-- Scripts -->
  <script>
    function openQuiz(id) {
      document.getElementById(id).classList.remove('hidden');
      document.getElementById('modalOverlay').classList.remove('hidden');
    }

    function closeQuiz(id) {
      document.getElementById(id).classList.add('hidden');
      document.getElementById('modalOverlay').classList.add('hidden');
    }

    const tips = [
      "Always check the sender's email address.",
      "Hover over links to verify the actual URL.",
      "Never download unexpected attachments.",
      "Look for grammar and spelling mistakes.",
      "Avoid entering credentials on unfamiliar sites.",
      "Use 2-factor authentication when available.",
      "Don't click on 'urgent' messages from unknown sources.",
      "Verify unexpected requests by calling the source."
    ];

    let tipIndex = 0;
    function showTip(index) {
      document.getElementById("phishingTip").textContent = tips[index];
    }
    function nextTip() {
      tipIndex = (tipIndex + 1) % tips.length;
      showTip(tipIndex);
    }
    function prevTip() {
      tipIndex = (tipIndex - 1 + tips.length) % tips.length;
      showTip(tipIndex);
    }

    showTip(tipIndex);
  </script>

  <script>
  function zoomImage(src, alt) {
    const zoomOverlay = document.getElementById("zoomOverlay");
    const zoomedImg = document.getElementById("zoomedImg");
    zoomedImg.src = src;
    zoomedImg.alt = alt;
    zoomOverlay.classList.remove("hidden");
  }

  function closeZoom() {
    document.getElementById("zoomOverlay").classList.add("hidden");
  }
</script>


  <!-- Zoom Overlay -->
<div id="zoomOverlay" class="zoom-overlay hidden" onclick="closeZoom()">
  <img id="zoomedImg" src="" alt="Zoomed Image" />
</div>

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
        <p>&copy; 2025 VeriFact. All rights reserved. Fighting misinformation with AI.</p>
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

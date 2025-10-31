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
</head>
<style>
  .flip-card {
    perspective: 1000px;
  }
  .flip-card-inner {
    position: relative;
    width: 100%;
    height: 160px;
    text-align: center;
    transition: transform 0.6s;
    transform-style: preserve-3d;
  }
  .flip-card:hover .flip-card-inner {
    transform: rotateY(180deg);
  }
  .flip-card-front, .flip-card-back {
    position: absolute;
    width: 100%;
    height: 160px;
    backface-visibility: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    box-sizing: border-box;
  }
  .flip-card-back {
    transform: rotateY(180deg);
    background-color:rgb(96, 55, 243);
  }
  .icon-colored {
    margin-right: 8px;
    font-size: 1.2em;
  }
  .icon-blue { color:rgb(228, 231, 233); }
  .icon-green { color: #27ae60; }
  .icon-red { color: #e74c3c; }
  .icon-yellow { color: #f1c40f; }
  .icon-purple { color: #9b59b6; }
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
function getCybersecurityModules($pdo, $type = null) {
    $sql = "SELECT * FROM cybersecurity_modules";
    if ($type !== null) {
        $sql .= " WHERE type = :type";
    }
    $stmt = $pdo->prepare($sql);
    if ($type !== null) {
        $stmt->bindParam(':type', $type);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCybersecurityVideosByKeyword($pdo, $keyword) {
    $videos = getCybersecurityModules($pdo, 'video');
    return array_filter($videos, function ($video) use ($keyword) {
        return stripos($video['title'], $keyword) !== false;
    });
}

function getCyberFacts($pdo) {
    $sql = "SELECT description FROM cybersecurity_modules WHERE type = 'fact' ORDER BY order_index ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>


<!-- 1. Educational Video Section (Main video only) -->
<?php
// Get all video modules
$allVideos = getCybersecurityModules($pdo, 'video');

// Filter for educational videos by keyword (e.g., title contains "phishing", "educational", etc.)
$educationalVideos = array_filter($allVideos, function ($video) {
    return stripos($video['title'], 'identity') !== false || stripos($video['title'], 'educational') !== false;
});
?>

<?php foreach ($educationalVideos as $video): ?>
<section class="container" style="margin: 60px auto;">
  <h3 class="section-title">🎓 <?= htmlspecialchars($video['title']) ?></h3>
  <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 40px;">
    <iframe
      src="<?= htmlspecialchars($video['video_url']) ?>"
      title="<?= htmlspecialchars($video['title']) ?>"
      frameborder="0"
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
      allowfullscreen
      style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
    </iframe>
  </div>
</section>
<?php endforeach; ?>


<!-- 2. Online Presence Section -->
<?php
$textTips = getCybersecurityModules($pdo, 'text', 'online_presence');
?>

<section class="container" style="margin: 80px auto;">
  <h3 class="section-title"><span style="color: #27ae60;">🛡️</span> Securing Your Online Presence</h3>
  <div class="features-grid">
    <?php foreach ($textTips as $tip): ?>
      <div class="feature-card">
        <div class="feature-icon" style="font-size: 48px; color: #3498db;">
          <i class="<?= htmlspecialchars($tip['icon_class']) ?>"></i>
        </div>
        <h3 class="feature-title"><?= htmlspecialchars($tip['title']) ?></h3>
        <p class="feature-description"><?= htmlspecialchars($tip['description']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- 3. Myth Busters Section -->
<?php
$myths = getCybersecurityModules($pdo, 'myth');
?>

<section class="container" style="margin: 80px auto;">
  <h3 class="section-title">🧠 Security Myth Busters</h3>
  <div class="features-grid">
    <?php foreach ($myths as $myth): ?>
      <div class="feature-card flip-card">
        <div class="flip-card-inner">
          <div class="flip-card-front">
            <h3 class="feature-title"><?= htmlspecialchars($myth['title']) ?></h3>
          </div>
          <div class="flip-card-back">
            <p class="feature-description"><?= htmlspecialchars($myth['description']) ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>



<!-- 5. Password Strength Lab Section (Second video + input) -->
<?php
// Fetch all videos (if not already done earlier in the file)
$allVideos = getCybersecurityModules($pdo, 'video');

// Filter only password-related videos (by title or keyword)
$passwordVideos = array_filter($allVideos, function ($video) {
    return stripos($video['title'], 'password') !== false;
});
?>

<?php if (!empty($passwordVideos)): ?>
  <?php foreach ($passwordVideos as $video): ?>
  <section class="container" style="margin: 80px auto; text-align: center;">
    <h3 class="section-title">🔐 <?= htmlspecialchars($video['title']) ?></h3>
    <div style="width: 1000px; max-width: 100%; margin: 0 auto 32px auto; position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 40px;">
      <iframe
        src="<?= htmlspecialchars($video['video_url']) ?>"
        title="<?= htmlspecialchars($video['title']) ?>"
        frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowfullscreen
        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
      </iframe>
    </div>
    <p style="color: var(--text-secondary); margin-bottom: 24px;">
      Type a password below to see how strong it is (don't use your real password!).
    </p>
    <div class="feature-card" style="max-width: 600px; margin: 0 auto; text-align: left;">
  <div style="position: relative;">
    <input
      type="password"
      id="passwordInput"
      placeholder="Enter a test password"
      class="btn btn-outline"
      style="width: 100%; padding: 12px 40px 12px 12px;"
      oninput="checkPasswordStrength()"
    >
    <span
      id="togglePassword"
      onclick="togglePasswordVisibility()"
      style="position: absolute; top: 50%; right: 12px; transform: translateY(-50%); cursor: pointer; color: #888;"
    >
      👁️
    </span>
  </div>
  <div id="passwordStrength" style="margin-top: 16px; font-weight: bold;"></div>
</div>
  </section>
  <?php endforeach; ?>
<?php endif; ?>


<script>
function checkPasswordStrength() {
  const input = document.getElementById('passwordInput').value;
  const strengthDisplay = document.getElementById('passwordStrength');

  if (input.length < 6) {
    strengthDisplay.textContent = "Weak: Too short";
    strengthDisplay.style.color = "red";
  } else if (/[A-Z]/.test(input) && /[0-9]/.test(input) && /[^A-Za-z0-9]/.test(input)) {
    strengthDisplay.textContent = "Strong password";
    strengthDisplay.style.color = "green";
  } else {
    strengthDisplay.textContent = "Moderate password – try adding numbers & symbols";
    strengthDisplay.style.color = "orange";
  }
  
}
function togglePasswordVisibility() {
  const input = document.getElementById("passwordInput");
  const icon = document.getElementById("togglePassword");
  
  if (input.type === "password") {
    input.type = "text";
    icon.textContent = "🙈"; // You can keep it 👁️ if you don’t want to change
  } else {
    input.type = "password";
    icon.textContent = "👁️";
  }
}

</script>




<!-- Quick Security Check Section
<section class="container" style="margin: 80px auto; text-align: center;">
  <h3 class="section-title">🔐 Quick Security Check</h3>
  <p style="color: var(--text-secondary); margin-bottom: 24px;">
    Answer these quick questions to evaluate your digital hygiene practices.
  </p>
  <div class="feature-card" style="max-width: 700px; margin: 0 auto;">
    <div id="securityQuestion" style="font-size: 18px; margin-bottom: 24px;"></div>
    <div style="display: flex; justify-content: center; gap: 16px;">
      <button class="btn btn-outline" onclick="answerSecurityCheck(true)">Yes</button>
      <button class="btn btn-outline" onclick="answerSecurityCheck(false)">No</button>
    </div>
    <div id="securityFeedback" style="margin-top: 24px; font-weight: bold;"></div>
    <button class="btn btn-secondary" style="margin-top: 24px;" onclick="nextSecurityCheck()">Next</button>
  </div>
</section>

<script>
  const securityChecks = [
    { question: "Do you use a different password for each account?", correct: true },
    { question: "Do you click links from unknown emails or texts?", correct: false },
    { question: "Have you enabled 2FA on your main accounts?", correct: true },
    { question: "Do you regularly update your software and devices?", correct: true },
    { question: "Do you share your passwords with others?", correct: false }
  ];

  let currentCheck = 0;

  function showSecurityCheck() {
    document.getElementById('securityQuestion').textContent = securityChecks[currentCheck].question;
    document.getElementById('securityFeedback').textContent = "";
  }

  function answerSecurityCheck(answer) {
    const isCorrect = answer === securityChecks[currentCheck].correct;
    document.getElementById('securityFeedback').textContent = isCorrect ? "✅ Great job! That's the right practice." : "⚠️ Oops! Consider changing that habit for better security.";
  }

  function nextSecurityCheck() {
    currentCheck = (currentCheck + 1) % securityChecks.length;
    showSecurityCheck();
  }

  // Initialize
  showSecurityCheck();
</script> -->

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

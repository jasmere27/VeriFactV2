<?php
session_start();
require_once 'db.php';

// Visitor logging
$ip = $_SERVER['REMOTE_ADDR'];
$visitedAt = date('Y-m-d H:i:s');
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$pageUrl = basename($_SERVER['REQUEST_URI']) ?? 'Unknown';
$email = $_SESSION['email'] ?? 'Guest';

$stmt = $pdo->prepare("INSERT INTO visitor_logs (ip_address, visited_at, user_agent, page_url, email) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$ip, $visitedAt, $userAgent, $pageUrl, $email]);

$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About | VeriFact</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header class="header">
  <div class="container" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; padding:16px;">
    <div class="nav-brand">
      <a href="index.php" class="logo-link" style="display:flex; align-items:center; text-decoration:none; color:inherit;">
        <div class="logo-icon" style="color:white; font-size:24px; margin-right:8px;">
          <i class="fas fa-shield-alt"></i>
        </div>
        <h1 class="logo" style="margin:0; font-size:24px;">VeriFact</h1>
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
        <li><a href="about.php" class="active">About</a></li>
      </ul>
    </nav>

    <!-- Desktop Nav -->
    <nav class="nav-menu">
      <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
      <a href="analyze.php" class="nav-link"><i class="fas fa-search"></i> Fact-Checker</a>
      <a href="cyberSecurity.php" class="nav-link"><i class="fas fa-shield-alt"></i> CyberSecurity</a>
      <a href="about.php" class="nav-link active"><i class="fas fa-info-circle"></i> About</a>
      <?php if (!empty($_SESSION['role']) && $_SESSION['role'] == 1): ?>
        <a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<!-- About Section -->
<section class="features" style="padding: 60px 20px; text-align:center;">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">About VeriFact</h2>
      <p class="section-subtitle">AI-Powered Fake News Detection and Cybersecurity Awareness</p>
    </div>

    <div class="about-content" style="max-width:900px; margin:0 auto; text-align:left;">
      <p>
        <strong>VeriFact</strong> is a web-based AI system designed to detect and classify fake news across 
        <strong>text, image, voice, and URL</strong> formats. It uses advanced technologies like 
        <strong>Natural Language Processing (NLP)</strong>, <strong>Optical Character Recognition (OCR)</strong>,
        <strong>Speech-to-Text (STT)</strong>, and <strong>real-time web verification</strong> to analyze online content.
      </p>

      <p>
        VeriFact’s custom-built <strong>AI Agent</strong> cross-checks information from verified online sources such as 
        government sites, academic publications, and credible news outlets. It delivers results with 
        a <strong>credibility verdict</strong>, <strong>confidence score</strong>, and <strong>source links</strong> for transparency.
      </p>

      <p>
        Beyond detection, VeriFact promotes <strong>cybersecurity awareness</strong> by including educational modules, 
        <strong>text-to-speech feedback</strong>, and <strong>image previews</strong> to make the system inclusive and engaging.
      </p>
    </div>

    <!-- Mission and Vision -->
    <div class="mission-vision" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:30px; margin-top:50px;">
      <div class="mission-card" style="background:white; border-radius:12px; box-shadow:0 3px 8px rgba(0,0,0,0.1); padding:25px;">
        <h3 class="section-title">Our Mission</h3>
        <p>To empower users with AI-driven tools that promote truth, critical thinking, and cybersecurity awareness in the digital world.</p>
      </div>
      <div class="vision-card" style="background:white; border-radius:12px; box-shadow:0 3px 8px rgba(0,0,0,0.1); padding:25px;">
        <h3 class="section-title">Our Vision</h3>
        <p>To build a digitally literate society where people can confidently distinguish real information from fake news using intelligent, accessible technology.</p>
      </div>
    </div>
  </div>
</section>

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
          <li><a href="about.php">About Us</a></li>
          <li><a href="#">Careers</a></li>
          <li><a href="#">Press</a></li>
          <li><a href="#contact">Contact</a></li>
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

</body>
</html>

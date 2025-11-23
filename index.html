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
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$registered_users = $stmt->fetchColumn();
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
// Get unique visits today
$stmt = $pdo->query("SELECT COUNT(DISTINCT ip_address) FROM visitor_logs WHERE DATE(visit_time) = CURDATE()");
$unique_visits_today = $stmt->fetchColumn();
// Get total visits ever
$stmt = $pdo->query("SELECT COUNT(*) FROM visitor_logs");
$total_visits = $stmt->fetchColumn();
// Get registered users count (replace 'users' with your actual user table name)
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$registered_users = $stmt->fetchColumn();
// Now you can use these variables anywhere in your HTML below
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
/* Modal Base Styles */
.modal {
  display: none;
  position: fixed;
  z-index: 999;
  left: 0; top: 0;
  width: 100%; height: 100%;
  background-color: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
}

/* Modal Content Box */
.modal-content {
  background: white;
  padding: 40px;
  border-radius: 16px;
  max-width: 400px;
  width: 90%;
  box-shadow: 0 15px 40px rgba(0,0,0,0.3);
  text-align: center;
  position: relative;
}

/* Close Button */
.modal .close {
  position: absolute;
  top: 12px;
  right: 16px;
  font-size: 24px;
  cursor: pointer;
  color: #888;
}

/* Modal Form Inputs */
.modal-content input {
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 15px;
}

/* Modal Button */
.modal-btn {
  padding: 14px;
  background: linear-gradient(to right, #6c63ff, #9b59b6);
  color: white;
  font-weight: bold;
  border: none;
  border-radius: 10px;
  cursor: pointer;
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
.auth-card {
  width: 100%;
  max-width: 100%;
  padding: 24px;
  box-sizing: border-box;
  margin: 0 auto; /* centers horizontally */
}

@media (max-width: 768px) {
  .auth-card {
    max-width: 90%;
    margin: 0 auto;
  }
}


/* Form container responsiveness */
.auth-card form {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

/* Input and button full width on small screens */
.auth-card input,
.auth-card button {
  width: 100%;
  box-sizing: border-box;
}


/* Upload label and hidden input fix */
.upload-label {
  display: inline-block;
  text-align: center;
  cursor: pointer;
  padding: 12px 20px;
  border-radius: 8px;
  background-color: #4f46e5;
  color: white;
  transition: background-color 0.3s;
}

.upload-label:hover {
  background-color: #3730a3;
}

.upload-label input {
  display: none;
}

/* Media Queries for fine-tuning */


  @media (max-width: 480px) {
    .hero-buttons a {
      max-width: 85% !important;
      padding: 10px 14px !important;
      font-size: 14px !important;
      border-radius: 8px !important;
    }

    .hero-buttons {
      flex-direction: column;
      align-items: center;
    }
  }

   .modal {
    display: none; /* Hidden by default */
    position: fixed;
    z-index: 1000;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background-color: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
    animation: fadeIn 0.3s ease forwards;
  }

  /* Modal content box */
  .modal-content {
    background: #fff;
    max-width: 400px;
    margin: 8% auto;
    padding: 30px 40px;
    border-radius: 12px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
    position: relative;
    animation: slideIn 0.4s ease forwards;
  }

  /* Close button */
  .close {
    position: absolute;
    top: 15px; right: 20px;
    font-size: 28px;
    font-weight: bold;
    color: #888;
    cursor: pointer;
    transition: color 0.2s ease;
  }
  .close:hover {
    color: #6c63ff;
  }

  /* Header */
  h2 {
    margin-bottom: 24px;
    color: #6c63ff;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-weight: 700;
    text-align: center;
  }

  /* Form styles */
  form {
    display: flex;
    flex-direction: column;
    gap: 18px;
  }

  form input {
    padding: 14px 18px;
    font-size: 16px;
    border: 1.8px solid #ddd;
    border-radius: 8px;
    transition: border-color 0.3s ease;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  form input:focus {
    border-color: #6c63ff;
    outline: none;
    box-shadow: 0 0 8px rgba(108, 99, 255, 0.3);
  }

  /* Button */
  .modal-btn {
    background-color: #6c63ff;
    color: white;
    padding: 14px 0;
    border: none;
    border-radius: 8px;
    font-size: 17px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  .modal-btn:hover {
    background-color: #5750d6;
  }

  /* Animations */
  @keyframes fadeIn {
    from {opacity: 0;}
    to {opacity: 1;}
  }

  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateY(-30px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
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
  <a href="index.php" class="nav-link active">
    <i class="fas fa-home"></i> Home
  </a>

  <a href="analyze.php" class="nav-link">
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
    
<main>
<!-- Hero Section -->
<section class="hero">
  <div class="container">
    <div class="hero-content">
      <h1 class="hero-title">
        <span class="gradient-text">Verify Truth,</span><br>
        <span class="gradient-text">Fight the False</span>
      </h1>
      <p class="hero-subtitle">
        AI-powered detection to ensure the authenticity of news and media.
        Protect yourself from fake news with cutting-edge technology.
      </p>
  <!-- Buttons -->
  <div class="hero-buttons" style="
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 12px;
    width: 100%;
    max-width: 400px;
    box-sizing: border-box;
  ">
    <a href="analyze.php" class="btn-primary" style="
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 12px 16px;
      font-size: 15px;
      border-radius: 10px;
      background-color: #6c63ff;
      color: white;
      border: none;
      text-decoration: none;
      width: 100%;
      box-sizing: border-box;
    ">
      <i class="fas fa-play"></i>
      Start Verifying
    </a>
  </div>
      <div class="trusted-by">
        <p>Trusted by leading organizations</p>
        <div class="trusted-logos">
          <div>CNN</div>
          <div>BBC</div>
          <div>Reuters</div>
          <div>AP</div>
          <div>NPR</div>
        </div>
      </div>
    </div>

<?php if ($show_login): ?>
<!-- 🔒 LOGIN FORM -->
<div class="hero-visual" style="
  flex: 1;
  max-width: 500px;
  min-width: 320px;
  padding: 16px;
  width: 100%;
  box-sizing: border-box;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  border-radius: 20px;
  box-shadow: inset 0 0 10px rgba(0,0,0,0.02);
">
  <div class="auth-card" style="
    background: #c9d4ff;
    padding: 32px 24px;
    border-radius: 20px;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
    width: 5000%;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: center;
    animation: fadeIn 0.5s ease-in-out;
  ">
    <h2 style="font-size: 26px; color: #6c63ff; margin-bottom: 8px;">Welcome Back</h2>
    <p style="color: #666; margin-bottom: 24px;">Sign in to continue verifying content with AI-powered tools.</p>

    <form action="login.php" method="POST" style="display: flex; flex-direction: column; gap: 16px;">
      <input type="email" name="email" placeholder="Email" required style="padding: 14px; border: 1px solid #ccc; border-radius: 10px; font-size: 16px; width: 100%; box-sizing: border-box;">
      <input type="password" name="password" placeholder="Password" required style="padding: 14px; border: 1px solid #ccc; border-radius: 10px; font-size: 16px; width: 100%; box-sizing: border-box;">
      <button type="submit" style="
        padding: 14px;
        font-size: 16px;
        border-radius: 10px;
        background: linear-gradient(90deg, #6c63ff, #9b59b6);
        color: white;
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        cursor: pointer;
        width: 100%;
        box-sizing: border-box;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
      " onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'">
        Login
      </button>
    </form>

    <p style="margin-top: 20px; font-size: 14px; color: black;">
      Don't have an account?
      <a href="#" id="open-signup" style="color: #6c63ff; text-decoration: none; font-weight: bold;">Sign up</a>
    </p>

    <div style="margin-top: 20px;">
      <button style="
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 12px 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: white;
        font-weight: 500;
        font-size: 14px;
        color: #444;
        cursor: pointer;
        width: 100%;
        max-width: 280px;
        margin: 0 auto;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: background 0.2s ease;
      " onmouseover="this.style.background='#c9d4ff'" onmouseout="this.style.background='#c9d4ff'">
        <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google icon" style="width: 20px; height: 20px;">
        Continue with Google
      </button>
    </div>

    <div style="text-align: center; margin-top: 16px;">
      <a href="index.php?guest=1" style="
        padding: 12px;
        border: 2px solid #6c63ff;
        border-radius: 10px;
        color: #6c63ff;
        text-decoration: none;
        display: inline-block;
        width: 100%;
        max-width: 280px;
        box-sizing: border-box;
        transition: all 0.2s ease;
      " onmouseover="this.style.background='#6c63ff'; this.style.color='white'" onmouseout="this.style.background='white'; this.style.color='#6c63ff'">
        Continue without signing in
      </a>
    </div>
  </div>
</div>

<!-- Simple Fade-in Animation -->
<style>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
        <?php else: ?>
        <!-- 🤖 HERO VISUAL ANIMATION -->
        <div class="ai-illustration">
          <div class="ai-container">
            <div class="ai-glow"></div>
            <div class="ai-main">
              <div class="circuit-pattern"></div>
              <div class="ai-brain">
                <i class="fas fa-brain"></i>
              </div>
              <div class="floating-elements">
                <div class="floating-dot"></div>
                <div class="floating-dot"></div>
                <div class="floating-dot"></div>
              </div>
            </div>
            <div class="orbiting-elements">
              <div class="orbit-dot"></div>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

        <!-- How It Works Section -->
        <section id="how-it-works" class="how-it-works  ">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">How It Works</h2>
                    <p class="section-subtitle">
                        Our advanced AI system analyzes content in three simple steps to deliver accurate results
                    </p>
                </div>
                <div class="steps-grid">
                    <div class="step-card">
                        <div class="step-icon blue">
                            <i class="fas fa-upload"></i>
                        </div>
                        <div class="step-number">Step 01</div>
                        <h3 class="step-title">Upload Content</h3>
                        <p class="step-description">
                            Submit text, images, or audio content for analysis using our secure upload system
                        </p>
                    </div>
                    <div class="step-card">
                        <div class="step-icon purple">
                            <i class="fas fa-brain"></i>
                        </div>
                        <div class="step-number">Step 02</div>
                        <h3 class="step-title">AI Analyzes</h3>
                        <p class="step-description">
                            Our neural networks process and verify the content against trusted sources
                        </p>
                    </div>
                    <div class="step-card">
                        <div class="step-icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="step-number">Step 03</div>
                        <h3 class="step-title">Get Result</h3>
                        <p class="step-description">
                            Receive detailed authenticity report with confidence score and explanations
                        </p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Features Section -->
        <section id="features" class="features">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Powerful Features</h2>
                    <p class="section-subtitle">
                        Advanced AI capabilities to detect fake news across all media types
                    </p>
                </div>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon blue">
                            <i class="fas fa-file-alt" style="font-size: 48px; color: #007BFF;"></i>
                        </div>
                        <h3 class="feature-title">Text Analysis</h3>
                        <p class="feature-description">
                            Advanced NLP algorithms analyze linguistic patterns, fact-check claims, and verify sources in real-time.
                        </p>
                        <ul class="feature-list">
                            <li><i class="fas fa-check"></i> Source verification</li>
                            <li><i class="fas fa-check"></i> Sentiment analysis</li>
                            <li><i class="fas fa-check"></i> Fact checking</li>
                            <li><i class="fas fa-check"></i> Language patterns</li>
                        </ul>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon purple">
                            <i class="fas fa-image" style="font-size: 48px; color: #6f42c1;"></i>
                        </div>
                        <h3 class="feature-title">Image OCR Detection</h3>
                        <p class="feature-description">
                            Extract and verify text from images, detect manipulated content, and analyze visual authenticity.
                        </p>
                        <ul class="feature-list">
                            <li><i class="fas fa-check"></i> Text extraction</li>
                            <li><i class="fas fa-check"></i> Manipulation detection</li>
                            <li><i class="fas fa-check"></i> Reverse image search</li>
                            <li><i class="fas fa-check"></i> Metadata analysis</li>
                        </ul>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon green">
                            <i class="fas fa-microphone" style="font-size: 48px; color: #28a745;"></i>
                        </div>
                        <h3 class="feature-title">Audio Fact-Check</h3>
                        <p class="feature-description">
                            Transcribe audio content, analyze speech patterns, and verify spoken claims against trusted sources.
                        </p>
                        <ul class="feature-list">
                            <li><i class="fas fa-check"></i> Speech-to-text</li>
                            <li><i class="fas fa-check"></i> Voice analysis</li>
                            <li><i class="fas fa-check"></i> Claim verification</li>
                            <li><i class="fas fa-check"></i> Audio forensics</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cybersecurity Practices Preview (Now Above with CTA's background) -->
 <section class="cta-section" style="padding: 80px 0;">
            <div class="container">
            <div style="max-width: 800px; margin: 0 auto; text-align: center;">
            <div style="background: rgba(59, 130, 246, 0.1); backdrop-filter: blur(20px); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 24px; padding: 48px;">
            <h2 style="font-size: 48px; font-weight: 800; margin-bottom: 24px; background: linear-gradient(135deg, #ffffff, #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                Cybersecurity Practices
            </h2>
            <p class="section-subtitle" style="color: var(--text-secondary); max-width: 600px;">
                VeriFact doesn't stop at detecting fake news — we also help you stay cyber aware.
                Learn how to spot phishing emails, recognize deepfakes, and secure your online presence.
            </p>
            <div style="margin-top: 24px;">
                <a href="cyberSecurity.php" class="btn btn-primary btn-lg">
                    Explore Cybersecurity Modules
                </a>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section (Now Below with no background) -->
<section class="cta-section" style="padding: 80px 0; background: transparent;">
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto; text-align: center;">
            <h2 style="font-size: 48px; font-weight: 800; margin-bottom: 24px;">
                Ready to Fight Fake News?
            </h2>
            <p style="font-size: 20px; color: var(--text-secondary); margin-bottom: 32px; max-width: 600px; margin-left: auto; margin-right: auto;">
                Join thousands of users who trust VeriFact to verify news and protect against fake information.
            </p>
            <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                <a href="analyze.php" class="btn btn-primary btn-lg">
                    Start Analyzing
                </a>                   
            </div>
        </div>
    </div>
</section>
</main>

<!-- Sign Up Modal -->
<div id="signupModal" class="modal">
  <div class="modal-content">
    <span class="close" id="closeModal">&times;</span>
    <h2>Create Account</h2>
    <form action="signup.php" method="POST">
      <input type="text" name="name" placeholder="Full Name" required />
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit" class="modal-btn">Sign Up</button>
    </form>
  </div>
</div>
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
  const modal = document.getElementById("signupModal");
  const openBtn = document.getElementById("open-signup");
  const closeBtn = document.getElementById("closeModal");

  openBtn.addEventListener("click", (e) => {
    e.preventDefault();
    modal.style.display = "flex";
  });

  closeBtn.addEventListener("click", () => {
    modal.style.display = "none";
  });

  window.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.style.display = "none";
    }
  });
</script>

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

<script>
  // Modal open/close logic
  const modal = document.getElementById('signupModal');
  const closeBtn = document.getElementById('closeModal');

  closeBtn.onclick = () => modal.style.display = 'none';

  // Close modal if clicking outside content area
  window.onclick = event => {
    if(event.target === modal) {
      modal.style.display = 'none';
    }
  };

  // For demonstration, you can open modal with:
  // modal.style.display = 'block';
</script>
<script src="script.js"></script>
</body>
</html>

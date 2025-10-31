<?php
session_start();
include 'db.php';

// Dummy user data from session (replace with real session values)
$username = $_SESSION['username'] ?? 'Guest';
$email = $_SESSION['email'] ?? 'guest@example.com';

// Show success alert if redirected after update
$showSuccess = isset($_GET['status']) && $_GET['status'] === 'success';
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

<?php
require_once 'db.php'; // Ensure this is the correct path to your db file

$ip = $_SERVER['REMOTE_ADDR'];
$visitedAt = date('Y-m-d H:i:s');
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$pageUrl = basename($_SERVER['REQUEST_URI']) ?? 'Unknown'; 

$stmt = $pdo->prepare("INSERT INTO visitor_logs (ip_address, visited_at, user_agent, page_url) VALUES (?, ?, ?, ?)");
$stmt->execute([$ip, $visitedAt, $userAgent, $pageUrl]);
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
    .settings-container {
        max-width: 430px;
        margin: 4rem auto 0;
        background: #fff;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        color: black;
        min-height: 440px; /* ensures the form area is at least 400px tall */
        }

    h2 {
      text-align: center;
    }
    .setting-group {
      margin-bottom: 1.5rem;
    }
    .setting-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: bold;
    }
    .setting-group input[type="text"],
    .setting-group input[type="email"],
    .setting-group input[type="password"] {
      width: 100%;
      padding: 0.5rem;
      border-radius: 4px;
      border: 1px solid #ccc;
    }
    .actions {
      display: flex;
      justify-content: space-between;
      margin-top: 2rem;
    }
    .btn {
      padding: 0.6rem 1.5rem;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .btn-save {
      background: #4CAF50;
      color: white;
    }
    .btn-logout {
      background: #f44336;
      color: white;
    }
    .toggle {
      display: flex;
      align-items: center;
    }
    .toggle input {
      margin-left: 10px;
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

.alert-success {
  background-color: #d4edda;
  color: #155724;
  padding: 1rem;
  border-radius: 6px;
  border: 1px solid #c3e6cb;
  margin-bottom: 1.5rem;
  text-align: center;
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
    <li><a href="index.php?signin=1" class="nav-link" id="mobile-signin">Sign In</a></li>
  </ul>
</nav>

<!-- Desktop Nav -->
<nav class="nav-menu">
  <a href="index.php" class="nav-link ">Home</a>
  <a href="analyze.php" class="nav-link">Fact-Checker</a>
  <a href="cyberSecurity.php" class="nav-link">CyberSecurity</a>
  <a href="#contact" class="nav-link">Contact</a>

  <?php if (!$is_logged_in): ?>
    <a href="index.php?remove_guest=1" class="nav-link active" style="margin-left:auto; font-weight: bold; ">Sign In</a>
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
<body>
  <div class="settings-container">
    <h2>User Settings</h2>

    <?php if ($showSuccess): ?>
      <div class="alert-success">
        ✅ Profile updated successfully!
      </div>
    <?php endif; ?>

    <form method="POST" action="update_profile.php">
      <div class="setting-group">
        <label>Username</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">
      </div>

      <div class="setting-group">
        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
      </div>

      <div class="setting-group">
        <label>Change Password</label>
        <input type="password" name="new_password" placeholder="Enter new password">
      </div>

      <div class="actions">
        <button type="submit" class="btn btn-save">Save Changes</button>
        <a href="logout.php" class="btn btn-logout">Logout</a>
      </div>
    </form>
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


<?php
session_start();
require 'db.php';

// Fetch active modules for "cybersecurity_modules"
$stmt = $pdo->prepare("SELECT id, title, description, icon_class, category, type, video_url, order_index, active, created_at, subtype FROM cybersecurity_modules WHERE active = 1 ORDER BY order_index ASC");
$stmt->execute();
$modules = $stmt->fetchAll();

// Fetch all modules for "cybersecurity_module1", order by order_index ASC
$stmt1 = $pdo->prepare("SELECT id, type, subtype, title, description, video_url, icon, order_index, color FROM cybersecurity_modules1 ORDER BY order_index ASC");
$stmt1->execute();
$modules1 = $stmt1->fetchAll();

// Fetch all modules for "cybersecurity_modules2", order by order_index ASC
$stmt2 = $pdo->prepare("SELECT id, type, subtype, title, description, video_url, image_url, icon, color, order_index, created_at, updated_at FROM cybersecurity_modules2 ORDER BY order_index ASC");
$stmt2->execute();
$modules2 = $stmt2->fetchAll();
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
// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?signin=1");
    exit;
}

// Redirect if not an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Access denied. Admins only.";
    exit;
}
?>

<?php
// Track stats
$stmt = $pdo->query("SELECT COUNT(DISTINCT ip_address) FROM visitor_logs WHERE DATE(visit_time) = CURDATE()");
$unique_visits_today = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM visitor_logs");
$total_visits = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users"); // Change 'users' to your actual table
$registered_users = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXX"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyze Content - VeriFact</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    


<main>
    <!-- Analytics Section -->
<div id="analytics-tab" class="admin-section" style="display: none;">
  <h2 style="margin-bottom: 24px;">Analytics Dashboard</h2>
  <?php
    $totalArticles = 100;
    $fakeNewsCount = 35;
    $suspiciousCount = 20;
    $avgConfidence = 82.5;
    $timeSaved = 120;
  ?>
  <div style="display: flex; gap: 24px; flex-wrap: wrap;">
    <div class="stat-card">✅ <strong>Total Articles:</strong> <?= $totalArticles ?></div>
    <div class="stat-card">❌ <strong>Fake News:</strong> <?= $fakeNewsCount ?></div>
    <div class="stat-card">⚠️ <strong>Suspicious:</strong> <?= $suspiciousCount ?></div>
    <div class="stat-card">📈 <strong>Avg. Confidence:</strong> <?= number_format($avgConfidence, 2) ?>%</div>
    <div class="stat-card">🕒 <strong>Time Saved:</strong> <?= $timeSaved ?> mins</div>
  </div>
  <div style="margin-top: 32px;">
    <canvas id="articleBreakdownChart" height="120"></canvas>
  </div>
</div>

<style>
  .stat-card {
    background: #f3f4f6;
    padding: 16px 20px;
    border-radius: 12px;
    font-size: 16px;
    min-width: 160px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  }
</style>

<script>
  let articleBreakdownChart;

  function showSection(id) {
    document.querySelectorAll('.admin-section').forEach(section => {
      section.style.display = 'none';
    });

    const el = document.getElementById(id);
    if (el) {
      el.style.display = 'block';

      if (id === 'analytics-tab') {
        setTimeout(() => {
          if (articleBreakdownChart) {
            articleBreakdownChart.destroy();
          }

          const ctx = document.getElementById('articleBreakdownChart').getContext('2d');
          articleBreakdownChart = new Chart(ctx, {
            type: 'bar',
            data: {
              labels: ['Total Articles', 'Fake News', 'Suspicious'],
              datasets: [{
                label: 'Article Analysis',
                data: [<?= $totalArticles ?>, <?= $fakeNewsCount ?>, <?= $suspiciousCount ?>],
                backgroundColor: ['#4ade80', '#f87171', '#facc15'],
                borderWidth: 1
              }]
            },
            options: {
              responsive: true,
              scales: {
                y: {
                  beginAtZero: true
                }
              }
            }
          });
        }, 100);
      }
    }
  }
</script>
</main>
<script src="script.js"></script>
</body>
</html>
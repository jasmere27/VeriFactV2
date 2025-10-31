
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
// Assuming $pdo is your PDO connection

$users = [];
$query = "SELECT * FROM users WHERE 1";
$params = [];

if (!empty($_GET['search_email'])) {
    $query .= " AND email LIKE ?";
    $params[] = "%" . $_GET['search_email'] . "%";
}
if (!empty($_GET['search_role'])) {
    $query .= " AND role = ?";
    $params[] = $_GET['search_role'];
}

$query .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user to edit if edit param is present
$editUser = null;
if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<?php
// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?signin=1");
    exit;
}

// Redirect if not an admin
if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
    echo "Access denied. Admins only.";
    exit;
}

$stmt = $pdo->query("SELECT * FROM cybersecurity_modules ORDER BY created_at DESC");
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php
if (isset($_POST['update_user'])) {
    $id = intval($_POST['id']);
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
    $stmt->execute([$username, $email, $role, $id]);

    header("Location: dashboard.php#manageUsers"); // Redirect after update
    exit;
}
?>
<?php
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$deleteId]);
    header("Location: dashboard.php#manageUsers");
    exit;
}
?>
<?php
$filter_ip = $_GET['ip'] ?? '';
$from_date = $_GET['start_date'] ?? '';
$to_date = $_GET['end_date'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int) $_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build base queries
$query = "SELECT * FROM visitor_logs WHERE 1=1";
$countQuery = "SELECT COUNT(*) FROM visitor_logs WHERE 1=1";
$params = [];
$countParams = [];

if (!empty($filter_ip)) {
    $query .= " AND email LIKE ?";
    $countQuery .= " AND email LIKE ?";
    $params[] = "%$filter_ip%";
    $countParams[] = "%$filter_ip%";
}

if (!empty($from_date)) {
    $query .= " AND visit_time >= ?";
    $countQuery .= " AND visit_time >= ?";
    $params[] = $from_date . " 00:00:00";
    $countParams[] = $from_date . " 00:00:00";
}

if (!empty($to_date)) {
    $query .= " AND visit_time <= ?";
    $countQuery .= " AND visit_time <= ?";
    $params[] = $to_date . " 23:59:59";
    $countParams[] = $to_date . " 23:59:59";
}

// Count total logs for pagination
$stmt = $pdo->prepare($countQuery);
$stmt->execute($countParams);
$totalLogs = $stmt->fetchColumn();
$totalPages = max(1, ceil($totalLogs / $perPage));

// Append order, limit and offset
$query .= " ORDER BY visit_time DESC LIMIT ? OFFSET ?";

// Bind limit and offset parameters as integers
$stmt = $pdo->prepare($query);

// Bind the previous parameters first
$paramIndex = 1;
foreach ($params as $param) {
    $stmt->bindValue($paramIndex, $param);
    $paramIndex++;
}

// Bind LIMIT and OFFSET
$stmt->bindValue($paramIndex++, $perPage, PDO::PARAM_INT);
$stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);

// Execute query and fetch results
$stmt->execute();
$visitor_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats queries
$stmt = $pdo->query("SELECT COUNT(DISTINCT ip_address) FROM visitor_logs WHERE DATE(visit_time) = CURDATE()");
$unique_visits_today = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM visitor_logs");
$total_visits = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users"); // Adjust 'users' if needed
$registered_users = $stmt->fetchColumn();

// Delete log
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_log_id'])) {
    $deleteId = intval($_POST['delete_log_id']);
    $stmt = $pdo->prepare("DELETE FROM visitor_logs WHERE id = ?");
    $stmt->execute([$deleteId]);

    // Redirect to current page with query string intact (except POST)
    $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?');
    $queryString = $_SERVER['QUERY_STRING'] ?? '';
    header("Location: $redirectUrl" . ($queryString ? '?' . $queryString : ''));
    exit;
}
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
/* Tabs */
.tab-buttons {
  display: flex;
  gap: 16px;
  margin: 24px 0;
  justify-content: center;
}

.tab-btn {
  padding: 10px 20px;
  background: var(--bg-secondary);
  color: var(--text-secondary);
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: var(--transition);
  font-weight: 600;
}

.tab-btn:hover,
.tab-btn.active {
  background: var(--primary-blue);
  color: var(--text-primary);
  box-shadow: var(--shadow-glow);
}

/* Tab Content */
.tab-content {
  display: none;
  animation: fadeIn 0.5s ease-in-out;
}

.tab-content.active {
  display: block;
}

/* Dashboard Tables */
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  background: var(--bg-card);
  border-radius: var(--border-radius);
  overflow: hidden;
  box-shadow: var(--shadow-card);
  color: var(--text-secondary);
}

thead {
  background: var(--bg-tertiary);
  color: var(--text-primary);
}

th, td {
  padding: 14px 18px;
  text-align: left;
  border-bottom: 1px solid var(--border-color);
}

tbody tr:hover {
  background: var(--bg-secondary);
}

.icon-cell i {
  font-size: 1.2rem;
  color: var(--primary-blue);
}

.video-link {
  color: var(--primary-blue);
  text-decoration: underline;
}

.description-cell {
  max-width: 300px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Fade-in animation for content */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.styled-table {
  width: 100%;
  border-collapse: collapse;
  color: var(--text-primary);
  font-size: 14px;
}

.styled-table th,
.styled-table td {
  padding: 12px 16px;
  border-bottom: 1px solid var(--border-color);
  text-align: left;
}

.styled-table thead th {
  background: var(--bg-tertiary);
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 12px;
}

.styled-table tbody tr:hover {
  background: rgba(59, 130, 246, 0.1);
  transition: var(--transition);
}
.modal-overlay {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
      display: flex; align-items: center; justify-content: center;
    }
    .modal {
      background: white;
      padding: 20px;
      border-radius: 10px;
      min-width: 300px;
    }
    #edit-modal {
  max-width: 500px;
  width: 90%;
}

.btn-edit,
.btn-delete {
  display: inline-block;
  text-align: center;
  min-width: 80px;
  padding: 8px 14px;
  font-size: 0.9rem;
  font-weight: 600;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: transform 0.15s ease, background-color 0.3s ease;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
}

/* Edit Button */
.btn-edit {
  background-color: rgb(207, 142, 0);
  margin-right: 8px; /* <-- Adds spacing between edit & delete */
}
.btn-edit:hover {
  background-color: rgb(98, 196, 7);
  transform: scale(1.05);
}

/* Delete Button */
.btn-delete {
  background-color: #e74c3c;
}
.btn-delete:hover {
  background-color: #c0392b;
  transform: scale(1.05);
}

.tab-content {
  display: none;
}
.tab-content.active {
  display: block;
}
.tab-btn.active {
  background: var(--accent-green);
  color: white;
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
    <i class="fas fa-shield-alt"></i> CyberSecurity
  </a>

  <?php if (!empty($_SESSION['role']) && $_SESSION['role'] == 1): ?>
    <a href="dashboard.php" class="nav-link active">
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
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $table = $_POST['table'] ?? null;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $type = $_POST['type'] ?? '';
    $video_url = $_POST['video_url'] ?? '';

    if (!$id || !$table) {
        echo json_encode(["success" => false, "message" => "Missing ID or table."]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE `$table` SET title=?, description=?, type=?, video_url=?, updated_at=NOW() WHERE id=?");
        $success = $stmt->execute([$title, $description, $type, $video_url, $id]);

        if ($success && $stmt->rowCount() > 0) {
            echo json_encode(["success" => true, "message" => "Update Successful!"]);
        } else {
            echo json_encode(["success" => false, "message" => "No changes were made."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
}
?>


<div style="display: flex; min-height: 100vh;">
  <!-- Sidebar -->
  <aside style="width: 220px; background:linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #581c87 100%); padding: 24px; color: white;">
    <h2 style="margin-bottom: 32px; font-size: 1.5rem;">Admin Panel</h2>
    <nav style="display: flex; flex-direction: column; gap: 12px;">
      <button class="nav-btn" onclick="showSection('modules')">Manage Modules</button>
      <!-- <button class="nav-btn" onclick="showSection('analytics-tab')">Analytics</button> -->
      <button class="nav-btn" onclick="showSection('visitLog')">Visitors Log</button>
      <button class="nav-btn" onclick="showSection('manageUsers')">Manage Users</button>
      <button class="nav-btn" onclick="showSection('security')">Security Settings</button>
    </nav>
  </aside>

<!-- Main Content Area -->
<div style="flex: 1; padding: 24px; background: linear-gradient(135deg, #4e80cfff, #8b5cf6);">
  <div id="modules" class="admin-section" style="display: block;">
    <main class="container" style="padding: 24px;">

    
<script>
function openAddModuleModal() {
  document.getElementById('addModuleModal').style.display = 'flex';
}
function closeAddModuleModal() {
  document.getElementById('addModuleModal').style.display = 'none';
}
</script>

      <!-- Tab Buttons -->
      <div class="tab-buttons" style="display: flex; gap: 16px; margin-bottom: 24px;">
        <button class="tab-btn btn btn-outline active" onclick="showTab(event, 'module2')">Spot Phishing</button>
        <button class="tab-btn btn btn-outline" onclick="showTab(event, 'module1')">Recognizing Deepfakes</button>
        <button class="tab-btn btn btn-outline" onclick="showTab(event, 'module')">Securing Online Presence</button>
      </div>

      <!-- MODULE 3 (Spot Phishing) -->
      <div id="module2" class="tab-content" style="display: block;">
        <?php if (count($modules2) > 0): ?>
        <div class="card" style="background: var(--bg-card); border-radius: var(--border-radius); padding: 16px; box-shadow: var(--shadow-card); overflow-x: auto;">
          <table class="styled-table">
            <thead>
              <tr>
                <th>Icon</th>
                <th>Title</th>
                <th>Description</th>
                <th>Type</th>
                <th>Video</th>
                <th>Updated At</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($modules2 as $mod2): ?>
              <tr style="color: <?= htmlspecialchars($mod2['color'] ?? '#cbd5e1') ?>;">
                <td><i class="<?= htmlspecialchars($mod2['icon'] ?? 'fas fa-folder') ?>"></i></td>
                <td><?= htmlspecialchars($mod2['title']) ?></td>
                <td><?= htmlspecialchars($mod2['description']) ?></td>
                <td><?= htmlspecialchars($mod2['type'] ?? '-') ?></td>
                <td>
                  <?php if (!empty($mod2['video_url'])): ?>
                    <a href="<?= htmlspecialchars($mod2['video_url']) ?>" target="_blank" class="btn btn-secondary btn-sm">Watch</a>
                  <?php else: ?>-<?php endif; ?>
                </td>
                <td><?= htmlspecialchars($mod2['updated_at'] ?? '-') ?></td>
                <td>
                  <button onclick="editModule(<?= $mod2['id'] ?>, 'cybersecurity_modules2')" class="btn-edit">Edit</button>
                  <button onclick="deleteModule(<?= $mod2['id'] ?>, 'cybersecurity_modules2')" class="btn-delete">Delete</button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
          <p style="text-align:center;">No data found in cybersecurity_modules2.</p>
        <?php endif; ?>
      </div>

      <!-- MODULE 2 (Recognizing Deepfakes) -->
      <div id="module1" class="tab-content" style="display: none;">
        <?php if (count($modules1) > 0): ?>
        <div class="card" style="background: var(--bg-card); border-radius: var(--border-radius); padding: 16px; box-shadow: var(--shadow-card); overflow-x: auto;">
          <table class="styled-table">
            <thead>
              <tr>
                <th>Icon</th>
                <th>Title</th>
                <th>Description</th>
                <th>Type</th>
                <th>Video</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($modules1 as $mod1): ?>
              <tr style="color: <?= htmlspecialchars($mod1['color'] ?? '#cbd5e1') ?>;">
                <td><i class="<?= htmlspecialchars($mod1['icon'] ?? 'fas fa-folder') ?>"></i></td>
                <td><?= htmlspecialchars($mod1['title']) ?></td>
                <td><?= htmlspecialchars($mod1['description']) ?></td>
                <td><?= htmlspecialchars($mod1['type'] ?? '-') ?></td>
                <td>
                  <?php if (!empty($mod1['video_url'])): ?>
                    <a href="<?= htmlspecialchars($mod1['video_url']) ?>" target="_blank" class="btn btn-secondary btn-sm">Watch</a>
                  <?php else: ?>-<?php endif; ?>
                </td>
                <td>
                  <button onclick="editModule(<?= $mod1['id'] ?>, 'cybersecurity_modules1')" class="btn-edit">Edit</button>
                  <button onclick="deleteModule(<?= $mod1['id'] ?>, 'cybersecurity_modules1')" class="btn-delete">Delete</button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
          <p style="text-align:center;">No data found in cybersecurity_modules1.</p>
        <?php endif; ?>
      </div>

      <!-- MODULE 1 (Securing Online Presence) -->
      <div id="module" class="tab-content" style="display: none;">
        <?php if (count($modules) > 0): ?>
        <div class="card" style="background: var(--bg-card); border-radius: var(--border-radius); padding: 16px; box-shadow: var(--shadow-card); overflow-x: auto;">
          <table class="styled-table">
            <thead>
              <tr>
                <th>Icon</th>
                <th>Title</th>
                <th>Description</th>
                <th>Type</th>
                <th>Video</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($modules as $mod): ?>
              <tr>
                <td><i class="<?= htmlspecialchars($mod['icon_class'] ?? 'fas fa-folder') ?>"></i></td>
                <td><?= htmlspecialchars($mod['title']) ?></td>
                <td><?= htmlspecialchars($mod['description']) ?></td>
                <td><?= htmlspecialchars($mod['type']) ?></td>
                <td>
                  <?php if (!empty($mod['video_url'])): ?>
                    <a href="<?= htmlspecialchars($mod['video_url']) ?>" target="_blank" class="btn btn-secondary btn-sm">Watch</a>
                  <?php else: ?>-<?php endif; ?>
                </td>
                <td>
                  <button onclick="editModule(<?= $mod['id'] ?>, 'cybersecurity_modules')" class="btn-edit">Edit</button>
                  <button onclick="deleteModule(<?= $mod['id'] ?>, 'cybersecurity_modules')" class="btn-delete">Delete</button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
          <p style="text-align:center;">No active modules found.</p>
        <?php endif; ?>
      </div>
    </main>
  </div>

<script>
  function editModule(id, table) {
  // Fetch the module details first
  fetch(`get_module.php?id=${id}&table=${table}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        document.querySelector('#editModuleForm [name="id"]').value = data.module.id;
        document.querySelector('#editModuleForm [name="table"]').value = table;
        document.querySelector('#editModuleForm [name="title"]').value = data.module.title;
        document.querySelector('#editModuleForm [name="description"]').value = data.module.description;
        document.querySelector('#editModuleForm [name="type"]').value = data.module.type ?? '';
        document.querySelector('#editModuleForm [name="video_url"]').value = data.module.video_url ?? '';
        document.getElementById('editModuleModal').style.display = 'flex';
      } else {
        alert("Failed to load module data.");
      }
    })
    .catch(error => console.error('Error fetching module:', error));
}
  </script>

<script>
document.getElementById("editModuleForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch("update_module.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert(data.message);
      location.reload(); // Refresh page to see updated module
    } else {
      alert(data.message); // Will show exact reason
    }
  })
  .catch(error => alert("Error: " + error));
});
</script>

<script>
function showTab(event, tabId) {
  document.querySelectorAll('.tab-content').forEach(tab => {
    tab.style.display = 'none';
  });

  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.classList.remove('active');
  });

  document.getElementById(tabId).style.display = 'block';

  if (event) {
    event.currentTarget.classList.add('active');
  }
}

document.addEventListener("DOMContentLoaded", () => {
  showTab(null, "module2"); // Default tab
  document.querySelector(".tab-buttons button").classList.add("active");
});
</script>



<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  let articleBreakdownChart, articleTrendChart, articlePieChart;

  function showSection(id) {
    document.querySelectorAll('.admin-section').forEach(section => {
      section.style.display = 'none';
    });

    const el = document.getElementById(id);
    if (el) {
      el.style.display = 'block';

      if (id === 'analytics-tab') {
        setTimeout(() => {
          const breakdownCtx = document.getElementById('articleBreakdownChart')?.getContext('2d');
          const trendCtx = document.getElementById('articleTrendChart')?.getContext('2d');
          const pieCtx = document.getElementById('articlePieChart')?.getContext('2d');

          if (articleBreakdownChart) articleBreakdownChart.destroy();
          if (articleTrendChart) articleTrendChart.destroy();
          if (articlePieChart) articlePieChart.destroy();

          articleTrendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
              labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5'],
              datasets: [{
                label: 'Articles Analyzed',
                data: [10, 100, 18, 25, 27],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                tension: 0.3,
                fill: true
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              scales: {
                y: { beginAtZero: true }
              }
            }
          });

          articleBreakdownChart = new Chart(breakdownCtx, {
            type: 'bar',
            data: {
              labels: ['Total Articles', 'Fake News', 'Suspicious'],
              datasets: [{
                label: 'Article Analysis',
                data: [100, 35, 20],
                backgroundColor: ['#4ade80', '#f87171', '#facc15'],
                borderWidth: 1
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: true,
              scales: {
                y: { beginAtZero: true }
              }
            }
          });

          articlePieChart = new Chart(pieCtx, {
            type: 'pie',
            data: {
              labels: ['Fake News', 'Suspicious', 'Legitimate'],
              datasets: [{
                label: 'Distribution',
                data: [35, 20, 100],
                backgroundColor: ['#ef4444', '#facc15', '#4ade80']
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false
            }
          });

        }, 200);
      }
    }
  }
</script>

<!-- Visits Log Section -->
<div id="visitLog" class="admin-section" style="display: none;">
  <h2 style="margin-bottom: 24px;">
    <i class="fas fa-user-clock" style="color: #ff6f00; margin-right: 10px;"></i>
    Visit Log
  </h2>

  <!-- Stats Cards -->
  <div class="dashboard-cards" style="display: flex; gap: 24px; margin-bottom: 32px; flex-wrap: wrap;">
    <div class="stat-card" style="flex: 1; background:  linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #581c87 100%); padding: 20px; border-radius: 12px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
      <h3 style="margin: 0 0 8px;">Unique Visits Today</h3>
      <p style="font-size: 2rem; font-weight: bold;"><?= htmlspecialchars($unique_visits_today) ?></p>
    </div>

    <div class="stat-card" style="flex: 1; background:linear-gradient(135deg,rgb(141, 115, 201), #ec4899); padding: 20px; border-radius: 12px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
      <h3 style="margin: 0 0 8px;">Total Visits</h3>
      <p style="font-size: 2rem; font-weight: bold;"><?= htmlspecialchars($total_visits) ?></p>
    </div>

    <div class="stat-card" style="flex: 1; background:linear-gradient(135deg, #3b82f6, #8b5cf6); padding: 20px; border-radius: 12px; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
      <h3 style="margin: 0 0 8px;">Registered Users</h3>
      <p style="font-size: 2rem; font-weight: bold;"><?= htmlspecialchars($registered_users) ?></p>
    </div>
  </div>

  <!-- Filter Form -->
  <form method="GET" style="margin-bottom: 24px; display: flex; gap: 12px; flex-wrap: wrap;">
    <input type="text" name="ip" value="<?= htmlspecialchars($_GET['ip'] ?? '') ?>" placeholder="Search by email" style="padding: 8px; border-radius: 6px; border: 1px solid #ccc;">
    <input type="date" name="start_date" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>" style="padding: 8px; border-radius: 6px; border: 1px solid #ccc;">
    <input type="date" name="end_date" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>" style="padding: 8px; border-radius: 6px; border: 1px solid #ccc;">
    <button type="submit" style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 6px;">Filter</button>
  </form>

  <!-- Visits Table -->
  <div style="overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; background: #1e293b; border-radius: 8px; overflow: hidden; color:white;">
      <thead style="background: #334155; color: white;">
        <tr>
          <th style="padding: 12px; text-align: left;">User Email</th>
          <th style="padding: 12px; text-align: left;">Visited At</th>
          <th style="padding: 12px; text-align: left;">User Agent</th>
          <th style="padding: 12px; text-align: left;">Visit Time</th>
          <th style="padding: 12px; text-align: left;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($visitor_logs as $log): ?>
        <tr style="border-bottom: 1px solid #475569;">
          <td style="padding: 12px;"><?= htmlspecialchars($log['email']) ?></td>
          <td style="padding: 12px;"><?= htmlspecialchars($log['page_url']) ?></td>
          <td style="padding: 12px;"><?= htmlspecialchars($log['user_agent']) ?></td>
          <td style="padding: 12px;"><?= htmlspecialchars($log['visit_time']) ?></td>
          <td style="padding: 12px;">
            <form method="post" onsubmit="return confirm('Delete this log?');">
              <input type="hidden" name="delete_log_id" value="<?= $log['id'] ?>">
              <button type="submit" style="background: #dc2626; color: white; border: none; padding: 6px 12px; border-radius: 4px;">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php 
// Make sure tab param is present in links if it exists in $_GET
$tabParam = $_GET['tab'] ?? '';

// Merge tab + other GET params + page param
function buildPageLink($pageNum, $tabParam) {
    $params = $_GET;
    $params['page'] = $pageNum;
    if ($tabParam) {
        $params['tab'] = $tabParam;
    }
    return htmlspecialchars($_SERVER['PHP_SELF']) . '?' . http_build_query($params);
}
?>

<?php if ($totalPages > 1): ?>
  <div style="margin-top: 20px; display: flex; gap: 8px; flex-wrap: wrap;">
    <?php if ($page > 1): ?>
      <a href="<?= buildPageLink($page - 1, $tabParam) ?>" style="padding: 6px 12px; background: #4b5563; color: white; border-radius: 4px;">Previous</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <a href="<?= buildPageLink($i, $tabParam) ?>"
         style="padding: 6px 12px; <?= $i == $page ? 'background: #2563eb;' : 'background: #6b7280;' ?> color: white; border-radius: 4px;">
         <?= $i ?>
      </a>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
      <a href="<?= buildPageLink($page + 1, $tabParam) ?>" style="padding: 6px 12px; background: #4b5563; color: white; border-radius: 4px;">Next</a>
    <?php endif; ?>
  </div>
<?php endif; ?>

<!-- Export Button -->
  <div style="margin-top: 16px;">
    <a href="export_visits.php" style="display: inline-block; padding: 8px 16px; background: #10b981; color: white; border-radius: 6px; text-decoration: none;">Export as CSV</a>
  </div>
</div>

    <!-- Manage Users Section -->
<div id="manageUsers" class="admin-section" style="display: none;">
  <h2 style="margin-bottom: 24px;">
  <i class="fas fa-users-cog" style="color: #2196f3; margin-right: 10px;"></i>
  Manage Users
</h2>


  <!-- Filter/Search Form -->
  <form method="GET" style="margin-bottom: 24px; display: flex; gap: 12px; flex-wrap: wrap;">
    <input type="text" name="search_email" placeholder="Search by email" style="padding: 8px; border-radius: 6px; border: 1px solid #ccc;">
    <select name="search_role" style="padding: 8px; border-radius: 6px; border: 1px solid #ccc;">
      <option value="">All Roles</option>
      <option value="admin">Admin</option>
      <option value="user">User</option>
    </select>
    <button type="submit" style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 6px;">Filter</button>
  </form>

  <!-- Users Table -->
  <div style="overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse; background: --gradient-primary; border-radius: 8px; overflow: hidden;">
      <thead style="background: var(--bg-card); color: white;">
        <tr>
          <th style="padding: 12px;">Username</th>
          <th style="padding: 12px;">Email</th>
          <th style="padding: 12px;">Role</th>
          <th style="padding: 12px;">Registered</th>
          <th style="padding: 12px;">Created at</th>
          <th style="padding: 12px;">Actions</th>
        </tr>
      </thead>
      <tbody>
    <?php foreach ($users as $user): ?>
    <tr>
      <td><?= htmlspecialchars($user['id']) ?></td>
      <td><?= htmlspecialchars($user['username']) ?></td>
      <td><?= htmlspecialchars($user['email']) ?></td>
      <td><?= htmlspecialchars($user['role']) ?></td>
      <td><?= htmlspecialchars($user['created_at']) ?></td>
      <td>
        <a href="?edit=<?= htmlspecialchars($user['id']) ?>#manageUsers" style="margin-right: 8px;"class="btn-edit">Edit</a>
        <a href="?delete=<?= $user['id'] ?>" onclick="return confirm('Are you sure?')" class="btn-delete">Delete</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
    </table>
  </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal" style="display: none;">
  <div class="modal-content">
    <span class="close-button" onclick="closeEditUserModal()">&times;</span>
    <?php if ($editUser): ?>
      <h3 style="margin-bottom: 16px; color: var(--text-primary);">Edit User #<?= htmlspecialchars($editUser['id']) ?></h3>
      <form method="POST" action="?tab=manageUsers&edit=<?= htmlspecialchars($editUser['id']) ?>#manageUsers">
        <input type="hidden" name="id" value="<?= htmlspecialchars($editUser['id']) ?>">
        <label style="display: block; margin-bottom: 10px;">Username:
          <input type="text" name="username" value="<?= htmlspecialchars($editUser['username'] ?? '') ?>"
            style="width: 100%; padding: 10px; background: var(--bg-tertiary); border: none; border-radius: var(--border-radius); color: white;">
        </label>
        <label style="display: block; margin-bottom: 10px;">Email:
          <input type="email" name="email" value="<?= htmlspecialchars($editUser['email'] ?? '') ?>"
            style="width: 100%; padding: 10px; background: var(--bg-tertiary); border: none; border-radius: var(--border-radius); color: white;">
        </label>
        <label style="display: block; margin-bottom: 16px;">Role:
          <select name="role" required
            style="width: 100%; padding: 10px; background: var(--bg-tertiary); border: none; border-radius: var(--border-radius); color: white;">
            <option value="user" <?= ($editUser['role'] === 'user') ? 'selected' : '' ?>>User</option>
            <option value="admin" <?= ($editUser['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
          </select>
        </label>
        <div style="text-align: right;">
          <button type="button" onclick="closeEditUserModal()" style="margin-right: 10px; background: transparent; border: 1px solid var(--border-color); color: var(--text-muted); padding: 8px 16px; border-radius: var(--border-radius); cursor: pointer;">Cancel</button>
          <button type="submit" name="update_user" style="background: var(--gradient-primary); color: white; border: none; padding: 8px 16px; border-radius: var(--border-radius); cursor: pointer;">Update User</button>
        </div>
      </form>
    <?php else: ?>
      <p style="color: var(--text-muted);">User not found.</p>
    <?php endif; ?>
  </div>
</div>

<script>
function closeEditUserModal() {
  const modal = document.getElementById('editUserModal');
  modal.style.display = 'none';

  const url = new URL(window.location);
  url.searchParams.delete('edit');
  window.history.replaceState({}, document.title, url.toString());
}

document.addEventListener('DOMContentLoaded', () => {
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('edit')) {
    const modal = document.getElementById('editUserModal');
    if (modal) {
      modal.style.display = 'flex';
    }
    const section = document.getElementById('manageUsers');
    if (section) {
      section.scrollIntoView({ behavior: 'smooth' });
    }
  }
});
</script>

<style>
.modal {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 10000;
}

.modal-content {
  background: var(--bg-secondary);
  padding: 24px;
  border-radius: 8px;
  width: 500px;
  max-width: 90%;
  position: relative;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
  color: white;
}

.close-button {
  position: absolute;
  top: 12px;
  right: 16px;
  font-size: 28px;
  font-weight: bold;
  color: var(--text-muted);
  cursor: pointer;
  user-select: none;
}

.close-button:hover {
  color: white;
}

</style>

    <div id="security" class="admin-section" style="display: none;">
      <h2 style="margin-bottom: 24px;">
  <i class="fas fa-shield-alt" style="color: #e53935; margin-right: 10px;"></i>
  Security Settings
</h2>
      <p>Configure system and user-level security features.</p>
    </div>
  </div>
</div>

<style>
.nav-btn {
  padding: 10px 16px;
  background-color: #334155;
  border: none;
  color: white;
  border-radius: 8px;
  text-align: left;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.nav-btn:hover {
  background-color: #475569;
}
</style>

<!-- Modal Overlay -->
<div id="modal-overlay" style="
  display:none; 
  position:fixed; 
  top:0; left:0; 
  width:100%; height:100%; 
  background:rgba(0,0,0,0.3); 
  z-index:9998;"></div>

<!-- Edit Modal -->
<div id="edit-modal" style="
  display:none; 
  position:fixed; 
  top:50%; left:50%; 
  transform: translate(-50%, -50%);
  width: 90%;
  max-width: 600px;
  background: white;
  border-radius: 20px;
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
  padding: 32px 40px;
  z-index: 9999;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #1a1a1a;
  overflow-y: auto;
  max-height: 80vh;">

  <form id="edit-form" onsubmit="return false;">
    <input type="hidden" name="id" id="edit-id">
    <input type="hidden" name="table" id="edit-table">

    <h2 style="
      font-size: 28px; 
      color: #6c63ff; 
      margin-top: 0; 
      margin-bottom: 24px; 
      font-weight: 700;
      text-align: center;">
      Edit Cybersecurity Module
    </h2>

    <label style="display:block; margin-bottom: 16px; font-weight: 600; color: #444;">
      Title:
      <input type="text" name="title" id="edit-title" required
        style="
          width: 100%; 
          padding: 14px 16px; 
          margin-top: 6px; 
          border: 1.5px solid #ccc; 
          border-radius: 15px; 
          font-size: 16px; 
          transition: border-color 0.3s ease;"onfocus="this.style.borderColor='#6c63ff';" onblur="this.style.borderColor='#ccc';">
    </label>

    <label style="display:block; margin-bottom: 16px; font-weight: 600; color: #444;">
      Description:
      <textarea name="description" id="edit-description" required rows="4"
        style="
          width: 100%; 
          padding: 14px 16px; 
          margin-top: 6px; 
          border: 1.5px solid #ccc; 
          background: white;
          border-radius: 15px; 
          font-size: 16px; 
          resize: vertical;
          color: black;
          transition: border-color 0.3s ease;
        "
        onfocus="this.style.borderColor='#6c63ff';" 
        onblur="this.style.borderColor='#ccc';"
      ></textarea>
    </label>

    <label style="display:block; margin-bottom: 16px; font-weight: 600; color: #444;">
      Type:
      <input type="text" name="type" id="edit-type" required
        style="
          width: 100%; 
          padding: 14px 16px; 
          margin-top: 6px; 
          border: 1.5px solid #ccc; 
          border-radius: 15px; 
          font-size: 16px; 
          transition: border-color 0.3s ease;
        "
        onfocus="this.style.borderColor='#6c63ff';" 
        onblur="this.style.borderColor='#ccc';"
      >
    </label>

    <label style="display:block; margin-bottom: 16px; font-weight: 600; color: #444;">
      Subtype:
      <input type="text" name="subtype" id="edit-subtype"
        style="
          width: 100%; 
          padding: 14px 16px; 
          margin-top: 6px; 
          border: 1.5px solid #ccc; 
          border-radius: 15px; 
          font-size: 16px; 
          transition: border-color 0.3s ease;
        "
        onfocus="this.style.borderColor='#6c63ff';" 
        onblur="this.style.borderColor='#ccc';"
      >
    </label>

    <label style="display:block; margin-bottom: 32px; font-weight: 600; color: #444;">
      Video URL:
      <input type="text" name="video_url" id="edit-video_url"
        style="
          width: 100%; 
          padding: 14px 16px; 
          margin-top: 6px; 
          border: 1.5px solid #ccc; 
          border-radius: 15px; 
          font-size: 16px; 
          transition: border-color 0.3s ease;
        "
        onfocus="this.style.borderColor='#6c63ff';" 
        onblur="this.style.borderColor='#ccc';">
    </label>

    <div style="text-align: center;">
      <button type="submit" class="btn-save" style="
        background: linear-gradient(90deg, #6c63ff, #9b59b6);
        border: none;
        padding: 14px 40px;
        border-radius: 15px;
        color: white;
        font-weight: 700;
        font-size: 18px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(108, 99, 255, 0.4);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        margin-right: 16px;"onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 6px 16px rgba(108, 99, 255, 0.6)';"onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 12px rgba(108, 99, 255, 0.4)';">
        Save
      </button>
      <button type="button" onclick="closeModal()" class="btn-cancel" style="
        background-color: #eee;
        border: none;
        padding: 14px 40px;
        border-radius: 15px;
        font-weight: 600;
        font-size: 18px;
        cursor: pointer;
        color: #666;
        transition: background 0.2s ease;
      "
      onmouseover="this.style.background='#ddd';"
      onmouseout="this.style.background='#eee';"
      >
        Cancel
      </button>
    </div>
  </form>
</div>

<script>
function editModule(id, table) {
  fetch(`get_module.php?id=${id}&table=${table}`)
    .then(response => response.json())
    .then(data => {
      if (data.error) throw new Error(data.error);

      // Fill modal form
      document.getElementById('edit-id').value = data.id;
      document.getElementById('edit-title').value = data.title;
      document.getElementById('edit-description').value = data.description;
      document.getElementById('edit-type').value = data.type;
      document.getElementById('edit-subtype').value = data.subtype;
      document.getElementById('edit-video_url').value = data.video_url || '';
      document.getElementById('edit-table').value = table;

      // ✅ Make modal visible in the center
      document.getElementById('modal-overlay').style.display = 'flex';
      document.getElementById('edit-modal').style.display = 'block';
    })
    .catch(error => {
      alert("Failed to load module data.");
      console.error(error);
    });
}

function closeModal() {
  document.getElementById('modal-overlay').style.display = 'none';
  document.getElementById('edit-modal').style.display = 'none';

  // Optional: Remove edit param from URL
  const url = new URL(window.location);
  url.searchParams.delete('edit');
  window.history.replaceState({}, document.title, url.toString());

}

document.getElementById('edit-form').addEventListener('submit', function (e) {
  e.preventDefault();
  const formData = new FormData(this);
  fetch('update_module.php', {
    method: 'POST',
    body: formData
  }).then(response => response.text()).then(result => {
    if (result.trim() === 'success') {
      location.reload();
    } else {
      alert('Update failed!');
    }
  });
});

function deleteModule(id, table) {
  if (confirm("Are you sure you want to delete this module?")) {
    fetch('delete_module.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id=${id}&table=${table}`
    }).then(response => response.text()).then(result => {
      if (result.trim() === 'success') {
        location.reload();
      } else {
        alert('Delete failed!');
      }
    });
  }
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
document.addEventListener("DOMContentLoaded", function () {
  // Activate the tab stored in the URL hash
  const hash = window.location.hash;
  if (hash) {
    const triggerEl = document.querySelector(`a[href="${hash}"]`);
    if (triggerEl) {
      const tab = new bootstrap.Tab(triggerEl);
      tab.show();
    }
  }

  // When clicking a tab, update the hash in the URL
  document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tabLink => {
    tabLink.addEventListener('shown.bs.tab', function (event) {
      history.replaceState(null, null, event.target.getAttribute('href'));
    });
  });
});
</script>
</main> 
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
<script src="script.js"></script>
</body>
</html>
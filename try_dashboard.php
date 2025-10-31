<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>VeriFact Admin Panel</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 12px 20px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .active-tab {
            background-color: #495057;
        }
        .content {
            padding: 20px;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar flex-shrink-0 p-3">
        <h4 class="text-center mb-4">VeriFact Admin</h4>
        <a href="#" class="tab-link active-tab" data-tab="dashboard"><i class="fas fa-home me-2"></i> Dashboard</a>
        <a href="#" class="tab-link" data-tab="modules"><i class="fas fa-tools me-2"></i> Modules</a>
        <a href="#" class="tab-link" data-tab="analytics"><i class="fas fa-chart-line me-2"></i> Analytics</a>
        <a href="#" class="tab-link" data-tab="visitors"><i class="fas fa-user-clock me-2"></i> Visitor Logs</a>
        <a href="#" class="tab-link" data-tab="users"><i class="fas fa-users me-2"></i> Users</a>
    </div>

    <!-- Content -->
    <div class="content flex-grow-1">
        <!-- Dashboard Tab -->
        <div id="dashboard" class="tab-content active">
            <h2>Dashboard</h2>
            <p>Welcome to the VeriFact Admin Dashboard!</p>
        </div>

        <!-- Modules Tab -->
        <div id="modules" class="tab-content">
            <h2>Modules</h2>
            <ul class="nav nav-tabs mb-3" id="moduleTabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#textModule">Text</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#imageModule">Image</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#audioModule">Audio</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="textModule">
                    <?php include 'modules/text.php'; ?>
                </div>
                <div class="tab-pane fade" id="imageModule">
                    <?php include 'modules/image.php'; ?>
                </div>
                <div class="tab-pane fade" id="audioModule">
                    <?php include 'modules/audio.php'; ?>
                </div>
            </div>
        </div>

        <!-- Analytics Tab -->
        <div id="analytics" class="tab-content">
            <h2>Analytics</h2>
            <?php include 'analytics.php'; ?>
        </div>

        <!-- Visitor Logs Tab -->
        <div id="visitors" class="tab-content">
            <h2>Visitor Logs</h2>
            <?php include 'visitors.php'; ?>
        </div>

        <!-- Users Tab -->
        <div id="users" class="tab-content">
            <h2>Manage Users</h2>
            <?php include 'users.php'; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function () {
        $(".tab-link").click(function (e) {
            e.preventDefault();
            $(".tab-link").removeClass("active-tab");
            $(this).addClass("active-tab");

            const target = $(this).data("tab");
            $(".tab-content").removeClass("active");
            $("#" + target).addClass("active");
        });
    });
</script>

</body>
</html>

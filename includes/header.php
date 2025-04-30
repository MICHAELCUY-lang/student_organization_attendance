// includes/header.php
<?php
// Authentication check
requireLogin();

// Get current page for active menu
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?><?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/datatables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/images/favicon.ico" type="image/x-icon">
    <style>
        :root {
            --sidebar-width: 250px;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        .content-wrapper {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s;
            min-height: 100vh;
            padding: 15px;
            padding-top: 75px;
        }
        .navbar {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s;
            z-index: 99;
        }
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: var(--sidebar-width);
            z-index: 100;
            padding: 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #343a40;
            transition: width 0.3s;
        }
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh);
            padding-top: 0.5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .sidebar .nav-link {
            font-weight: 500;
            color: rgba(255, 255, 255, 0.75);
            padding: 0.75rem 1rem;
            transition: all 0.2s;
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.2);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
            color: rgba(255, 255, 255, 0.5);
        }
        .sidebar .nav-link.active i {
            color: #fff;
        }
        .sidebar-heading {
            font-size: .75rem;
            text-transform: uppercase;
            padding: 0.75rem 1rem;
            color: rgba(255, 255, 255, 0.5);
        }
        .sidebar-brand {
            padding: 1rem;
            background-color: #212529;
            color: #fff;
            font-size: 1.2rem;
            font-weight: bold;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 56px;
        }
        .sidebar-brand img {
            max-height: 30px;
            margin-right: 10px;
        }
        .navbar-toggler {
            padding: 0.25rem 0.75rem;
            font-size: 1.25rem;
            line-height: 1;
            background-color: transparent;
            border: 1px solid transparent;
            border-radius: 0.25rem;
        }
        .sidebar-toggle {
            padding: 0.25rem 0.75rem;
            margin-right: 1rem;
            color: #fff;
            background-color: transparent;
            border: none;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
            border: none;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            border-top-left-radius: 0.5rem !important;
            border-top-right-radius: 0.5rem !important;
            font-weight: 600;
        }
        .btn {
            border-radius: 0.25rem;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-success {
            background-color: #198754;
            border-color: #198754;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
        .btn-info {
            background-color: #0dcaf0;
            border-color: #0dcaf0;
            color: #212529;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
            }
            .navbar, .content-wrapper {
                margin-left: 0;
            }
            .sidebar.show {
                width: var(--sidebar-width);
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <button class="sidebar-toggle d-md-none" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            
            <span class="navbar-brand d-none d-md-block"><?= isset($pageTitle) ? $pageTitle : APP_NAME ?></span>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> <?= $_SESSION['username'] ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/modules/users/edit.php?id=<?= $_SESSION['user_id'] ?>"><i class="bi bi-gear"></i> Profile Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <main class="content-wrapper">
        <div class="container-fluid">

// includes/sidebar.php
<div class="sidebar">
    <div class="sidebar-brand">
        <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="Logo"> <?= substr(APP_NAME, 0, 12) ?>...
    </div>
    <div class="sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            
            <div class="sidebar-heading">Management</div>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentPage, 'attendance') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/modules/attendance/list.php">
                    <i class="bi bi-calendar-check"></i> Attendance
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentPage, 'members') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/modules/members/list.php">
                    <i class="bi bi-people"></i> Members
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentPage, 'meetings') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/modules/meetings/list.php">
                    <i class="bi bi-calendar-event"></i> Meetings
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentPage, 'documents') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/modules/documents/list.php">
                    <i class="bi bi-file-earmark-text"></i> Documents
                </a>
            </li>
            
            <?php if (hasRole('admin')): ?>
            <div class="sidebar-heading">Administration</div>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentPage, 'divisions') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/modules/divisions/list.php">
                    <i class="bi bi-diagram-3"></i> Divisions
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentPage, 'users') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/modules/users/list.php">
                    <i class="bi bi-person-badge"></i> Users
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= strpos($currentPage, 'report') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/modules/attendance/report.php">
                    <i class="bi bi-bar-chart"></i> Reports
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</div>

// includes/footer.php
        </div>
    </main>

    <script src="<?= BASE_URL ?>/assets/js/jquery.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/datatables.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/script.js"></script>
    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });

        // Initialize DataTables
        $(document).ready(function() {
            $('.datatable').DataTable({
                responsive: true,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search..."
                }
            });

            // Delete confirmation
            $('.btn-delete').on('click', function(e) {
                if (!confirm('Are you sure you want to delete this item?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
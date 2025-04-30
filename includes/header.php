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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/images/favicon.ico" type="image/x-icon">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="Logo">
        </div>
        
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="<?= BASE_URL ?>/dashboard.php" class="sidebar-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <div class="sidebar-heading">MANAGEMENT</div>
            
            <li class="sidebar-item">
                <a href="<?= BASE_URL ?>/modules/attendance/list.php" class="sidebar-link <?= strpos($currentPage, 'attendance') !== false ? 'active' : '' ?>">
                    <i class="bi bi-calendar-check"></i>
                    <span>Attendance</span>
                </a>
            </li>
            
            <li class="sidebar-item">
                <a href="<?= BASE_URL ?>/modules/members/list.php" class="sidebar-link <?= strpos($currentPage, 'members') !== false ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>Members</span>
                </a>
            </li>
            
            <li class="sidebar-item">
                <a href="<?= BASE_URL ?>/modules/meetings/list.php" class="sidebar-link <?= strpos($currentPage, 'meetings') !== false ? 'active' : '' ?>">
                    <i class="bi bi-calendar-event"></i>
                    <span>Meetings</span>
                </a>
            </li>
            
            <li class="sidebar-item">
                <a href="<?= BASE_URL ?>/modules/documents/list.php" class="sidebar-link <?= strpos($currentPage, 'documents') !== false ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Documents</span>
                </a>
            </li>
            
            <?php if (hasRole('admin')): ?>
            <div class="sidebar-heading">ADMINISTRATION</div>
            
            <li class="sidebar-item">
                <a href="<?= BASE_URL ?>/modules/divisions/list.php" class="sidebar-link <?= strpos($currentPage, 'divisions') !== false ? 'active' : '' ?>">
                    <i class="bi bi-diagram-3"></i>
                    <span>Divisions</span>
                </a>
            </li>
            
            <li class="sidebar-item">
                <a href="<?= BASE_URL ?>/modules/users/list.php" class="sidebar-link <?= strpos($currentPage, 'users') !== false ? 'active' : '' ?>">
                    <i class="bi bi-person-badge"></i>
                    <span>Users</span>
                </a>
            </li>
            
            <li class="sidebar-item">
                <a href="<?= BASE_URL ?>/modules/attendance/report.php" class="sidebar-link <?= strpos($currentPage, 'report') !== false ? 'active' : '' ?>">
                    <i class="bi bi-bar-chart"></i>
                    <span>Reports</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </aside>

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid px-4">
            <button class="btn btn-sm btn-outline-secondary d-lg-none me-3" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            
            <div class="d-none d-lg-block">
                <h5 class="mb-0"><?= isset($pageTitle) ? $pageTitle : APP_NAME ?></h5>
            </div>
            
            <div class="ms-auto d-flex align-items-center">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar avatar-sm bg-primary me-2 d-flex align-items-center justify-content-center rounded-circle">
                            <span class="text-white"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></span>
                        </div>
                        <span class="d-none d-md-inline me-1"><?= $_SESSION['username'] ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/modules/users/edit.php?id=<?= $_SESSION['user_id'] ?>">
                            <i class="bi bi-gear me-2"></i>Settings
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="content-wrapper">
        <?php displayFlashMessage('flash_message'); ?>
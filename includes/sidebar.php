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
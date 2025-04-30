<aside class="sidebar">
    <div class="sidebar-brand">
        <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="Logo">
        <span><?= substr(APP_NAME, 0, 14) ?></span>
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
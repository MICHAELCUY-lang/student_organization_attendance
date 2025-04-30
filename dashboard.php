<?php
// dashboard.php
require_once 'config/config.php';
require_once 'includes/auth.php';

// Set page title
$pageTitle = 'Dashboard';

// Get total members
$membersQuery = "SELECT COUNT(*) as total FROM members";
$membersResult = $conn->query($membersQuery);
$totalMembers = $membersResult ? $membersResult->fetch_assoc()['total'] : 0;

// Get total meetings
$meetingsQuery = "SELECT COUNT(*) as total FROM meetings";
$meetingsResult = $conn->query($meetingsQuery);
$totalMeetings = $meetingsResult ? $meetingsResult->fetch_assoc()['total'] : 0;

// Get total divisions
$divisionsQuery = "SELECT COUNT(*) as total FROM divisions";
$divisionsResult = $conn->query($divisionsQuery);
$totalDivisions = $divisionsResult ? $divisionsResult->fetch_assoc()['total'] : 0;

// Get total attendances
$attendancesQuery = "SELECT COUNT(*) as total FROM attendance";
$attendancesResult = $conn->query($attendancesQuery);
$totalAttendances = $attendancesResult ? $attendancesResult->fetch_assoc()['total'] : 0;

// Get attendance statistics
$attendanceStatsQuery = "
    SELECT 
        SUM(CASE WHEN status_kehadiran = 'hadir' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN status_kehadiran = 'izin' THEN 1 ELSE 0 END) as excused,
        SUM(CASE WHEN status_kehadiran = 'alpa' THEN 1 ELSE 0 END) as absent,
        SUM(CASE WHEN status_kehadiran = 'telat' THEN 1 ELSE 0 END) as late,
        COUNT(*) as total
    FROM 
        attendance
";
$attendanceStatsResult = $conn->query($attendanceStatsQuery);
$attendanceStats = $attendanceStatsResult ? $attendanceStatsResult->fetch_assoc() : [
    'present' => 0, 'excused' => 0, 'absent' => 0, 'late' => 0, 'total' => 0
];

// Prepare chart data
$chartData = json_encode([
    'present' => (int)$attendanceStats['present'],
    'excused' => (int)$attendanceStats['excused'],
    'absent' => (int)$attendanceStats['absent'],
    'late' => (int)$attendanceStats['late']
]);

// Get monthly attendance trend
$trendQuery = "
    SELECT 
        DATE_FORMAT(m.tanggal, '%b %Y') as month,
        COUNT(DISTINCT m.meeting_id) as total_meetings,
        COUNT(a.attendance_id) as total_attendances,
        SUM(CASE WHEN a.status_kehadiran IN ('hadir', 'telat') THEN 1 ELSE 0 END) as present_count,
        ROUND((SUM(CASE WHEN a.status_kehadiran IN ('hadir', 'telat') THEN 1 ELSE 0 END) / COUNT(a.attendance_id)) * 100, 1) as rate
    FROM 
        meetings m
    LEFT JOIN 
        attendance a ON m.meeting_id = a.meeting_id
    GROUP BY 
        month
    ORDER BY 
        m.tanggal DESC
    LIMIT 6
";
$trendResult = $conn->query($trendQuery);
$trendData = [
    'labels' => [],
    'values' => []
];

if ($trendResult && $trendResult->num_rows > 0) {
    while ($row = $trendResult->fetch_assoc()) {
        array_unshift($trendData['labels'], $row['month']);
        array_unshift($trendData['values'], $row['rate'] ? $row['rate'] : 0);
    }
}
$trendDataJson = json_encode($trendData);

// Get recent meetings (last 5)
$recentMeetingsQuery = "
    SELECT 
        m.*, 
        COUNT(a.attendance_id) as attendance_count,
        SUM(CASE WHEN a.status_kehadiran = 'hadir' THEN 1 ELSE 0 END) as present_count,
        SUM(CASE WHEN a.status_kehadiran = 'izin' THEN 1 ELSE 0 END) as excused_count,
        SUM(CASE WHEN a.status_kehadiran = 'alpa' THEN 1 ELSE 0 END) as absent_count,
        SUM(CASE WHEN a.status_kehadiran = 'telat' THEN 1 ELSE 0 END) as late_count
    FROM 
        meetings m
    LEFT JOIN 
        attendance a ON m.meeting_id = a.meeting_id
    GROUP BY 
        m.meeting_id
    ORDER BY 
        m.tanggal DESC, m.waktu_mulai DESC
    LIMIT 5
";
$recentMeetings = $conn->query($recentMeetingsQuery);

// Get attendance by division
$attendanceByDivisionQuery = "
    SELECT 
        d.division_id,
        d.nama_divisi,
        COUNT(DISTINCT m.member_id) as total_members,
        COUNT(a.attendance_id) as total_records,
        SUM(CASE WHEN a.status_kehadiran IN ('hadir', 'telat') THEN 1 ELSE 0 END) as total_present,
        ROUND((SUM(CASE WHEN a.status_kehadiran IN ('hadir', 'telat') THEN 1 ELSE 0 END) / COUNT(a.attendance_id)) * 100, 1) as attendance_rate
    FROM 
        divisions d
    LEFT JOIN 
        members m ON d.division_id = m.division_id
    LEFT JOIN 
        attendance a ON m.member_id = a.member_id
    GROUP BY 
        d.division_id
    ORDER BY 
        d.nama_divisi ASC
";
$attendanceByDivision = $conn->query($attendanceByDivisionQuery);

// Prepare data for division comparison chart
$divisionData = [
    'divisions' => [],
    'rates' => [],
    'colors' => []
];

$colorPalette = ['#4361ee', '#4cc9f0', '#06d6a0', '#f9c74f', '#ef476f', '#7209b7', '#3a0ca3', '#f72585'];
$colorIndex = 0;

if ($attendanceByDivision && $attendanceByDivision->num_rows > 0) {
    $attendanceByDivision->data_seek(0); // Reset pointer
    while ($division = $attendanceByDivision->fetch_assoc()) {
        $divisionData['divisions'][] = $division['nama_divisi'];
        $divisionData['rates'][] = $division['attendance_rate'] ? $division['attendance_rate'] : 0;
        $divisionData['colors'][] = $colorPalette[$colorIndex % count($colorPalette)];
        $colorIndex++;
    }
}
$divisionDataJson = json_encode($divisionData);

// Include header
include_once 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Dashboard</h1>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" id="refreshDashboard">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <!-- Total Members Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="dashboard-stat-card">
            <div class="card-body">
                <h5 class="card-title">TOTAL MEMBERS</h5>
                <div class="card-value counter-value"><?= $totalMembers ?></div>
                <p class="card-text text-muted mb-0">Registered organization members</p>
                <i class="bi bi-people-fill card-icon"></i>
            </div>
            <div class="card-accent card-accent-primary"></div>
        </div>
    </div>

    <!-- Total Meetings Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="dashboard-stat-card">
            <div class="card-body">
                <h5 class="card-title">TOTAL MEETINGS</h5>
                <div class="card-value counter-value"><?= $totalMeetings ?></div>
                <p class="card-text text-muted mb-0">Scheduled organization meetings</p>
                <i class="bi bi-calendar-event-fill card-icon"></i>
            </div>
            <div class="card-accent card-accent-success"></div>
        </div>
    </div>

    <!-- Total Divisions Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="dashboard-stat-card">
            <div class="card-body">
                <h5 class="card-title">TOTAL DIVISIONS</h5>
                <div class="card-value counter-value"><?= $totalDivisions ?></div>
                <p class="card-text text-muted mb-0">Organization divisions/departments</p>
                <i class="bi bi-diagram-3-fill card-icon"></i>
            </div>
            <div class="card-accent card-accent-info"></div>
        </div>
    </div>

    <!-- Total Attendances Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="dashboard-stat-card">
            <div class="card-body">
                <h5 class="card-title">ATTENDANCE RECORDS</h5>
                <div class="card-value counter-value"><?= $totalAttendances ?></div>
                <p class="card-text text-muted mb-0">Total attendance entries recorded</p>
                <i class="bi bi-clipboard-check-fill card-icon"></i>
            </div>
            <div class="card-accent card-accent-warning"></div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Attendance Overview Chart -->
    <div class="col-xl-4 col-lg-5 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Attendance Overview</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 240px;">
                    <canvas id="attendanceOverviewChart" data-chart='<?= $chartData ?>'></canvas>
                </div>
                
                <div class="row text-center mt-4">
                    <div class="col-3">
                        <div class="attendance-stat">
                            <h6 class="small text-muted mb-1">Present</h6>
                            <h5 class="mb-0"><?= $attendanceStats['present'] ?: 0 ?></h5>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="attendance-stat">
                            <h5 class="mb-0"><?= $attendanceStats['late'] ?: 0 ?></h5>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="attendance-stat">
                            <h6 class="small text-muted mb-1">Excused</h6>
                            <h5 class="mb-0"><?= $attendanceStats['excused'] ?: 0 ?></h5>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="attendance-stat">
                            <h6 class="small text-muted mb-1">Absent</h6>
                            <h5 class="mb-0"><?= $attendanceStats['absent'] ?: 0 ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Attendance Trend Chart -->
    <div class="col-xl-8 col-lg-7 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Attendance Trend</h5>
                <div class="card-actions">
                    <button class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-calendar3"></i> Monthly
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 240px;">
                    <canvas id="attendanceTrendChart" data-chart='<?= $trendDataJson ?>'></canvas>
                </div>
                
                <div class="d-flex justify-content-center align-items-center mt-2">
                    <div class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Shows average attendance rate over time
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Meetings -->
    <div class="col-xl-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Meetings</h5>
                <a href="<?= BASE_URL ?>/modules/meetings/list.php" class="btn btn-sm btn-primary">
                    <i class="bi bi-list"></i> View All
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Meeting</th>
                                <th>Date</th>
                                <th>Attendance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentMeetings && $recentMeetings->num_rows > 0): ?>
                                <?php while ($meeting = $recentMeetings->fetch_assoc()): ?>
                                    <?php 
                                    $attendanceRate = $meeting['attendance_count'] > 0 
                                        ? round(($meeting['present_count'] + $meeting['late_count']) / $meeting['attendance_count'] * 100) 
                                        : 0;
                                    ?>
                                    <tr>
                                        <td class="fw-medium"><?= $meeting['judul_rapat'] ?></td>
                                        <td><?= formatDate($meeting['tanggal']) ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                    <div class="progress-bar bg-success" role="progressbar" 
                                                        style="width: <?= $attendanceRate ?>%" 
                                                        aria-valuenow="<?= $attendanceRate ?>" 
                                                        aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <span class="text-muted small"><?= $attendanceRate ?>%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <a href="<?= BASE_URL ?>/modules/attendance/list.php?meeting_id=<?= $meeting['meeting_id'] ?>" 
                                                   class="btn btn-sm btn-outline-info me-1" data-bs-toggle="tooltip" title="View Attendance">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>/modules/attendance/add.php?meeting_id=<?= $meeting['meeting_id'] ?>"
                                                   class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Record Attendance">
                                                    <i class="bi bi-plus-circle"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">No meetings found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance by Division -->
    <div class="col-xl-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Attendance by Division</h5>
                <a href="<?= BASE_URL ?>/modules/attendance/report.php" class="btn btn-sm btn-primary">
                    <i class="bi bi-file-earmark-text"></i> Full Report
                </a>
            </div>
            <div class="card-body">
                <div class="chart-container mb-3" style="position: relative; height: 200px;">
                    <canvas id="divisionComparisonChart" data-chart='<?= $divisionDataJson ?>'></canvas>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0">
                        <thead>
                            <tr class="text-muted small">
                                <th>Division</th>
                                <th>Members</th>
                                <th>Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($attendanceByDivision && $attendanceByDivision->num_rows > 0): ?>
                                <?php 
                                $attendanceByDivision->data_seek(0); // Reset pointer
                                while ($division = $attendanceByDivision->fetch_assoc()): 
                                ?>
                                    <tr>
                                        <td class="fw-medium"><?= $division['nama_divisi'] ?></td>
                                        <td><?= $division['total_members'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: a6px;">
                                                    <div class="progress-bar <?= $division['attendance_rate'] >= 75 ? 'bg-success' : ($division['attendance_rate'] >= 50 ? 'bg-warning' : 'bg-danger') ?>" 
                                                        role="progressbar" 
                                                        style="width: <?= $division['attendance_rate'] ?>%" 
                                                        aria-valuenow="<?= $division['attendance_rate'] ?>" 
                                                        aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <span class="text-muted small"><?= $division['attendance_rate'] ?>%</span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">No data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <a href="<?= BASE_URL ?>/modules/attendance/add.php" class="btn btn-primary d-flex align-items-center justify-content-center p-3 w-100 h-100">
                            <div class="text-center">
                                <i class="bi bi-clipboard-plus d-block mb-2" style="font-size: 1.75rem;"></i>
                                <span>Record Attendance</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="<?= BASE_URL ?>/modules/members/add.php" class="btn btn-success d-flex align-items-center justify-content-center p-3 w-100 h-100">
                            <div class="text-center">
                                <i class="bi bi-person-plus d-block mb-2" style="font-size: 1.75rem;"></i>
                                <span>Add New Member</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="<?= BASE_URL ?>/modules/meetings/add.php" class="btn btn-info d-flex align-items-center justify-content-center p-3 w-100 h-100">
                            <div class="text-center">
                                <i class="bi bi-calendar-plus d-block mb-2" style="font-size: 1.75rem;"></i>
                                <span>Schedule Meeting</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="<?= BASE_URL ?>/modules/documents/upload.php" class="btn btn-warning d-flex align-items-center justify-content-center p-3 w-100 h-100">
                            <div class="text-center">
                                <i class="bi bi-file-earmark-arrow-up d-block mb-2" style="font-size: 1.75rem;"></i>
                                <span>Upload Document</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Refresh dashboard on button click
    document.getElementById('refreshDashboard').addEventListener('click', function() {
        location.reload();
    });
</script>

<?php include_once 'includes/footer.php'; ?>
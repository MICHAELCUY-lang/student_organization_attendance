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

// Get recent meetings (last 5)
$recentMeetingsQuery = "
    SELECT * 
    FROM meetings 
    ORDER BY tanggal DESC, waktu_mulai DESC 
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
        SUM(CASE WHEN a.status_kehadiran = 'hadir' OR a.status_kehadiran = 'telat' THEN 1 ELSE 0 END) as total_present,
        SUM(CASE WHEN a.status_kehadiran = 'izin' THEN 1 ELSE 0 END) as total_excused,
        SUM(CASE WHEN a.status_kehadiran = 'alpa' THEN 1 ELSE 0 END) as total_absent
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

// Include header
include_once 'includes/header.php';
?>

<!-- Dashboard Content -->
<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="h3 mb-3">Dashboard</h1>
        <div class="row">
            <!-- Total Members Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Members</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalMembers ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people fa-2x text-gray-300" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Meetings Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Meetings</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalMeetings ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-calendar-event fa-2x text-gray-300" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Divisions Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Divisions</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalDivisions ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-diagram-3 fa-2x text-gray-300" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Attendances Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Total Attendances</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalAttendances ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-calendar-check fa-2x text-gray-300" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Meetings -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Recent Meetings</h6>
                <a href="<?= BASE_URL ?>/modules/meetings/list.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentMeetings && $recentMeetings->num_rows > 0): ?>
                                <?php while ($meeting = $recentMeetings->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $meeting['judul_rapat'] ?></td>
                                        <td><?= formatDate($meeting['tanggal']) ?></td>
                                        <td><?= formatTime($meeting['waktu_mulai']) ?> - <?= formatTime($meeting['waktu_selesai']) ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/modules/attendance/list.php?meeting_id=<?= $meeting['meeting_id'] ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i> Attendance
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No meetings found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance by Division -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">Attendance by Division</h6>
                <a href="<?= BASE_URL ?>/modules/attendance/report.php" class="btn btn-sm btn-primary">Full Report</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Division</th>
                                <th>Members</th>
                                <th>Present</th>
                                <th>Excused</th>
                                <th>Absent</th>
                                <th>Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($attendanceByDivision && $attendanceByDivision->num_rows > 0): ?>
                                <?php while ($division = $attendanceByDivision->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $division['nama_divisi'] ?></td>
                                        <td><?= $division['total_members'] ?></td>
                                        <td><?= $division['total_present'] ?? 0 ?></td>
                                        <td><?= $division['total_excused'] ?? 0 ?></td>
                                        <td><?= $division['total_absent'] ?? 0 ?></td>
                                        <td>
                                            <?php
                                            $attendanceRate = 0;
                                            if ($division['total_records'] > 0) {
                                                $attendanceRate = ($division['total_present'] / $division['total_records']) * 100;
                                            }
                                            ?>
                                            <div class="progress">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= round($attendanceRate) ?>%" 
                                                    aria-valuenow="<?= round($attendanceRate) ?>" aria-valuemin="0" aria-valuemax="100">
                                                    <?= round($attendanceRate) ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No data found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="<?= BASE_URL ?>/modules/attendance/add.php" class="btn btn-primary btn-block py-3">
                            <i class="bi bi-plus-circle"></i> Record Attendance
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= BASE_URL ?>/modules/members/add.php" class="btn btn-success btn-block py-3">
                            <i class="bi bi-person-plus"></i> Add New Member
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= BASE_URL ?>/modules/meetings/add.php" class="btn btn-info btn-block py-3">
                            <i class="bi bi-calendar-plus"></i> Schedule Meeting
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= BASE_URL ?>/modules/documents/upload.php" class="btn btn-warning btn-block py-3">
                            <i class="bi bi-file-earmark-plus"></i> Upload Document
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
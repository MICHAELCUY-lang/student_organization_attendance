<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Set page title
$pageTitle = 'Meetings List';

// Get filter parameters
$startDate = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : '';
$searchTerm = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query with potential filters
$query = "
    SELECT m.*,
        (SELECT COUNT(*) FROM attendance WHERE meeting_id = m.meeting_id) as attendance_count,
        (SELECT COUNT(*) FROM attendance WHERE meeting_id = m.meeting_id AND status_kehadiran = 'hadir') as present_count,
        (SELECT COUNT(*) FROM attendance WHERE meeting_id = m.meeting_id AND status_kehadiran = 'izin') as excused_count,
        (SELECT COUNT(*) FROM attendance WHERE meeting_id = m.meeting_id AND status_kehadiran = 'alpa') as absent_count,
        (SELECT COUNT(*) FROM attendance WHERE meeting_id = m.meeting_id AND status_kehadiran = 'telat') as late_count
    FROM meetings m
";

// Add WHERE clauses if filters are set
$whereClauses = [];
if ($startDate) {
    $whereClauses[] = "m.tanggal >= '$startDate'";
}
if ($endDate) {
    $whereClauses[] = "m.tanggal <= '$endDate'";
}
if ($searchTerm) {
    $whereClauses[] = "m.judul_rapat LIKE '%$searchTerm%'";
}

if (!empty($whereClauses)) {
    $query .= " WHERE " . implode(" AND ", $whereClauses);
}

$query .= " ORDER BY m.tanggal DESC, m.waktu_mulai DESC";
$result = $conn->query($query);

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM meetings";
if (!empty($whereClauses)) {
    $countQuery .= " WHERE " . implode(" AND ", $whereClauses);
}
$countResult = $conn->query($countQuery);
$totalMeetings = $countResult ? $countResult->fetch_assoc()['total'] : 0;

// Include header
include_once '../../includes/header.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Meetings Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Meetings</li>
            </ol>
        </nav>
    </div>
    <a href="add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Schedule New Meeting
    </a>
</div>

<?php displayFlashMessage('meeting_message'); ?>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= $_SERVER['PHP_SELF'] ?>" class="row g-3">
            <div class="col-md-3">
                <label for="start_date" class="form-label small">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $startDate ?>">
            </div>
            
            <div class="col-md-3">
                <label for="end_date" class="form-label small">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $endDate ?>">
            </div>
            
            <div class="col-md-3">
                <label for="search" class="form-label small">Search</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="Search meeting title" value="<?= $searchTerm ?>">
            </div>
            
            <div class="col-md-3 d-flex align-items-end">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter me-1"></i> Apply
                    </button>
                    <a href="list.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Meetings Table -->
<div class="card shadow-sm">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Meetings List</h5>
        <span class="badge bg-primary rounded-pill"><?= $totalMeetings ?> meetings</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Duration</th>
                        <th>Attendance</th>
                        <th width="180" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($meeting = $result->fetch_assoc()): ?>
                            <?php 
                            // Calculate time duration
                            $startTime = strtotime($meeting['waktu_mulai']);
                            $endTime = strtotime($meeting['waktu_selesai']);
                            $durationMinutes = round(($endTime - $startTime) / 60);
                            $hours = floor($durationMinutes / 60);
                            $minutes = $durationMinutes % 60;
                            $durationText = ($hours > 0 ? $hours . 'h ' : '') . ($minutes > 0 ? $minutes . 'm' : '');
                            
                            // Calculate attendance statistics
                            $attendanceRate = $meeting['attendance_count'] > 0 
                                ? round(($meeting['present_count'] + $meeting['late_count']) / $meeting['attendance_count'] * 100) 
                                : 0;
                            
                            // Determine badge color based on rate
                            $badgeClass = $attendanceRate >= 75 ? 'bg-success' : ($attendanceRate >= 50 ? 'bg-warning' : 'bg-danger');
                            ?>
                            <tr>
                                <td>
                                    <h6 class="mb-0"><?= $meeting['judul_rapat'] ?></h6>
                                    <?php if ($meeting['catatan_rapat']): ?>
                                        <span class="badge bg-light text-dark">Has notes</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-nowrap">
                                        <i class="bi bi-calendar3 me-1 text-muted"></i>
                                        <?= formatDate($meeting['tanggal']) ?>
                                    </span>
                                </td>
                                <td class="text-nowrap">
                                    <i class="bi bi-clock me-1 text-muted"></i>
                                    <?= formatTime($meeting['waktu_mulai']) ?> - <?= formatTime($meeting['waktu_selesai']) ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <?= $durationText ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <span class="badge <?= $badgeClass ?>"><?= $attendanceRate ?>%</span>
                                        </div>
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                style="width: <?= ($meeting['present_count'] / max(1, $meeting['attendance_count'])) * 100 ?>%" 
                                                title="Present: <?= $meeting['present_count'] ?>"></div>
                                            <div class="progress-bar bg-info" role="progressbar" 
                                                style="width: <?= ($meeting['late_count'] / max(1, $meeting['attendance_count'])) * 100 ?>%" 
                                                title="Late: <?= $meeting['late_count'] ?>"></div>
                                            <div class="progress-bar bg-warning" role="progressbar" 
                                                style="width: <?= ($meeting['excused_count'] / max(1, $meeting['attendance_count'])) * 100 ?>%" 
                                                title="Excused: <?= $meeting['excused_count'] ?>"></div>
                                            <div class="progress-bar bg-danger" role="progressbar" 
                                                style="width: <?= ($meeting['absent_count'] / max(1, $meeting['attendance_count'])) * 100 ?>%" 
                                                title="Absent: <?= $meeting['absent_count'] ?>"></div>
                                        </div>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <?= $meeting['attendance_count'] ?> records
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="../attendance/add.php?meeting_id=<?= $meeting['meeting_id'] ?>" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Record Attendance">
                                            <i class="bi bi-clipboard-check"></i>
                                        </a>
                                        <a href="../attendance/list.php?meeting_id=<?= $meeting['meeting_id'] ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View Attendance">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="edit.php?id=<?= $meeting['meeting_id'] ?>" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="process.php?action=delete&id=<?= $meeting['meeting_id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" data-bs-toggle="tooltip" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center py-5">
                                    <i class="bi bi-calendar-x text-muted mb-3" style="font-size: 2.5rem;"></i>
                                    <h5 class="text-muted mb-3">No meetings found</h5>
                                    <p class="text-muted mb-3">Schedule a new meeting to start recording attendance</p>
                                    <a href="add.php" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i> Schedule New Meeting
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
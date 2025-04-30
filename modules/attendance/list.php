<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Set page title
$pageTitle = 'Attendance Records';

// Get filter parameters
$meetingId = isset($_GET['meeting_id']) ? (int)$_GET['meeting_id'] : null;
$memberId = isset($_GET['member_id']) ? (int)$_GET['member_id'] : null;
$divisonId = isset($_GET['division_id']) ? (int)$_GET['division_id'] : null;
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$dateFrom = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : '';

// Get meeting info if meeting_id is provided
$meetingInfo = null;
if ($meetingId) {
    $meetingQuery = "SELECT * FROM meetings WHERE meeting_id = $meetingId";
    $meetingResult = $conn->query($meetingQuery);
    if ($meetingResult && $meetingResult->num_rows > 0) {
        $meetingInfo = $meetingResult->fetch_assoc();
    } else {
        setFlashMessage('attendance_message', 'Meeting not found', 'danger');
        redirect(BASE_URL . '/modules/meetings/list.php');
    }
}

// Get member info if member_id is provided
$memberInfo = null;
if ($memberId) {
    $memberQuery = "
        SELECT m.*, d.nama_divisi 
        FROM members m
        JOIN divisions d ON m.division_id = d.division_id
        WHERE m.member_id = $memberId
    ";
    $memberResult = $conn->query($memberQuery);
    if ($memberResult && $memberResult->num_rows > 0) {
        $memberInfo = $memberResult->fetch_assoc();
    } else {
        setFlashMessage('attendance_message', 'Member not found', 'danger');
        redirect(BASE_URL . '/modules/members/list.php');
    }
}

// Get all divisions for filter
$divisionsQuery = "SELECT * FROM divisions ORDER BY nama_divisi ASC";
$divisionsResult = $conn->query($divisionsQuery);

// Get all meetings for filter
$meetingsQuery = "SELECT meeting_id, judul_rapat, tanggal FROM meetings ORDER BY tanggal DESC, waktu_mulai DESC";
$meetingsResult = $conn->query($meetingsQuery);

// Build query based on filters
$query = "
    SELECT a.*, m.nama as member_name, mt.judul_rapat, mt.tanggal, 
           d.nama_divisi, m.nim
    FROM attendance a
    JOIN members m ON a.member_id = m.member_id
    JOIN meetings mt ON a.meeting_id = mt.meeting_id
    JOIN divisions d ON m.division_id = d.division_id
";

// Add WHERE clauses if filters are set
$whereClauses = [];
if ($meetingId) {
    $whereClauses[] = "a.meeting_id = $meetingId";
}
if ($memberId) {
    $whereClauses[] = "a.member_id = $memberId";
}
if ($divisonId) {
    $whereClauses[] = "m.division_id = $divisonId";
}
if ($status) {
    $whereClauses[] = "a.status_kehadiran = '$status'";
}
if ($dateFrom) {
    $whereClauses[] = "mt.tanggal >= '$dateFrom'";
}
if ($dateTo) {
    $whereClauses[] = "mt.tanggal <= '$dateTo'";
}

if (!empty($whereClauses)) {
    $query .= " WHERE " . implode(" AND ", $whereClauses);
}

$query .= " ORDER BY mt.tanggal DESC, mt.waktu_mulai DESC, m.nama ASC";
$result = $conn->query($query);

// Get total count
$countQuery = "
    SELECT COUNT(*) as total 
    FROM attendance a
    JOIN members m ON a.member_id = m.member_id
    JOIN meetings mt ON a.meeting_id = mt.meeting_id
";
if (!empty($whereClauses)) {
    $countQuery .= " WHERE " . implode(" AND ", $whereClauses);
}
$countResult = $conn->query($countQuery);
$totalRecords = $countResult ? $countResult->fetch_assoc()['total'] : 0;

function hasPermission($permission) {
    if (!isset($_SESSION['user_permissions'])) {
        return false;
    }
    return in_array($permission, $_SESSION['user_permissions']);
}

// Include header
include_once '../../includes/header.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <?php if ($meetingInfo): ?>
                Attendance: <?= $meetingInfo['judul_rapat'] ?>
            <?php elseif ($memberInfo): ?>
                Attendance for: <?= $memberInfo['nama'] ?> (<?= $memberInfo['nim'] ?>)
            <?php else: ?>
                Attendance Records
            <?php endif; ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a></li>
                <?php if ($meetingInfo): ?>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/modules/meetings/list.php">Meetings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Attendance</li>
                <?php elseif ($memberInfo): ?>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/modules/members/list.php">Members</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Attendance</li>
                <?php else: ?>
                    <li class="breadcrumb-item active" aria-current="page">Attendance</li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <?php if ($meetingId): ?>
            <a href="add.php?meeting_id=<?= $meetingId ?>" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i> Record Attendance
            </a>
            <a href="list.php" class="btn btn-outline-secondary">
                <i class="bi bi-list me-1"></i> All Records
            </a>
        <?php elseif ($memberId): ?>
            <a href="<?= BASE_URL ?>/modules/members/list.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Members
            </a>
        <?php else: ?>
            <a href="add.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> New Record
            </a>
        <?php endif; ?>
    </div>
</div>

<?php displayFlashMessage('attendance_message'); ?>

<!-- Filter Card -->
<?php if (!$meetingId && !$memberId): ?>
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= $_SERVER['PHP_SELF'] ?>" class="row g-3">
            <div class="col-md-3">
                <label for="meeting_id" class="form-label small">Meeting</label>
                <select class="form-select" id="meeting_id" name="meeting_id">
                    <option value="">All Meetings</option>
                    <?php if ($meetingsResult && $meetingsResult->num_rows > 0): ?>
                        <?php while ($meeting = $meetingsResult->fetch_assoc()): ?>
                            <option value="<?= $meeting['meeting_id'] ?>" <?= $meetingId == $meeting['meeting_id'] ? 'selected' : '' ?>>
                                <?= formatDate($meeting['tanggal']) ?> - <?= $meeting['judul_rapat'] ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="division_id" class="form-label small">Division</label>
                <select class="form-select" id="division_id" name="division_id">
                    <option value="">All Divisions</option>
                    <?php if ($divisionsResult && $divisionsResult->num_rows > 0): ?>
                        <?php while ($division = $divisionsResult->fetch_assoc()): ?>
                            <option value="<?= $division['division_id'] ?>" <?= $divisonId == $division['division_id'] ? 'selected' : '' ?>>
                                <?= $division['nama_divisi'] ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="status" class="form-label small">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="hadir" <?= $status == 'hadir' ? 'selected' : '' ?>>Present</option>
                    <option value="izin" <?= $status == 'izin' ? 'selected' : '' ?>>Excused</option>
                    <option value="alpa" <?= $status == 'alpa' ? 'selected' : '' ?>>Absent</option>
                    <option value="telat" <?= $status == 'telat' ? 'selected' : '' ?>>Late</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="date_from" class="form-label small">Date From</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?= $dateFrom ?>">
            </div>
            
            <div class="col-md-2">
                <label for="date_to" class="form-label small">Date To</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?= $dateTo ?>">
            </div>
            
            <div class="col-12">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter me-1"></i> Apply Filters
                    </button>
                    <a href="list.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Clear
                    </a>
                    
                    <div class="ms-auto">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <button type="button" class="btn btn-outline-success" onclick="exportAttendance()">
                                <i class="bi bi-file-earmark-excel me-1"></i> Export
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Attendance Summary -->
<?php if ($meetingInfo || $memberInfo): ?>
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <?php if ($meetingInfo): ?>
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Meeting Details</h6>
                    <p><strong>Title:</strong> <?= $meetingInfo['judul_rapat'] ?></p>
                    <p><strong>Date:</strong> <?= formatDate($meetingInfo['tanggal']) ?></p>
                    <p><strong>Time:</strong> <?= formatTime($meetingInfo['waktu_mulai']) ?> - <?= formatTime($meetingInfo['waktu_selesai']) ?></p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Attendance Statistics</h6>
                    <?php
                    // Calculate attendance statistics
                    $statsQuery = "
                        SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN status_kehadiran = 'hadir' THEN 1 ELSE 0 END) as present,
                            SUM(CASE WHEN status_kehadiran = 'izin' THEN 1 ELSE 0 END) as excused,
                            SUM(CASE WHEN status_kehadiran = 'alpa' THEN 1 ELSE 0 END) as absent,
                            SUM(CASE WHEN status_kehadiran = 'telat' THEN 1 ELSE 0 END) as late
                        FROM attendance
                        WHERE meeting_id = $meetingId
                    ";
                    $statsResult = $conn->query($statsQuery);
                    $stats = $statsResult->fetch_assoc();
                    $presentRate = $stats['total'] > 0 ? round((($stats['present'] + $stats['late']) / $stats['total']) * 100) : 0;
                    ?>
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="p-2 rounded bg-success bg-opacity-10">
                                <h5 class="mb-0"><?= $stats['present'] ?></h5>
                                <span class="small text-muted">Present</span>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2 rounded bg-info bg-opacity-10">
                                <h5 class="mb-0"><?= $stats['late'] ?></h5>
                                <span class="small text-muted">Late</span>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2 rounded bg-warning bg-opacity-10">
                                <h5 class="mb-0"><?= $stats['excused'] ?></h5>
                                <span class="small text-muted">Excused</span>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2 rounded bg-danger bg-opacity-10">
                                <h5 class="mb-0"><?= $stats['absent'] ?></h5>
                                <span class="small text-muted">Absent</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small">Attendance Rate:</span>
                            <span class="small fw-bold"><?= $presentRate ?>%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $presentRate ?>%" aria-valuenow="<?= $presentRate ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
                <?php elseif ($memberInfo): ?>
    <div class="col-md-6">
        <h6 class="text-muted mb-2">Member Details</h6>
        <p><strong>Name:</strong> <?= $memberInfo['nama'] ?></p>
        <p><strong>ID Number:</strong> <?= $memberInfo['nim'] ?></p>
        <p><strong>Division:</strong> <?= $memberInfo['nama_divisi'] ?></p>
    </div>
    <div class="col-md-6">
        <h6 class="text-muted mb-2">Attendance Statistics</h6>
        <?php
        // Calculate attendance statistics for this member
        $statsQuery = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status_kehadiran = 'hadir' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status_kehadiran = 'izin' THEN 1 ELSE 0 END) as excused,
                SUM(CASE WHEN status_kehadiran = 'alpa' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status_kehadiran = 'telat' THEN 1 ELSE 0 END) as late
            FROM attendance
            WHERE member_id = $memberId
        ";
        $statsResult = $conn->query($statsQuery);
        $stats = $statsResult->fetch_assoc();
        $presentRate = $stats['total'] > 0 ? round((($stats['present'] + $stats['late']) / $stats['total']) * 100) : 0;
        ?>
        <div class="row text-center">
            <div class="col-3">
                <div class="p-2 rounded bg-success bg-opacity-10">
                    <h5 class="mb-0"><?= $stats['present'] ?></h5>
                    <span class="small text-muted">Present</span>
                </div>
            </div>
            <div class="col-3">
                <div class="p-2 rounded bg-info bg-opacity-10">
                    <h5 class="mb-0"><?= $stats['late'] ?></h5>
                    <span class="small text-muted">Late</span>
                </div>
            </div>
            <div class="col-3">
                <div class="p-2 rounded bg-warning bg-opacity-10">
                    <h5 class="mb-0"><?= $stats['excused'] ?></h5>
                    <span class="small text-muted">Excused</span>
                </div>
            </div>
            <div class="col-3">
                <div class="p-2 rounded bg-danger bg-opacity-10">
                    <h5 class="mb-0"><?= $stats['absent'] ?></h5>
                    <span class="small text-muted">Absent</span>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small">Attendance Rate:</span>
                <span class="small fw-bold"><?= $presentRate ?>%</span>
            </div>
            <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $presentRate ?>%" aria-valuenow="<?= $presentRate ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>
<?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Results Count -->
<div class="mb-3">
    <span class="text-muted">Showing <?= $result ? $result->num_rows : 0 ?> of <?= $totalRecords ?> records</span>
</div>

<!-- Attendance Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col">#</th>
                    <?php if (!$meetingId): ?>
                        <th scope="col">Meeting</th>
                        <th scope="col">Date</th>
                    <?php endif; ?>
                    <?php if (!$memberId): ?>
                        <th scope="col">Member</th>
                        <th scope="col">NIM</th>
                        <th scope="col">Division</th>
                    <?php endif; ?>
                    <th scope="col">Status</th>
                    <th scope="col">Notes</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <?php if (!$meetingId): ?>
                                <td>
                                    <a href="list.php?meeting_id=<?= $row['meeting_id'] ?>" class="text-decoration-none">
                                        <?= $row['judul_rapat'] ?>
                                    </a>
                                </td>
                                <td><?= formatDate($row['tanggal']) ?></td>
                            <?php endif; ?>
                            <?php if (!$memberId): ?>
                                <td>
                                    <a href="list.php?member_id=<?= $row['member_id'] ?>" class="text-decoration-none">
                                        <?= $row['member_name'] ?>
                                    </a>
                                </td>
                                <td><?= $row['nim'] ?></td>
                                <td><?= $row['nama_divisi'] ?></td>
                            <?php endif; ?>
                            <td>
                                <?php if ($row['status_kehadiran'] == 'hadir'): ?>
                                    <span class="badge bg-success">Present</span>
                                <?php elseif ($row['status_kehadiran'] == 'telat'): ?>
                                    <span class="badge bg-info">Late</span>
                                <?php elseif ($row['status_kehadiran'] == 'izin'): ?>
                                    <span class="badge bg-warning">Excused</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Absent</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $row['keterangan'] ? $row['keterangan'] : '<span class="text-muted">-</span>' ?>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                        <li>
                                            <a class="dropdown-item" href="edit.php?id=<?= $row['attendance_id'] ?>">
                                                <i class="bi bi-pencil me-1"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="confirmDelete(<?= $row['attendance_id'] ?>)">
                                                <i class="bi bi-trash me-1"></i> Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= (!$meetingId && !$memberId) ? '8' : ((!$meetingId || !$memberId) ? '6' : '4') ?>" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>
                                No attendance records found
                            </div>
                            <?php if ($meetingId && hasPermission('attendance_create')): ?>
                                <a href="add.php?meeting_id=<?= $meetingId ?>" class="btn btn-sm btn-primary mt-2">
                                    <i class="bi bi-plus-circle me-1"></i> Record Attendance
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this attendance record? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="deleteLink" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id) {
        const deleteLink = document.getElementById('deleteLink');
        deleteLink.href = 'delete.php?id=' + id;
        
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }
    
    function exportAttendance() {
        // Get current URL with query parameters
        let url = new URL(window.location.href);
        
        // Change the path to the export endpoint
        url.pathname = url.pathname.replace('list.php', 'export.php');
        
        // Redirect to the export URL
        window.location.href = url.toString();
    }
</script>

<?php
// Include footer
include_once '../../includes/footer.php';
?>
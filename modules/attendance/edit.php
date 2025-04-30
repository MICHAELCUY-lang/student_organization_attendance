<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Set page title
$pageTitle = 'Edit Attendance';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setFlashMessage('attendance_message', 'Attendance ID is required', 'danger');
    redirect(BASE_URL . '/modules/attendance/list.php');
}

$attendanceId = (int) $_GET['id'];

// Get attendance data
$query = "
    SELECT a.*, m.nama as member_name, m.nim, d.nama_divisi, mt.judul_rapat, mt.tanggal, mt.waktu_mulai, mt.waktu_selesai
    FROM attendance a
    JOIN members m ON a.member_id = m.member_id
    JOIN meetings mt ON a.meeting_id = mt.meeting_id
    JOIN divisions d ON m.division_id = d.division_id
    WHERE a.attendance_id = $attendanceId
";
$result = $conn->query($query);

// Check if attendance record exists
if (!$result || $result->num_rows === 0) {
    setFlashMessage('attendance_message', 'Attendance record not found', 'danger');
    redirect(BASE_URL . '/modules/attendance/list.php');
}

$attendance = $result->fetch_assoc();

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Edit Attendance</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/modules/attendance/list.php">Attendance</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
    <a href="list.php<?= isset($_SERVER['HTTP_REFERER']) ? '' : '?meeting_id=' . $attendance['meeting_id'] ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
</div>

<?php displayFlashMessage('attendance_message'); ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0 font-weight-bold">Attendance Details</h5>
        
        <div class="text-muted small">
            <i class="bi bi-calendar3 me-1"></i> <?= formatDate($attendance['tanggal']) ?>
            <i class="bi bi-clock ms-2 me-1"></i> <?= formatTime($attendance['waktu_mulai']) ?> - <?= formatTime($attendance['waktu_selesai']) ?>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="text-muted mb-3">Meeting Information</h6>
                <p><strong>Title:</strong> <?= $attendance['judul_rapat'] ?></p>
                <p><strong>Date:</strong> <?= formatDate($attendance['tanggal']) ?></p>
                <p><strong>Time:</strong> <?= formatTime($attendance['waktu_mulai']) ?> - <?= formatTime($attendance['waktu_selesai']) ?></p>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-3">Member Information</h6>
                <p><strong>Name:</strong> <?= $attendance['member_name'] ?></p>
                <p><strong>NIM:</strong> <?= $attendance['nim'] ?></p>
                <p><strong>Division:</strong> <?= $attendance['nama_divisi'] ?></p>
            </div>
        </div>
        
        <form action="process.php" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="attendance_id" value="<?= $attendance['attendance_id'] ?>">
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="status_kehadiran" class="form-label">Attendance Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="status_kehadiran" name="status_kehadiran" required>
                        <option value="hadir" <?= $attendance['status_kehadiran'] == 'hadir' ? 'selected' : '' ?>>Present</option>
                        <option value="telat" <?= $attendance['status_kehadiran'] == 'telat' ? 'selected' : '' ?>>Late</option>
                        <option value="izin" <?= $attendance['status_kehadiran'] == 'izin' ? 'selected' : '' ?>>Excused</option>
                        <option value="alpa" <?= $attendance['status_kehadiran'] == 'alpa' ? 'selected' : '' ?>>Absent</option>
                    </select>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="keterangan" class="form-label">Notes</label>
                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?= $attendance['keterangan'] ?></textarea>
                </div>
            </div>
            
            <div class="mt-4 d-flex justify-content-between">
                <a href="list.php<?= isset($_SERVER['HTTP_REFERER']) ? '' : '?meeting_id=' . $attendance['meeting_id'] ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Update Record
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
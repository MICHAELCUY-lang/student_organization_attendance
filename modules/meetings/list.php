// modules/meetings/list.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Set page title
$pageTitle = 'Meetings List';

// Get all meetings
$query = "
    SELECT *
    FROM meetings
    ORDER BY tanggal DESC, waktu_mulai DESC
";
$result = $conn->query($query);

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Meetings Management</h1>
    <a href="add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Schedule New Meeting
    </a>
</div>

<?php displayFlashMessage('meeting_message'); ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Meetings List</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Duration</th>
                        <th>Attendance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($meeting = $result->fetch_assoc()): ?>
                            <?php
                            // Calculate attendance statistics for this meeting
                            $attendanceQuery = "
                                SELECT 
                                    COUNT(*) as total,
                                    SUM(CASE WHEN status_kehadiran = 'hadir' THEN 1 ELSE 0 END) as present,
                                    SUM(CASE WHEN status_kehadiran = 'izin' THEN 1 ELSE 0 END) as excused,
                                    SUM(CASE WHEN status_kehadiran = 'alpa' THEN 1 ELSE 0 END) as absent,
                                    SUM(CASE WHEN status_kehadiran = 'telat' THEN 1 ELSE 0 END) as late
                                FROM attendance
                                WHERE meeting_id = {$meeting['meeting_id']}
                            ";
                            $attendanceResult = $conn->query($attendanceQuery);
                            $attendance = $attendanceResult->fetch_assoc();
                            
                            // Calculate time duration
                            $startTime = strtotime($meeting['waktu_mulai']);
                            $endTime = strtotime($meeting['waktu_selesai']);
                            $durationMinutes = round(($endTime - $startTime) / 60);
                            ?>
                            <tr>
                                <td><?= $meeting['meeting_id'] ?></td>
                                <td><?= $meeting['judul_rapat'] ?></td>
                                <td><?= formatDate($meeting['tanggal']) ?></td>
                                <td><?= formatTime($meeting['waktu_mulai']) ?> - <?= formatTime($meeting['waktu_selesai']) ?></td>
                                <td><?= $durationMinutes ?> minutes</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                            <?php 
                                            $presentPercentage = 0;
                                            $excusedPercentage = 0;
                                            $absentPercentage = 0;
                                            $latePercentage = 0;
                                            
                                            if ($attendance['total'] > 0) {
                                                $presentPercentage = ($attendance['present'] / $attendance['total']) * 100;
                                                $excusedPercentage = ($attendance['excused'] / $attendance['total']) * 100;
                                                $absentPercentage = ($attendance['absent'] / $attendance['total']) * 100;
                                                $latePercentage = ($attendance['late'] / $attendance['total']) * 100;
                                            }
                                            ?>
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $presentPercentage ?>%" title="Present: <?= $attendance['present'] ?>"></div>
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $excusedPercentage ?>%" title="Excused: <?= $attendance['excused'] ?>"></div>
                                            <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $absentPercentage ?>%" title="Absent: <?= $attendance['absent'] ?>"></div>
                                            <div class="progress-bar bg-info" role="progressbar" style="width: <?= $latePercentage ?>%" title="Late: <?= $attendance['late'] ?>"></div>
                                        </div>
                                        <span class="badge bg-secondary"><?= $attendance['total'] ? $attendance['total'] : 0 ?></span>
                                    </div>
                                </td>
                                <td>
                                    <a href="../attendance/add.php?meeting_id=<?= $meeting['meeting_id'] ?>" class="btn btn-sm btn-success">
                                        <i class="bi bi-calendar-check"></i> Record
                                    </a>
                                    <a href="edit.php?id=<?= $meeting['meeting_id'] ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <a href="process.php?action=delete&id=<?= $meeting['meeting_id'] ?>" class="btn btn-sm btn-danger btn-delete">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No meetings found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>

// modules/meetings/add.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Set page title
$pageTitle = 'Schedule Meeting';

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Schedule New Meeting</h1>
    <a href="list.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Meeting Information</h6>
    </div>
    <div class="card-body">
        <form action="process.php" method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="judul_rapat" class="form-label">Meeting Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="judul_rapat" name="judul_rapat" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="tanggal" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control datepicker" id="tanggal" name="tanggal" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="waktu_mulai" class="form-label">Start Time <span class="text-danger">*</span></label>
                    <input type="time" class="form-control timepicker" id="waktu_mulai" name="waktu_mulai" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="waktu_selesai" class="form-label">End Time <span class="text-danger">*</span></label>
                    <input type="time" class="form-control timepicker" id="waktu_selesai" name="waktu_selesai" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="catatan_rapat" class="form-label">Meeting Notes</label>
                    <textarea class="form-control" id="catatan_rapat" name="catatan_rapat" rows="4"></textarea>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Schedule Meeting
                </button>
                <a href="list.php" class="btn btn-danger">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>

// modules/meetings/edit.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Set page title
$pageTitle = 'Edit Meeting';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setFlashMessage('meeting_message', 'Meeting ID is required', 'danger');
    redirect(BASE_URL . '/modules/meetings/list.php');
}

$meetingId = (int) $_GET['id'];

// Get meeting data
$query = "
    SELECT * FROM meetings 
    WHERE meeting_id = $meetingId
";
$result = $conn->query($query);

// Check if meeting exists
if (!$result || $result->num_rows === 0) {
    setFlashMessage('meeting_message', 'Meeting not found', 'danger');
    redirect(BASE_URL . '/modules/meetings/list.php');
}

$meeting = $result->fetch_assoc();

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit Meeting</h1>
    <a href="list.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Meeting Information</h6>
    </div>
    <div class="card-body">
        <form action="process.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="meeting_id" value="<?= $meeting['meeting_id'] ?>">
            // Continuing from modules/meetings/edit.php
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="judul_rapat" class="form-label">Meeting Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="judul_rapat" name="judul_rapat" value="<?= $meeting['judul_rapat'] ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="tanggal" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control datepicker" id="tanggal" name="tanggal" value="<?= $meeting['tanggal'] ?>" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="waktu_mulai" class="form-label">Start Time <span class="text-danger">*</span></label>
                    <input type="time" class="form-control timepicker" id="waktu_mulai" name="waktu_mulai" value="<?= $meeting['waktu_mulai'] ?>" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="waktu_selesai" class="form-label">End Time <span class="text-danger">*</span></label>
                    <input type="time" class="form-control timepicker" id="waktu_selesai" name="waktu_selesai" value="<?= $meeting['waktu_selesai'] ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="catatan_rapat" class="form-label">Meeting Notes</label>
                    <textarea class="form-control" id="catatan_rapat" name="catatan_rapat" rows="4"><?= $meeting['catatan_rapat'] ?></textarea>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update Meeting
                </button>
                <a href="list.php" class="btn btn-danger">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>

// modules/meetings/process.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Check if action is provided
if (!isset($_POST['action']) && !isset($_GET['action'])) {
    setFlashMessage('meeting_message', 'Invalid request', 'danger');
    redirect(BASE_URL . '/modules/meetings/list.php');
}

// Determine the action
$action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];

switch ($action) {
    case 'add':
        addMeeting();
        break;
    case 'edit':
        editMeeting();
        break;
    case 'delete':
        deleteMeeting();
        break;
    default:
        setFlashMessage('meeting_message', 'Invalid action', 'danger');
        redirect(BASE_URL . '/modules/meetings/list.php');
}

/**
 * Add new meeting
 */
function addMeeting() {
    global $conn;
    
    // Check required fields
    if (empty($_POST['judul_rapat']) || empty($_POST['tanggal']) || empty($_POST['waktu_mulai']) || empty($_POST['waktu_selesai'])) {
        setFlashMessage('meeting_message', 'Please fill all required fields', 'danger');
        redirect(BASE_URL . '/modules/meetings/add.php');
    }
    
    // Sanitize and validate input
    $judulRapat = sanitize($_POST['judul_rapat']);
    $tanggal = sanitize($_POST['tanggal']);
    $waktuMulai = sanitize($_POST['waktu_mulai']);
    $waktuSelesai = sanitize($_POST['waktu_selesai']);
    $catatanRapat = !empty($_POST['catatan_rapat']) ? sanitize($_POST['catatan_rapat']) : NULL;
    
    // Validate start and end times
    if (strtotime($waktuMulai) >= strtotime($waktuSelesai)) {
        setFlashMessage('meeting_message', 'End time must be after start time', 'danger');
        redirect(BASE_URL . '/modules/meetings/add.php');
    }
    
    // Insert new meeting
    $query = "
        INSERT INTO meetings (judul_rapat, tanggal, waktu_mulai, waktu_selesai, catatan_rapat)
        VALUES ('$judulRapat', '$tanggal', '$waktuMulai', '$waktuSelesai', " . ($catatanRapat ? "'$catatanRapat'" : 'NULL') . ")
    ";
    
    if ($conn->query($query)) {
        $meetingId = $conn->insert_id;
        setFlashMessage('meeting_message', 'Meeting scheduled successfully', 'success');
        
        // Redirect to add attendance if requested
        if (isset($_POST['redirect_to_attendance']) && $_POST['redirect_to_attendance'] == '1') {
            redirect(BASE_URL . '/modules/attendance/add.php?meeting_id=' . $meetingId);
        } else {
            redirect(BASE_URL . '/modules/meetings/list.php');
        }
    } else {
        setFlashMessage('meeting_message', 'Failed to schedule meeting: ' . $conn->error, 'danger');
        redirect(BASE_URL . '/modules/meetings/add.php');
    }
}

/**
 * Edit existing meeting
 */
function editMeeting() {
    global $conn;
    
    // Check required fields
    if (empty($_POST['meeting_id']) || empty($_POST['judul_rapat']) || empty($_POST['tanggal']) || empty($_POST['waktu_mulai']) || empty($_POST['waktu_selesai'])) {
        setFlashMessage('meeting_message', 'Please fill all required fields', 'danger');
        redirect(BASE_URL . '/modules/meetings/list.php');
    }
    
    // Sanitize and validate input
    $meetingId = (int) $_POST['meeting_id'];
    $judulRapat = sanitize($_POST['judul_rapat']);
    $tanggal = sanitize($_POST['tanggal']);
    $waktuMulai = sanitize($_POST['waktu_mulai']);
    $waktuSelesai = sanitize($_POST['waktu_selesai']);
    $catatanRapat = !empty($_POST['catatan_rapat']) ? sanitize($_POST['catatan_rapat']) : NULL;
    
    // Validate start and end times
    if (strtotime($waktuMulai) >= strtotime($waktuSelesai)) {
        setFlashMessage('meeting_message', 'End time must be after start time', 'danger');
        redirect(BASE_URL . '/modules/meetings/edit.php?id=' . $meetingId);
    }
    
    // Update meeting
    $query = "
        UPDATE meetings
        SET judul_rapat = '$judulRapat',
            tanggal = '$tanggal',
            waktu_mulai = '$waktuMulai',
            waktu_selesai = '$waktuSelesai',
            catatan_rapat = " . ($catatanRapat ? "'$catatanRapat'" : 'NULL') . "
        WHERE meeting_id = $meetingId
    ";
    
    if ($conn->query($query)) {
        setFlashMessage('meeting_message', 'Meeting updated successfully', 'success');
    } else {
        setFlashMessage('meeting_message', 'Failed to update meeting: ' . $conn->error, 'danger');
    }
    
    redirect(BASE_URL . '/modules/meetings/list.php');
}

/**
 * Delete meeting
 */
function deleteMeeting() {
    global $conn;
    
    // Check if ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        setFlashMessage('meeting_message', 'Meeting ID is required', 'danger');
        redirect(BASE_URL . '/modules/meetings/list.php');
    }
    
    $meetingId = (int) $_GET['id'];
    
    // Check if meeting exists
    $checkQuery = "SELECT meeting_id FROM meetings WHERE meeting_id = $meetingId";
    $checkResult = $conn->query($checkQuery);
    
    if (!$checkResult || $checkResult->num_rows === 0) {
        setFlashMessage('meeting_message', 'Meeting not found', 'danger');
        redirect(BASE_URL . '/modules/meetings/list.php');
    }
    
    // Delete meeting
    $query = "DELETE FROM meetings WHERE meeting_id = $meetingId";
    
    if ($conn->query($query)) {
        setFlashMessage('meeting_message', 'Meeting deleted successfully', 'success');
    } else {
        setFlashMessage('meeting_message', 'Failed to delete meeting: ' . $conn->error, 'danger');
    }
    
    redirect(BASE_URL . '/modules/meetings/list.php');
}
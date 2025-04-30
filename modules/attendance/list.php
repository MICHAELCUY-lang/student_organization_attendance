// modules/attendance/list.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Set page title
$pageTitle = 'Attendance Records';

// Check if meeting_id is provided
$meetingId = isset($_GET['meeting_id']) ? (int) $_GET['meeting_id'] : null;

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

// Build query based on filters
$query = "
    SELECT a.*, m.nama as member_name, mt.judul_rapat, mt.tanggal, 
           d.nama_divisi, m.nim
    FROM attendance a
    JOIN members m ON a.member_id = m.member_id
    JOIN meetings mt ON a.meeting_id = mt.meeting_id
    JOIN divisions d ON m.division_id = d.division_id
";

// Add WHERE clause if meeting_id is provided
if ($meetingId) {
    $query .= " WHERE a.meeting_id = $meetingId";
}

$query .= " ORDER BY mt.tanggal DESC, mt.waktu_mulai DESC, m.nama ASC";

$result = $conn->query($query);

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <?= $meetingInfo ? 'Attendance for: ' . $meetingInfo['judul_rapat'] . ' (' . formatDate($meetingInfo['tanggal']) . ')' : 'All Attendance Records' ?>
    </h1>
    <div>
        <?php if ($meetingId): ?>
            <a href="add.php?meeting_id=<?= $meetingId ?>" class="btn btn-success me-2">
                <i class="bi bi-plus-circle"></i> Record Attendance
            </a>
            <a href="list.php" class="btn btn-secondary">
                <i class="bi bi-list"></i> All Records
            </a>
        <?php else: ?>
            <a href="add.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> New Attendance Record
            </a>
        <?php endif; ?>
    </div>
</div>

<?php displayFlashMessage('attendance_message'); ?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold">
            <?= $meetingInfo ? 'Attendance Records' : 'All Attendance Records' ?>
        </h6>
        
        <?php if ($meetingId && $result && $result->num_rows > 0): ?>
        <div>
            <button type="button" class="btn btn-sm btn-outline-primary btn-print" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
            <a href="report.php?meeting_id=<?= $meetingId ?>" class="btn btn-sm btn-outline-success">
                <i class="bi bi-file-earmark-excel"></i> Export
            </a>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <?php if (!$meetingId): ?>
                        <th>Meeting</th>
                        <th>Date</th>
                        <?php endif; ?>
                        <th>Member</th>
                        <th>NIM</th>
                        <th>Division</th>
                        <th>Status</th>
                        <th>Timestamp</th>
                        <th>Note</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($attendance = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $attendance['attendance_id'] ?></td>
                                <?php if (!$meetingId): ?>
                                <td><?= $attendance['judul_rapat'] ?></td>
                                <td><?= formatDate($attendance['tanggal']) ?></td>
                                <?php endif; ?>
                                <td><?= $attendance['member_name'] ?></td>
                                <td><?= $attendance['nim'] ?></td>
                                <td><?= $attendance['nama_divisi'] ?></td>
                                <td><?= getStatusBadge($attendance['status_kehadiran']) ?></td>
                                <td><?= formatDate($attendance['waktu_absen'], 'd M Y H:i') ?></td>
                                <td><?= $attendance['keterangan'] ? $attendance['keterangan'] : '-' ?></td>
                                <td>
                                    <a href="edit.php?id=<?= $attendance['attendance_id'] ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <a href="process.php?action=delete&id=<?= $attendance['attendance_id'] ?>&redirect=<?= $meetingId ? 'meeting&meeting_id=' . $meetingId : 'list' ?>" class="btn btn-sm btn-danger btn-delete">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= $meetingId ? '7' : '9' ?>" class="text-center">No attendance records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>

// modules/attendance/add.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Set page title
$pageTitle = 'Record Attendance';

// Check if meeting_id is provided or needs to be selected
$meetingId = isset($_GET['meeting_id']) ? (int) $_GET['meeting_id'] : null;
$meetingInfo = null;

// Get all meetings for dropdown (if meeting_id is not provided)
$meetingsQuery = "SELECT * FROM meetings ORDER BY tanggal DESC, waktu_mulai DESC";
$meetingsResult = $conn->query($meetingsQuery);

// If meeting_id is provided, get meeting info
if ($meetingId) {
    $meetingInfoQuery = "SELECT * FROM meetings WHERE meeting_id = $meetingId";
    $meetingInfoResult = $conn->query($meetingInfoQuery);
    
    if ($meetingInfoResult && $meetingInfoResult->num_rows > 0) {
        $meetingInfo = $meetingInfoResult->fetch_assoc();
    } else {
        setFlashMessage('attendance_message', 'Meeting not found', 'danger');
        redirect(BASE_URL . '/modules/meetings/list.php');
    }
}

// Get all members with divisions for selection
$membersQuery = "
    SELECT m.*, d.nama_divisi 
    FROM members m
    JOIN divisions d ON m.division_id = d.division_id
    ORDER BY d.nama_divisi ASC, m.nama ASC
";
$membersResult = $conn->query($membersQuery);

// Get list of members already recorded for this meeting (if meeting_id is provided)
$recordedMembers = [];
if ($meetingId) {
    $recordedQuery = "SELECT member_id FROM attendance WHERE meeting_id = $meetingId";
    $recordedResult = $conn->query($recordedQuery);
    
    if ($recordedResult && $recordedResult->num_rows > 0) {
        while ($row = $recordedResult->fetch_assoc()) {
            $recordedMembers[] = $row['member_id'];
        }
    }
}

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <?= $meetingInfo ? 'Record Attendance: ' . $meetingInfo['judul_rapat'] . ' (' . formatDate($meetingInfo['tanggal']) . ')' : 'Record Attendance' ?>
    </h1>
    <div>
        <?php if ($meetingId): ?>
            <a href="list.php?meeting_id=<?= $meetingId ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Records
            </a>
        <?php else: ?>
            <a href="list.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        <?php endif; ?>
    </div>
</div>

<?php displayFlashMessage('attendance_message'); ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Attendance Form</h6>
    </div>
    <div class="card-body">
        <form action="process.php" method="POST">
            <input type="hidden" name="action" value="add">
            
            <?php if (!$meetingId): ?>
            <!-- Meeting selection if not provided -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <label for="meeting_id" class="form-label">Select Meeting <span class="text-danger">*</span></label>
                    <select class="form-select" id="meeting_id" name="meeting_id" required>
                        <option value="">Select Meeting</option>
                        <?php if ($meetingsResult && $meetingsResult->num_rows > 0): ?>
                            <?php while ($meeting = $meetingsResult->fetch_assoc()): ?>
                                <option value="<?= $meeting['meeting_id'] ?>">
                                    <?= $meeting['judul_rapat'] ?> (<?= formatDate($meeting['tanggal']) ?> - <?= formatTime($meeting['waktu_mulai']) ?>)
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <?php else: ?>
            <!-- Hidden meeting_id if already provided -->
            <input type="hidden" name="meeting_id" value="<?= $meetingId ?>">
            <?php endif; ?>
            
            <!-- Member selection and attendance status -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold">Select Members</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                    <label class="form-check-label" for="selectAll">
                                        Select All
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <input type="text" class="form-control" id="searchMembers" placeholder="Search members...">
                            </div>
                            
                            <?php if ($membersResult && $membersResult->num_rows > 0): ?>
                                <!-- Group members by division -->
                                <?php
                                $membersByDivision = [];
                                while ($member = $membersResult->fetch_assoc()) {
                                    $membersByDivision[$member['division_id']]['name'] = $member['nama_divisi'];
                                    $membersByDivision[$member['division_id']]['members'][] = $member;
                                }
                                ?>
                                
                                <?php foreach ($membersByDivision as $division): ?>
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <h6 class="m-0"><?= $division['name'] ?></h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <?php foreach ($division['members'] as $member): ?>
                                                    <?php $isRecorded = in_array($member['member_id'], $recordedMembers); ?>
                                                    <div class="col-md-6 mb-2 member-item">
                                                        <div class="form-check">
                                                            <input class="form-check-input checkbox-item" type="checkbox" 
                                                                name="members[<?= $member['member_id'] ?>][selected]" 
                                                                id="member_<?= $member['member_id'] ?>" 
                                                                value="1"
                                                                <?= $isRecorded ? 'disabled' : '' ?>>
                                                            <label class="form-check-label <?= $isRecorded ? 'text-muted' : '' ?>" for="member_<?= $member['member_id'] ?>">
                                                                <?= $member['nama'] ?> (<?= $member['nim'] ?>)
                                                                <?php if ($isRecorded): ?>
                                                                    <span class="badge bg-info">Already recorded</span>
                                                                <?php endif; ?>
                                                            </label>
                                                        </div>
                                                        
                                                        <div class="ms-4 mt-2" id="status_<?= $member['member_id'] ?>" style="display: none;">
                                                            <select class="form-select form-select-sm" 
                                                                name="members[<?= $member['member_id'] ?>][status]">
                                                                <option value="hadir">Present</option>
                                                                <option value="izin">Excused</option>
                                                                <option value="alpa">Absent</option>
                                                                <option value="telat">Late</option>
                                                            </select>
                                                            
                                                            <div class="mt-2">
                                                                <input type="text" class="form-control form-control-sm" 
                                                                    name="members[<?= $member['member_id'] ?>][keterangan]" 
                                                                    placeholder="Note (optional)">
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <script>
                                    // Toggle status options when checkbox is clicked
                                    $(document).ready(function() {
                                        $('.checkbox-item').on('change', function() {
                                            var memberId = $(this).attr('id').replace('member_', '');
                                            if ($(this).is(':checked')) {
                                                $('#status_' + memberId).show();
                                            } else {
                                                $('#status_' + memberId).hide();
                                            }
                                        });
                                        
                                        // Search functionality
                                        $('#searchMembers').on('keyup', function() {
                                            var value = $(this).val().toLowerCase();
                                            $('.member-item').filter(function() {
                                                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                                            });
                                        });
                                        
                                        // Select all functionality
                                        $('#selectAll').on('click', function() {
                                            $('.checkbox-item:not(:disabled)').prop('checked', this.checked);
                                            if (this.checked) {
                                                $('.checkbox-item:not(:disabled)').each(function() {
                                                    var memberId = $(this).attr('id').replace('member_', '');
                                                    $('#status_' + memberId).show();
                                                });
                                            } else {
                                                $('.checkbox-item:not(:disabled)').each(function() {
                                                    var memberId = $(this).attr('id').replace('member_', '');
                                                    $('#status_' + memberId).hide();
                                                });
                                            }
                                        });
                                    });
                                </script>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    No members found. Please <a href="<?= BASE_URL ?>/modules/members/add.php">add members</a> first.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Attendance
                </button>
                <?php if ($meetingId): ?>
                    <a href="list.php?meeting_id=<?= $meetingId ?>" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                <?php else: ?>
                    <a href="list.php" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>

// modules/attendance/edit.php
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
    SELECT a.*, m.nama as member_name, mt.judul_rapat, mt.tanggal
    FROM attendance a
    JOIN members m ON a.member_id = m.member_id
    JOIN meetings mt ON a.meeting_id = mt.meeting_id
    WHERE a.attendance_id = $attendanceId
";
$result = $conn->query($query);

// Check if attendance exists
if (!$result || $result->num_rows === 0) {
    setFlashMessage('attendance_message', 'Attendance record not found', 'danger');
    redirect(BASE_URL . '/modules/attendance/list.php');
}

$attendance = $result->fetch_assoc();

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit Attendance</h1>
    <a href="list.php?meeting_id=<?= $attendance['meeting_id'] ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Records
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Attendance Information</h6>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <p><strong>Meeting:</strong> <?= $attendance['judul_rapat'] ?></p>
                <p><strong>Date:</strong> <?= formatDate($attendance['tanggal']) ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Member:</strong> <?= $attendance['member_name'] ?></p>
                <p><strong>Recorded on:</strong> <?= formatDate($attendance['waktu_absen'], 'd M Y H:i') ?></p>
            </div>
        </div>

        <form action="process.php" method="POST">
            <input type="hidden" name="action" value="edit">
            // Continuing from modules/attendance/edit.php
            <input type="hidden" name="attendance_id" value="<?= $attendance['attendance_id'] ?>">
            <input type="hidden" name="meeting_id" value="<?= $attendance['meeting_id'] ?>">
            <input type="hidden" name="member_id" value="<?= $attendance['member_id'] ?>">
            
            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="status_kehadiran" class="form-label">Attendance Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="status_kehadiran" name="status_kehadiran" required>
                        <option value="hadir" <?= $attendance['status_kehadiran'] === 'hadir' ? 'selected' : '' ?>>Present</option>
                        <option value="izin" <?= $attendance['status_kehadiran'] === 'izin' ? 'selected' : '' ?>>Excused</option>
                        <option value="alpa" <?= $attendance['status_kehadiran'] === 'alpa' ? 'selected' : '' ?>>Absent</option>
                        <option value="telat" <?= $attendance['status_kehadiran'] === 'telat' ? 'selected' : '' ?>>Late</option>
                    </select>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="keterangan" class="form-label">Notes</label>
                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?= $attendance['keterangan'] ?></textarea>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update Attendance
                </button>
                <a href="list.php?meeting_id=<?= $attendance['meeting_id'] ?>" class="btn btn-danger">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>

// modules/attendance/process.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Check if action is provided
if (!isset($_POST['action']) && !isset($_GET['action'])) {
    setFlashMessage('attendance_message', 'Invalid request', 'danger');
    redirect(BASE_URL . '/modules/attendance/list.php');
}

// Determine the action
$action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];

switch ($action) {
    case 'add':
        addAttendance();
        break;
    case 'edit':
        editAttendance();
        break;
    case 'delete':
        deleteAttendance();
        break;
    default:
        setFlashMessage('attendance_message', 'Invalid action', 'danger');
        redirect(BASE_URL . '/modules/attendance/list.php');
}

/**
 * Add new attendance records
 */
function addAttendance() {
    global $conn;
    
    // Check required fields
    if (empty($_POST['meeting_id']) || !isset($_POST['members']) || empty($_POST['members'])) {
        setFlashMessage('attendance_message', 'Please select a meeting and at least one member', 'danger');
        redirect(BASE_URL . '/modules/attendance/add.php');
    }
    
    // Sanitize and validate input
    $meetingId = (int) $_POST['meeting_id'];
    $members = $_POST['members'];
    
    // Check if meeting exists
    $checkQuery = "SELECT meeting_id FROM meetings WHERE meeting_id = $meetingId";
    $checkResult = $conn->query($checkQuery);
    
    if (!$checkResult || $checkResult->num_rows === 0) {
        setFlashMessage('attendance_message', 'Meeting not found', 'danger');
        redirect(BASE_URL . '/modules/attendance/add.php');
    }
    
    // Count for success message
    $successCount = 0;
    $errorCount = 0;
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Process each selected member
        foreach ($members as $memberId => $data) {
            // Skip if not selected
            if (!isset($data['selected']) || $data['selected'] != 1) {
                continue;
            }
            
            $memberId = (int) $memberId;
            $status = sanitize($data['status'] ?? 'hadir');
            $keterangan = isset($data['keterangan']) ? sanitize($data['keterangan']) : null;
            
            // Check if record already exists
            $checkAttendanceQuery = "SELECT attendance_id FROM attendance WHERE member_id = $memberId AND meeting_id = $meetingId";
            $checkAttendanceResult = $conn->query($checkAttendanceQuery);
            
            if ($checkAttendanceResult && $checkAttendanceResult->num_rows > 0) {
                $errorCount++;
                continue; // Skip if already exists
            }
            
            // Insert attendance record
            $query = "
                INSERT INTO attendance (member_id, meeting_id, status_kehadiran, keterangan, waktu_absen)
                VALUES ($memberId, $meetingId, '$status', " . ($keterangan ? "'$keterangan'" : 'NULL') . ", NOW())
            ";
            
            if ($conn->query($query)) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Set success message
        if ($successCount > 0) {
            setFlashMessage('attendance_message', "Successfully recorded attendance for $successCount member(s)" . ($errorCount > 0 ? ", $errorCount member(s) could not be recorded" : ""), 'success');
        } else {
            setFlashMessage('attendance_message', 'No attendance records were added. Please check if members are already recorded.', 'warning');
        }
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        setFlashMessage('attendance_message', 'Error recording attendance: ' . $e->getMessage(), 'danger');
    }
    
    // Redirect to attendance list for this meeting
    redirect(BASE_URL . '/modules/attendance/list.php?meeting_id=' . $meetingId);
}

/**
 * Edit existing attendance record
 */
function editAttendance() {
    global $conn;
    
    // Check required fields
    if (empty($_POST['attendance_id']) || empty($_POST['status_kehadiran'])) {
        setFlashMessage('attendance_message', 'Please fill all required fields', 'danger');
        redirect(BASE_URL . '/modules/attendance/list.php');
    }
    
    // Sanitize and validate input
    $attendanceId = (int) $_POST['attendance_id'];
    $meetingId = (int) $_POST['meeting_id'];
    $memberId = (int) $_POST['member_id'];
    $status = sanitize($_POST['status_kehadiran']);
    $keterangan = !empty($_POST['keterangan']) ? sanitize($_POST['keterangan']) : NULL;
    
    // Update attendance record
    $query = "
        UPDATE attendance
        SET status_kehadiran = '$status',
            keterangan = " . ($keterangan ? "'$keterangan'" : 'NULL') . "
        WHERE attendance_id = $attendanceId
    ";
    
    if ($conn->query($query)) {
        setFlashMessage('attendance_message', 'Attendance record updated successfully', 'success');
    } else {
        setFlashMessage('attendance_message', 'Failed to update attendance record: ' . $conn->error, 'danger');
    }
    
    // Redirect to attendance list for this meeting
    redirect(BASE_URL . '/modules/attendance/list.php?meeting_id=' . $meetingId);
}

/**
 * Delete attendance record
 */
function deleteAttendance() {
    global $conn;
    
    // Check if ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        setFlashMessage('attendance_message', 'Attendance ID is required', 'danger');
        redirect(BASE_URL . '/modules/attendance/list.php');
    }
    
    $attendanceId = (int) $_GET['id'];
    
    // Get meeting_id for redirect
    $meetingIdQuery = "SELECT meeting_id FROM attendance WHERE attendance_id = $attendanceId";
    $meetingIdResult = $conn->query($meetingIdQuery);
    $meetingId = $meetingIdResult && $meetingIdResult->num_rows > 0 ? $meetingIdResult->fetch_assoc()['meeting_id'] : null;
    
    // Delete attendance record
    $query = "DELETE FROM attendance WHERE attendance_id = $attendanceId";
    
    if ($conn->query($query)) {
        setFlashMessage('attendance_message', 'Attendance record deleted successfully', 'success');
    } else {
        setFlashMessage('attendance_message', 'Failed to delete attendance record: ' . $conn->error, 'danger');
    }
    
    // Determine redirect location
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'list';
    
    if ($redirect === 'meeting' && $meetingId) {
        redirect(BASE_URL . '/modules/attendance/list.php?meeting_id=' . $meetingId);
    } else {
        redirect(BASE_URL . '/modules/attendance/list.php');
    }
}

// modules/attendance/report.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Set page title
$pageTitle = 'Attendance Reports';

// Get all divisions for filtering
$divisionsQuery = "SELECT * FROM divisions ORDER BY nama_divisi ASC";
$divisionsResult = $conn->query($divisionsQuery);

// Initialize filters
$divisionId = isset($_GET['division_id']) ? (int) $_GET['division_id'] : null;
$startDate = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : '';

// Build query based on filters
$query = "
    SELECT 
        m.member_id, m.nama, m.nim, d.nama_divisi,
        COUNT(DISTINCT mt.meeting_id) as total_meetings,
        SUM(CASE WHEN a.status_kehadiran = 'hadir' OR a.status_kehadiran = 'telat' THEN 1 ELSE 0 END) as total_present,
        SUM(CASE WHEN a.status_kehadiran = 'izin' THEN 1 ELSE 0 END) as total_excused,
        SUM(CASE WHEN a.status_kehadiran = 'alpa' THEN 1 ELSE 0 END) as total_absent,
        COALESCE(SUM(CASE WHEN a.status_kehadiran = 'hadir' OR a.status_kehadiran = 'telat' THEN 1 ELSE 0 END) / COUNT(DISTINCT mt.meeting_id) * 100, 0) as attendance_rate
    FROM 
        members m
    JOIN 
        divisions d ON m.division_id = d.division_id
    LEFT JOIN 
        attendance a ON m.member_id = a.member_id
    LEFT JOIN 
        meetings mt ON a.meeting_id = mt.meeting_id
";

// Add WHERE conditions based on filters
$whereConditions = [];

if ($divisionId) {
    $whereConditions[] = "m.division_id = $divisionId";
}

if ($startDate && $endDate) {
    $whereConditions[] = "mt.tanggal BETWEEN '$startDate' AND '$endDate'";
} elseif ($startDate) {
    $whereConditions[] = "mt.tanggal >= '$startDate'";
} elseif ($endDate) {
    $whereConditions[] = "mt.tanggal <= '$endDate'";
}

if (!empty($whereConditions)) {
    $query .= " WHERE " . implode(" AND ", $whereConditions);
}

$query .= " GROUP BY m.member_id ORDER BY d.nama_divisi ASC, m.nama ASC";

$result = $conn->query($query);

// Get total meetings count for percentage calculation
$totalMeetingsQuery = "
    SELECT COUNT(*) as total FROM meetings
";

if ($startDate && $endDate) {
    $totalMeetingsQuery .= " WHERE tanggal BETWEEN '$startDate' AND '$endDate'";
} elseif ($startDate) {
    $totalMeetingsQuery .= " WHERE tanggal >= '$startDate'";
} elseif ($endDate) {
    $totalMeetingsQuery .= " WHERE tanggal <= '$endDate'";
}

$totalMeetingsResult = $conn->query($totalMeetingsQuery);
$totalMeetings = $totalMeetingsResult->fetch_assoc()['total'];

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Attendance Reports</h1>
    <div>
        <button type="button" class="btn btn-outline-primary btn-print" onclick="window.print()">
            <i class="bi bi-printer"></i> Print Report
        </button>
        <button type="button" class="btn btn-outline-success" onclick="exportTableToExcel('attendanceTable', 'attendance_report')">
            <i class="bi bi-file-earmark-excel"></i> Export to Excel
        </button>
    </div>
</div>

<!-- Filter Card -->
<div class="card shadow mb-4 no-print">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Filter Options</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= $_SERVER['PHP_SELF'] ?>" class="row">
            <div class="col-md-3 mb-3">
                <label for="division_id" class="form-label">Division</label>
                <select class="form-select" id="division_id" name="division_id">
                    <option value="">All Divisions</option>
                    <?php if ($divisionsResult && $divisionsResult->num_rows > 0): ?>
                        <?php while ($division = $divisionsResult->fetch_assoc()): ?>
                            <option value="<?= $division['division_id'] ?>" <?= $divisionId == $division['division_id'] ? 'selected' : '' ?>>
                                <?= $division['nama_divisi'] ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $startDate ?>">
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $endDate ?>">
            </div>
            
            <div class="col-md-3 d-flex align-items-end mb-3">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-filter"></i> Apply Filters
                </button>
                <a href="report.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Report Summary -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Report Summary</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-3">
                        <h5>Total Meetings</h5>
                        <h2 class="text-primary"><?= $totalMeetings ?></h2>
                    </div>
                    
                    <div class="col-md-4 text-center mb-3">
                        <h5>Date Range</h5>
                        <h6 class="text-muted">
                            <?php if ($startDate && $endDate): ?>
                                <?= formatDate($startDate) ?> to <?= formatDate($endDate) ?>
                            <?php elseif ($startDate): ?>
                                From <?= formatDate($startDate) ?>
                            <?php elseif ($endDate): ?>
                                Until <?= formatDate($endDate) ?>
                            <?php else: ?>
                                All Time
                            <?php endif; ?>
                        </h6>
                    </div>
                    
                    <div class="col-md-4 text-center mb-3">
                        <h5>Division</h5>
                        <h6 class="text-muted">
                            <?php 
                            if ($divisionId) {
                                $divName = $conn->query("SELECT nama_divisi FROM divisions WHERE division_id = $divisionId")->fetch_assoc()['nama_divisi'];
                                echo $divName;
                            } else {
                                echo 'All Divisions';
                            }
                            ?>
                        </h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Report Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Attendance Report</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="attendanceTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>NIM</th>
                        <th>Division</th>
                        <th>Present</th>
                        <th>Excused</th>
                        <th>Absent</th>
                        <th>Total Meetings</th>
                        <th>Attendance Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php $no = 1; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $row['nama'] ?></td>
                                <td><?= $row['nim'] ?></td>
                                <td><?= $row['nama_divisi'] ?></td>
                                <td><?= $row['total_present'] ?></td>
                                <td><?= $row['total_excused'] ?></td>
                                <td><?= $row['total_absent'] ?></td>
                                <td><?= $row['total_meetings'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                style="width: <?= round($row['attendance_rate']) ?>%" 
                                                aria-valuenow="<?= round($row['attendance_rate']) ?>" 
                                                aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <span><?= round($row['attendance_rate']) ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No data found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Function to export table to Excel
    function exportTableToExcel(tableID, filename = '') {
        var downloadLink;
        var dataType = 'application/vnd.ms-excel';
        var tableSelect = document.getElementById(tableID);
        var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
        
        // Specify file name
        filename = filename ? filename + '.xls' : 'attendance_report.xls';
        
        // Create download link element
        downloadLink = document.createElement("a");
        
        document.body.appendChild(downloadLink);
        
        if (navigator.msSaveOrOpenBlob) {
            var blob = new Blob(['\ufeff', tableHTML], {
                type: dataType
            });
            navigator.msSaveOrOpenBlob(blob, filename);
        } else {
            // Create a link to the file
            downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
        
            // Setting the file name
            downloadLink.download = filename;
            
            // Triggering the function
            downloadLink.click();
        }
    }
</script>

<?php include_once '../../includes/footer.php'; ?>
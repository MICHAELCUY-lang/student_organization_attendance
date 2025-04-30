<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Set page title
$pageTitle = 'Record Attendance';

// Check if meeting_id is provided
$meetingId = isset($_GET['meeting_id']) ? (int)$_GET['meeting_id'] : null;

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

// Get members for attendance recording
$membersQuery = "
    SELECT m.*, d.nama_divisi
    FROM members m
    JOIN divisions d ON m.division_id = d.division_id
    ORDER BY d.nama_divisi ASC, m.nama ASC
";
$membersResult = $conn->query($membersQuery);

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <?php if ($meetingInfo): ?>
                Record Attendance: <?= $meetingInfo['judul_rapat'] ?>
            <?php else: ?>
                Record Attendance
            <?php endif; ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/modules/attendance/list.php">Attendance</a></li>
                <li class="breadcrumb-item active" aria-current="page">Record</li>
            </ol>
        </nav>
    </div>
    <a href="list.php<?= $meetingId ? '?meeting_id=' . $meetingId : '' ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
</div>

<?php displayFlashMessage('attendance_message'); ?>

<!-- Meeting Selection -->
<?php if (!$meetingInfo): ?>
<div class="card mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Select Meeting</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="">
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label for="meeting_id" class="form-label">Meeting <span class="text-danger">*</span></label>
                    <select class="form-select" id="meeting_id" name="meeting_id" required>
                        <option value="">Select Meeting</option>
                        <?php
                        $meetingsQuery = "SELECT meeting_id, judul_rapat, tanggal FROM meetings ORDER BY tanggal DESC, waktu_mulai DESC";
                        $meetingsResult = $conn->query($meetingsQuery);
                        if ($meetingsResult && $meetingsResult->num_rows > 0):
                            while ($meeting = $meetingsResult->fetch_assoc()):
                        ?>
                            <option value="<?= $meeting['meeting_id'] ?>">
                                <?= formatDate($meeting['tanggal']) ?> - <?= $meeting['judul_rapat'] ?>
                            </option>
                        <?php
                            endwhile;
                        endif;
                        ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Select Meeting
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Attendance Form -->
<?php if ($meetingInfo): ?>
<div class="card mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold">Record Attendance</h6>
        
        <!-- Meeting Details -->
        <span class="text-muted">
            <i class="bi bi-calendar3 me-1"></i> <?= formatDate($meetingInfo['tanggal']) ?>
            <i class="bi bi-clock ms-2 me-1"></i> <?= formatTime($meetingInfo['waktu_mulai']) ?> - <?= formatTime($meetingInfo['waktu_selesai']) ?>
        </span>
    </div>
    <div class="card-body">
        <form action="process.php" method="POST">
            <input type="hidden" name="action" value="record">
            <input type="hidden" name="meeting_id" value="<?= $meetingInfo['meeting_id'] ?>">
            
            <!-- Search Box -->
            <div class="mb-4">
                <div class="input-group search-input-container">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control search-input" id="searchMembers" placeholder="Search members..." data-search-target=".member-item">
                </div>
            </div>
            
            <!-- Select All -->
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAll">
                    <label class="form-check-label fw-bold" for="selectAll">
                        Select All Members
                    </label>
                </div>
            </div>
            
            <!-- Members List -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60">Select</th>
                            <th>Name</th>
                            <th>NIM</th>
                            <th>Division</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($membersResult && $membersResult->num_rows > 0): ?>
                            <?php
                            // Check existing attendance records
                            $existingRecordsQuery = "SELECT member_id, status_kehadiran, keterangan FROM attendance WHERE meeting_id = $meetingId";
                            $existingRecordsResult = $conn->query($existingRecordsQuery);
                            $existingRecords = [];
                            
                            if ($existingRecordsResult && $existingRecordsResult->num_rows > 0) {
                                while ($record = $existingRecordsResult->fetch_assoc()) {
                                    $existingRecords[$record['member_id']] = [
                                        'status' => $record['status_kehadiran'],
                                        'notes' => $record['keterangan']
                                    ];
                                }
                            }
                            ?>
                            
                            <?php while ($member = $membersResult->fetch_assoc()): ?>
                                <?php
                                $hasRecord = isset($existingRecords[$member['member_id']]);
                                $recordStatus = $hasRecord ? $existingRecords[$member['member_id']]['status'] : '';
                                $recordNotes = $hasRecord ? $existingRecords[$member['member_id']]['notes'] : '';
                                ?>
                                <tr class="member-item">
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input checkbox-item" type="checkbox" 
                                                id="member_<?= $member['member_id'] ?>" 
                                                name="members[]" 
                                                value="<?= $member['member_id'] ?>"
                                                <?= $hasRecord ? 'checked' : '' ?>
                                                <?= $hasRecord ? 'disabled' : '' ?>>
                                        </div>
                                    </td>
                                    <td>
                                        <label for="member_<?= $member['member_id'] ?>" class="fw-medium mb-0 cursor-pointer">
                                            <?= $member['nama'] ?>
                                        </label>
                                    </td>
                                    <td><?= $member['nim'] ?></td>
                                    <td>
                                        <span class="badge bg-info"><?= $member['nama_divisi'] ?></span>
                                    </td>
                                    <td>
                                        <div id="status_<?= $member['member_id'] ?>" <?= (!$hasRecord && !isset($_POST['members']) || !in_array($member['member_id'], $_POST['members'] ?? [])) ? 'style="display:none;"' : '' ?>>
                                            <?php if ($hasRecord): ?>
                                                <?php if ($recordStatus == 'hadir'): ?>
                                                    <span class="badge bg-success">Present</span>
                                                <?php elseif ($recordStatus == 'telat'): ?>
                                                    <span class="badge bg-info">Late</span>
                                                <?php elseif ($recordStatus == 'izin'): ?>
                                                    <span class="badge bg-warning">Excused</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Absent</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <select class="form-select form-select-sm" name="status[<?= $member['member_id'] ?>]">
                                                    <option value="hadir">Present</option>
                                                    <option value="telat">Late</option>
                                                    <option value="izin">Excused</option>
                                                    <option value="alpa">Absent</option>
                                                </select>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div id="notes_<?= $member['member_id'] ?>" <?= (!$hasRecord && !isset($_POST['members']) || !in_array($member['member_id'], $_POST['members'] ?? [])) ? 'style="display:none;"' : '' ?>>
                                            <?php if ($hasRecord): ?>
                                                <?= $recordNotes ?: '<span class="text-muted">-</span>' ?>
                                            <?php else: ?>
                                                <input type="text" class="form-control form-control-sm" name="notes[<?= $member['member_id'] ?>]" placeholder="Optional notes">
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-people fs-3 d-block mb-2"></i>
                                        No members found
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 d-flex justify-content-between">
                <a href="list.php<?= $meetingId ? '?meeting_id=' . $meetingId : '' ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-save me-1"></i> Save Attendance
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php include_once '../../includes/footer.php'; ?>
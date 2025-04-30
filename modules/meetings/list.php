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
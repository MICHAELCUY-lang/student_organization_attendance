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
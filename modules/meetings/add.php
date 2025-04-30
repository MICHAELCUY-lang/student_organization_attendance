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
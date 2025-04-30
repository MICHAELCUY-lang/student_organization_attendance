// modules/documents/upload.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Set page title
$pageTitle = 'Upload Document';

// Get all meetings for dropdown
$meetingsQuery = "SELECT * FROM meetings ORDER BY tanggal DESC, waktu_mulai DESC";
$meetingsResult = $conn->query($meetingsQuery);

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Upload New Document</h1>
    <a href="list.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Document Information</h6>
    </div>
    <div class="card-body">
        <form action="process.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload">
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="meeting_id" class="form-label">Meeting <span class="text-danger">*</span></label>
                    <select class="form-select" id="meeting_id" name="meeting_id" required>
                        <option value="">Select Meeting</option>
                        <?php if ($meetingsResult && $meetingsResult->num_rows > 0): ?>
                            <?php while ($meeting = $meetingsResult->fetch_assoc()): ?>
                                <option value="<?= $meeting['meeting_id'] ?>">
                                    <?= $meeting['judul_rapat'] ?> (<?= formatDate($meeting['tanggal']) ?>)
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="nama_file" class="form-label">Document Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama_file" name="nama_file" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="document_file" class="form-label">Document File <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="document_file" name="document_file" required>
                    <div class="form-text">
                        Allowed file types: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT (Max size: 5MB)
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-upload"></i> Upload Document
                </button>
                <a href="list.php" class="btn btn-danger">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
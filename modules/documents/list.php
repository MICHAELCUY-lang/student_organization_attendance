// modules/documents/list.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Set page title
$pageTitle = 'Documents List';

// Get all documents with meeting info
$query = "
    SELECT d.*, m.judul_rapat, m.tanggal
    FROM documents d
    JOIN meetings m ON d.meeting_id = m.meeting_id
    ORDER BY d.document_id DESC
";
$result = $conn->query($query);

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Documents Management</h1>
    <a href="upload.php" class="btn btn-primary">
        <i class="bi bi-file-earmark-plus"></i> Upload New Document
    </a>
</div>

<?php displayFlashMessage('document_message'); ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Documents List</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Document Name</th>
                        <th>Meeting</th>
                        <th>Meeting Date</th>
                        <th>File Path</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($document = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $document['document_id'] ?></td>
                                <td><?= $document['nama_file'] ?></td>
                                <td><?= $document['judul_rapat'] ?></td>
                                <td><?= formatDate($document['tanggal']) ?></td>
                                <td><small class="text-muted"><?= $document['path_file'] ?></small></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/uploads/documents/<?= $document['path_file'] ?>" class="btn btn-sm btn-primary" target="_blank">
                                        <i class="bi bi-file-earmark-text"></i> View
                                    </a>
                                    <a href="<?= BASE_URL ?>/uploads/documents/<?= $document['path_file'] ?>" class="btn btn-sm btn-success" download="<?= $document['nama_file'] ?>">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                    <a href="process.php?action=delete&id=<?= $document['document_id'] ?>" class="btn btn-sm btn-danger btn-delete">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No documents found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>

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

// modules/documents/process.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Check if action is provided
if (!isset($_POST['action']) && !isset($_GET['action'])) {
    setFlashMessage('document_message', 'Invalid request', 'danger');
    redirect(BASE_URL . '/modules/documents/list.php');
}

// Determine the action
$action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];

switch ($action) {
    case 'upload':
        uploadDocument();
        break;
    case 'delete':
        deleteDocument();
        break;
    default:
        setFlashMessage('document_message', 'Invalid action', 'danger');
        redirect(BASE_URL . '/modules/documents/list.php');
}

/**
 * Upload new document
 */
function uploadDocument() {
    global $conn;
    
    // Check required fields
    if (empty($_POST['meeting_id']) || empty($_POST['nama_file']) || empty($_FILES['document_file']['name'])) {
        setFlashMessage('document_message', 'Please fill all required fields and select a file', 'danger');
        redirect(BASE_URL . '/modules/documents/upload.php');
    }
    
    // Sanitize and validate input
    $meetingId = (int) $_POST['meeting_id'];
    $namaFile = sanitize($_POST['nama_file']);
    
    // Check if meeting exists
    $checkQuery = "SELECT meeting_id FROM meetings WHERE meeting_id = $meetingId";
    $checkResult = $conn->query($checkQuery);
    
    if (!$checkResult || $checkResult->num_rows === 0) {
        setFlashMessage('document_message', 'Meeting not found', 'danger');
        redirect(BASE_URL . '/modules/documents/upload.php');
    }
    
    // Validate file
    $file = $_FILES['document_file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    // Get file extension
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Allowed file extensions
    $allowedExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
    
    // Check if extension is allowed
    if (!in_array($fileExt, $allowedExts)) {
        setFlashMessage('document_message', 'Invalid file type. Allowed types: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT', 'danger');
        redirect(BASE_URL . '/modules/documents/upload.php');
    }
    
    // Check file size (5MB max)
    if ($fileSize > 5 * 1024 * 1024) {
        setFlashMessage('document_message', 'File is too large. Maximum size is 5MB', 'danger');
        redirect(BASE_URL . '/modules/documents/upload.php');
    }
    
    // Check for upload errors
    if ($fileError !== 0) {
        setFlashMessage('document_message', 'Error uploading file. Please try again.', 'danger');
        redirect(BASE_URL . '/modules/documents/upload.php');
    }
    
    // Create unique filename to prevent overwriting
    $newFileName = uniqid('doc_') . '.' . $fileExt;
    $uploadPath = UPLOAD_PATH . $newFileName;
    
    // Create uploads directory if it doesn't exist
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0777, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($fileTmpName, $uploadPath)) {
        // Insert document record
        $query = "
            INSERT INTO documents (meeting_id, nama_file, path_file)
            VALUES ($meetingId, '$namaFile', '$newFileName')
        ";
        
        if ($conn->query($query)) {
            setFlashMessage('document_message', 'Document uploaded successfully', 'success');
            redirect(BASE_URL . '/modules/documents/list.php');
        } else {
            // Delete uploaded file if database insertion fails
            unlink($uploadPath);
            setFlashMessage('document_message', 'Failed to save document record: ' . $conn->error, 'danger');
            redirect(BASE_URL . '/modules/documents/upload.php');
        }
    } else {
        setFlashMessage('document_message', 'Failed to upload file. Please try again.', 'danger');
        redirect(BASE_URL . '/modules/documents/upload.php');
    }
}

/**
 * Delete document
 */
function deleteDocument() {
    global $conn;
    
    // Check if ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        setFlashMessage('document_message', 'Document ID is required', 'danger');
        redirect(BASE_URL . '/modules/documents/list.php');
    }
    
    $documentId = (int) $_GET['id'];
    
    // Get document info
    $query = "SELECT * FROM documents WHERE document_id = $documentId";
    $result = $conn->query($query);
    
    if (!$result || $result->num_rows === 0) {
        setFlashMessage('document_message', 'Document not found', 'danger');
        redirect(BASE_URL . '/modules/documents/list.php');
    }
    
    $document = $result->fetch_assoc();
    $filePath = UPLOAD_PATH . $document['path_file'];
    
    // Delete file if it exists
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    
    // Delete document record
    $deleteQuery = "DELETE FROM documents WHERE document_id = $documentId";
    
    if ($conn->query($deleteQuery)) {
        setFlashMessage('document_message', 'Document deleted successfully', 'success');
    } else {
        setFlashMessage('document_message', 'Failed to delete document: ' . $conn->error, 'danger');
    }
    
    redirect(BASE_URL . '/modules/documents/list.php');
}
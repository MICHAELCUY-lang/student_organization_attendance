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

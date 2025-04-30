<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Check if user has admin role
requireRole('admin');

// Set page title
$pageTitle = 'Edit Division';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setFlashMessage('division_message', 'Division ID is required', 'danger');
    redirect(BASE_URL . '/modules/divisions/list.php');
}

$divisionId = (int) $_GET['id'];

// Get division data
$query = "SELECT * FROM divisions WHERE division_id = $divisionId";
$result = $conn->query($query);

// Check if division exists
if (!$result || $result->num_rows === 0) {
    setFlashMessage('division_message', 'Division not found', 'danger');
    redirect(BASE_URL . '/modules/divisions/list.php');
}

$division = $result->fetch_assoc();

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Edit Division</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/modules/divisions/list.php">Divisions</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Division</li>
            </ol>
        </nav>
    </div>
    <a href="list.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
</div>

<?php displayFlashMessage('division_message'); ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Division Information</h6>
    </div>
    <div class="card-body">
        <form action="process.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="division_id" value="<?= $division['division_id'] ?>">
            
            <div class="mb-3">
                <label for="nama_divisi" class="form-label">Division Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama_divisi" name="nama_divisi" value="<?= $division['nama_divisi'] ?>" required>
                <div class="form-text">Enter a unique name for the division</div>
            </div>
            
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Description</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= $division['deskripsi'] ?></textarea>
                <div class="form-text">Optional description of the division's responsibilities</div>
            </div>
            
            <!-- Display member count if needed -->
            <?php
            $memberCountQuery = "SELECT COUNT(*) as count FROM members WHERE division_id = $divisionId";
            $memberCountResult = $conn->query($memberCountQuery);
            $memberCount = $memberCountResult ? $memberCountResult->fetch_assoc()['count'] : 0;
            ?>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-1"></i>
                This division has <?= $memberCount ?> member<?= $memberCount != 1 ? 's' : '' ?> assigned to it.
            </div>
            
            <div class="mt-4 d-flex justify-content-between">
                <a href="list.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Update Division
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
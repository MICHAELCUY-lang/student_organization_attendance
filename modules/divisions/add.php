<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Check if user has admin role
requireRole('admin');

// Set page title
$pageTitle = 'Add Division';

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Add New Division</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/modules/divisions/list.php">Divisions</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add Division</li>
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
            <input type="hidden" name="action" value="add">
            
            <div class="mb-3">
                <label for="nama_divisi" class="form-label">Division Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama_divisi" name="nama_divisi" required>
                <div class="form-text">Enter a unique name for the division</div>
            </div>
            
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Description</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                <div class="form-text">Optional description of the division's responsibilities</div>
            </div>
            
            <div class="mt-4 d-flex justify-content-between">
                <a href="list.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Save Division
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
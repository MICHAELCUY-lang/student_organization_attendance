// modules/members/add.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Set page title
$pageTitle = 'Add Member';

// Get all divisions for dropdown
$divisionsQuery = "SELECT * FROM divisions ORDER BY nama_divisi ASC";
$divisionsResult = $conn->query($divisionsQuery);

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add New Member</h1>
    <a href="list.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Member Information</h6>
    </div>
    <div class="card-body">
        <form action="process.php" method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nama" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama" name="nama" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="nim" class="form-label">NIM <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nim" name="nim" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="jurusan" class="form-label">Department</label>
                    <input type="text" class="form-control" id="jurusan" name="jurusan">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="angkatan" class="form-label">Batch Year</label>
                    <input type="number" class="form-control" id="angkatan" name="angkatan" min="2000" max="<?= date('Y') ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="no_hp" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="no_hp" name="no_hp">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="division_id" class="form-label">Division <span class="text-danger">*</span></label>
                    <select class="form-select" id="division_id" name="division_id" required>
                        <option value="">Select Division</option>
                        <?php if ($divisionsResult && $divisionsResult->num_rows > 0): ?>
                            <?php while ($division = $divisionsResult->fetch_assoc()): ?>
                                <option value="<?= $division['division_id'] ?>"><?= $division['nama_divisi'] ?></option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Member
                </button>
                <a href="list.php" class="btn btn-danger">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
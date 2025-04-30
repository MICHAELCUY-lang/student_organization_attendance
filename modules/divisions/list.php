// modules/divisions/list.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Check if user has admin role
requireRole('admin');

// Set page title
$pageTitle = 'Divisions List';

// Get all divisions with member counts
$query = "
    SELECT d.*, COUNT(m.member_id) as member_count
    FROM divisions d
    LEFT JOIN members m ON d.division_id = m.division_id
    GROUP BY d.division_id
    ORDER BY d.nama_divisi ASC
";
$result = $conn->query($query);

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Divisions Management</h1>
    <a href="add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add New Division
    </a>
</div>

<?php displayFlashMessage('division_message'); ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Divisions List</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Division Name</th>
                        <th>Description</th>
                        <th>Members</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($division = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $division['division_id'] ?></td>
                                <td><?= $division['nama_divisi'] ?></td>
                                <td><?= $division['deskripsi'] ? $division['deskripsi'] : '-' ?></td>
                                <td>
                                    <a href="../members/list.php?division_id=<?= $division['division_id'] ?>" class="badge bg-primary">
                                        <?= $division['member_count'] ?> members
                                    </a>
                                </td>
                                <td>
                                    <a href="edit.php?id=<?= $division['division_id'] ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <?php if ($division['member_count'] == 0): ?>
                                        <a href="process.php?action=delete&id=<?= $division['division_id'] ?>" class="btn btn-sm btn-danger btn-delete">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled title="Cannot delete division with members">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No divisions found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>

// modules/divisions/add.php
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
    <h1 class="h3 mb-0">Add New Division</h1>
    <a href="list.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Division Information</h6>
    </div>
    <div class="card-body">
        <form action="process.php" method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="nama_divisi" class="form-label">Division Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama_divisi" name="nama_divisi" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="deskripsi" class="form-label">Description</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Division
                </button>
                <a href="list.php" class="btn btn-danger">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>

// modules/divisions/edit.php
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
$query = "
    SELECT * FROM divisions 
    WHERE division_id = $divisionId
";
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
    <h1 class="h3 mb-0">Edit Division</h1>
    <a href="list.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Division Information</h6>
    </div>
    <div class="card-body">
        <form action="process.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="division_id" value="<?= $division['division_id'] ?>">
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="nama_divisi" class="form-label">Division Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama_divisi" name="nama_divisi" value="<?= $division['nama_divisi'] ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="deskripsi" class="form-label">Description</label>
                    // Continuing modules/divisions/edit.php
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= $division['deskripsi'] ?></textarea>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update Division
                </button>
                <a href="list.php" class="btn btn-danger">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>

// modules/divisions/process.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Check if user has admin role
requireRole('admin');

// Check if action is provided
if (!isset($_POST['action']) && !isset($_GET['action'])) {
    setFlashMessage('division_message', 'Invalid request', 'danger');
    redirect(BASE_URL . '/modules/divisions/list.php');
}

// Determine the action
$action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];

switch ($action) {
    case 'add':
        addDivision();
        break;
    case 'edit':
        editDivision();
        break;
    case 'delete':
        deleteDivision();
        break;
    default:
        setFlashMessage('division_message', 'Invalid action', 'danger');
        redirect(BASE_URL . '/modules/divisions/list.php');
}

/**
 * Add new division
 */
function addDivision() {
    global $conn;
    
    // Check required fields
    if (empty($_POST['nama_divisi'])) {
        setFlashMessage('division_message', 'Division name is required', 'danger');
        redirect(BASE_URL . '/modules/divisions/add.php');
    }
    
    // Sanitize and validate input
    $namaDivisi = sanitize($_POST['nama_divisi']);
    $deskripsi = !empty($_POST['deskripsi']) ? sanitize($_POST['deskripsi']) : NULL;
    
    // Check if division name already exists
    $checkQuery = "SELECT division_id FROM divisions WHERE nama_divisi = '$namaDivisi'";
    $checkResult = $conn->query($checkQuery);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        setFlashMessage('division_message', 'Division name already exists. Please use a different name.', 'danger');
        redirect(BASE_URL . '/modules/divisions/add.php');
    }
    
    // Insert new division
    $query = "
        INSERT INTO divisions (nama_divisi, deskripsi)
        VALUES ('$namaDivisi', " . ($deskripsi ? "'$deskripsi'" : 'NULL') . ")
    ";
    
    if ($conn->query($query)) {
        setFlashMessage('division_message', 'Division added successfully', 'success');
    } else {
        setFlashMessage('division_message', 'Failed to add division: ' . $conn->error, 'danger');
    }
    
    redirect(BASE_URL . '/modules/divisions/list.php');
}

/**
 * Edit existing division
 */
function editDivision() {
    global $conn;
    
    // Check required fields
    if (empty($_POST['division_id']) || empty($_POST['nama_divisi'])) {
        setFlashMessage('division_message', 'Division ID and name are required', 'danger');
        redirect(BASE_URL . '/modules/divisions/list.php');
    }
    
    // Sanitize and validate input
    $divisionId = (int) $_POST['division_id'];
    $namaDivisi = sanitize($_POST['nama_divisi']);
    $deskripsi = !empty($_POST['deskripsi']) ? sanitize($_POST['deskripsi']) : NULL;
    
    // Check if division name already exists for other divisions
    $checkQuery = "SELECT division_id FROM divisions WHERE nama_divisi = '$namaDivisi' AND division_id != $divisionId";
    $checkResult = $conn->query($checkQuery);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        setFlashMessage('division_message', 'Division name already exists. Please use a different name.', 'danger');
        redirect(BASE_URL . '/modules/divisions/edit.php?id=' . $divisionId);
    }
    
    // Update division
    $query = "
        UPDATE divisions
        SET nama_divisi = '$namaDivisi',
            deskripsi = " . ($deskripsi ? "'$deskripsi'" : 'NULL') . "
        WHERE division_id = $divisionId
    ";
    
    if ($conn->query($query)) {
        setFlashMessage('division_message', 'Division updated successfully', 'success');
    } else {
        setFlashMessage('division_message', 'Failed to update division: ' . $conn->error, 'danger');
    }
    
    redirect(BASE_URL . '/modules/divisions/list.php');
}

/**
 * Delete division
 */
function deleteDivision() {
    global $conn;
    
    // Check if ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        setFlashMessage('division_message', 'Division ID is required', 'danger');
        redirect(BASE_URL . '/modules/divisions/list.php');
    }
    
    $divisionId = (int) $_GET['id'];
    
    // Check if division exists
    $checkQuery = "SELECT division_id FROM divisions WHERE division_id = $divisionId";
    $checkResult = $conn->query($checkQuery);
    
    if (!$checkResult || $checkResult->num_rows === 0) {
        setFlashMessage('division_message', 'Division not found', 'danger');
        redirect(BASE_URL . '/modules/divisions/list.php');
    }
    
    // Check if division has members
    $memberCheckQuery = "SELECT COUNT(*) as member_count FROM members WHERE division_id = $divisionId";
    $memberCheckResult = $conn->query($memberCheckQuery);
    $memberCount = $memberCheckResult->fetch_assoc()['member_count'];
    
    if ($memberCount > 0) {
        setFlashMessage('division_message', 'Cannot delete division with members. Please reassign members first.', 'danger');
        redirect(BASE_URL . '/modules/divisions/list.php');
    }
    
    // Delete division
    $query = "DELETE FROM divisions WHERE division_id = $divisionId";
    
    if ($conn->query($query)) {
        setFlashMessage('division_message', 'Division deleted successfully', 'success');
    } else {
        setFlashMessage('division_message', 'Failed to delete division: ' . $conn->error, 'danger');
    }
    
    redirect(BASE_URL . '/modules/divisions/list.php');
}
// modules/members/list.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Set page title
$pageTitle = 'Members List';

// Get all members with divisions
$query = "
    SELECT m.*, d.nama_divisi 
    FROM members m
    JOIN divisions d ON m.division_id = d.division_id
    ORDER BY m.nama ASC
";
$result = $conn->query($query);

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Members Management</h1>
    <a href="add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add New Member
    </a>
</div>

<?php displayFlashMessage('member_message'); ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Members List</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>NIM</th>
                        <th>Division</th>
                        <th>Department</th>
                        <th>Batch</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($member = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $member['member_id'] ?></td>
                                <td><?= $member['nama'] ?></td>
                                <td><?= $member['nim'] ?></td>
                                <td><?= $member['nama_divisi'] ?></td>
                                <td><?= $member['jurusan'] ?></td>
                                <td><?= $member['angkatan'] ?></td>
                                <td><?= $member['no_hp'] ?></td>
                                <td>
                                    <a href="edit.php?id=<?= $member['member_id'] ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <a href="process.php?action=delete&id=<?= $member['member_id'] ?>" class="btn btn-sm btn-danger btn-delete">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No members found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>

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

// modules/members/edit.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Set page title
$pageTitle = 'Edit Member';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setFlashMessage('member_message', 'Member ID is required', 'danger');
    redirect(BASE_URL . '/modules/members/list.php');
}

$memberId = (int) $_GET['id'];

// Get member data
$query = "
    SELECT * FROM members 
    WHERE member_id = $memberId
";
$result = $conn->query($query);

// Check if member exists
if (!$result || $result->num_rows === 0) {
    setFlashMessage('member_message', 'Member not found', 'danger');
    redirect(BASE_URL . '/modules/members/list.php');
}

$member = $result->fetch_assoc();

// Get all divisions for dropdown
$divisionsQuery = "SELECT * FROM divisions ORDER BY nama_divisi ASC";
$divisionsResult = $conn->query($divisionsQuery);

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit Member</h1>
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
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="member_id" value="<?= $member['member_id'] ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nama" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama" name="nama" value="<?= $member['nama'] ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="nim" class="form-label">NIM <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nim" name="nim" value="<?= $member['nim'] ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="jurusan" class="form-label">Department</label>
                    <input type="text" class="form-control" id="jurusan" name="jurusan" value="<?= $member['jurusan'] ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="angkatan" class="form-label">Batch Year</label>
                    <input type="number" class="form-control" id="angkatan" name="angkatan" min="2000" max="<?= date('Y') ?>" value="<?= $member['angkatan'] ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="no_hp" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="no_hp" name="no_hp" value="<?= $member['no_hp'] ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="division_id" class="form-label">Division <span class="text-danger">*</span></label>
                    <select class="form-select" id="division_id" name="division_id" required>
                        <option value="">Select Division</option>
                        <?php if ($divisionsResult && $divisionsResult->num_rows > 0): ?>
                            <?php while ($division = $divisionsResult->fetch_assoc()): ?>
                                <option value="<?= $division['division_id'] ?>" <?= ($member['division_id'] == $division['division_id']) ? 'selected' : '' ?>>
                                    <?= $division['nama_divisi'] ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update Member
                </button>
                <a href="list.php" class="btn btn-danger">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>

// modules/members/process.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Check if action is provided
if (!isset($_POST['action']) && !isset($_GET['action'])) {
    setFlashMessage('member_message', 'Invalid request', 'danger');
    redirect(BASE_URL . '/modules/members/list.php');
}

// Determine the action
$action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];

switch ($action) {
    case 'add':
        addMember();
        break;
    case 'edit':
        editMember();
        break;
    case 'delete':
        deleteMember();
        break;
    default:
        setFlashMessage('member_message', 'Invalid action', 'danger');
        redirect(BASE_URL . '/modules/members/list.php');
}

/**
 * Add new member
 */
function addMember() {
    global $conn;
    
    // Check required fields
    if (empty($_POST['nama']) || empty($_POST['nim']) || empty($_POST['division_id'])) {
        setFlashMessage('member_message', 'Please fill all required fields', 'danger');
        redirect(BASE_URL . '/modules/members/add.php');
    }
    
    // Sanitize and validate input
    $nama = sanitize($_POST['nama']);
    $nim = sanitize($_POST['nim']);
    $jurusan = sanitize($_POST['jurusan']);
    $angkatan = !empty($_POST['angkatan']) ? (int) $_POST['angkatan'] : NULL;
    $noHp = sanitize($_POST['no_hp']);
    $divisionId = (int) $_POST['division_id'];
    
    // Check if NIM already exists
    $checkQuery = "SELECT member_id FROM members WHERE nim = '$nim'";
    $checkResult = $conn->query($checkQuery);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        setFlashMessage('member_message', 'NIM already exists. Please use a different NIM.', 'danger');
        redirect(BASE_URL . '/modules/members/add.php');
    }
    
    // Insert new member
    $query = "
        INSERT INTO members (nama, nim, jurusan, angkatan, no_hp, division_id)
        VALUES ('$nama', '$nim', '$jurusan', " . ($angkatan ? $angkatan : 'NULL') . ", '$noHp', $divisionId)
    ";
    
    if ($conn->query($query)) {
        setFlashMessage('member_message', 'Member added successfully', 'success');
    } else {
        setFlashMessage('member_message', 'Failed to add member: ' . $conn->error, 'danger');
    }
    
    redirect(BASE_URL . '/modules/members/list.php');
}

/**
 * Edit existing member
 */
function editMember() {
    global $conn;
    
    // Check required fields
    if (empty($_POST['member_id']) || empty($_POST['nama']) || empty($_POST['nim']) || empty($_POST['division_id'])) {
        setFlashMessage('member_message', 'Please fill all required fields', 'danger');
        redirect(BASE_URL . '/modules/members/list.php');
    }
    
    // Sanitize and validate input
    $memberId = (int) $_POST['member_id'];
    $nama = sanitize($_POST['nama']);
    $nim = sanitize($_POST['nim']);
    $jurusan = sanitize($_POST['jurusan']);
    $angkatan = !empty($_POST['angkatan']) ? (int) $_POST['angkatan'] : NULL;
    $noHp = sanitize($_POST['no_hp']);
    $divisionId = (int) $_POST['division_id'];
    
    // Check if NIM already exists for other members
    $checkQuery = "SELECT member_id FROM members WHERE nim = '$nim' AND member_id != $memberId";
    $checkResult = $conn->query($checkQuery);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        setFlashMessage('member_message', 'NIM already exists. Please use a different NIM.', 'danger');
        redirect(BASE_URL . '/modules/members/edit.php?id=' . $memberId);
    }
    
    // Update member
    $query = "
        UPDATE members
        SET nama = '$nama',
            nim = '$nim',
            jurusan = '$jurusan',
            angkatan = " . ($angkatan ? $angkatan : 'NULL') . ",
            no_hp = '$noHp',
            division_id = $divisionId
        WHERE member_id = $memberId
    ";
    
    if ($conn->query($query)) {
        setFlashMessage('member_message', 'Member updated successfully', 'success');
    } else {
        setFlashMessage('member_message', 'Failed to update member: ' . $conn->error, 'danger');
    }
    
    redirect(BASE_URL . '/modules/members/list.php');
}

/**
 * Delete member
 */
function deleteMember() {
    global $conn;
    
    // Check if ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        setFlashMessage('member_message', 'Member ID is required', 'danger');
        redirect(BASE_URL . '/modules/members/list.php');
    }
    
    $memberId = (int) $_GET['id'];
    
    // Check if member exists
    $checkQuery = "SELECT member_id FROM members WHERE member_id = $memberId";
    $checkResult = $conn->query($checkQuery);
    
    if (!$checkResult || $checkResult->num_rows === 0) {
        setFlashMessage('member_message', 'Member not found', 'danger');
        redirect(BASE_URL . '/modules/members/list.php');
    }
    
    // Delete member
    $query = "DELETE FROM members WHERE member_id = $memberId";
    
    if ($conn->query($query)) {
        setFlashMessage('member_message', 'Member deleted successfully', 'success');
    } else {
        setFlashMessage('member_message', 'Failed to delete member: ' . $conn->error, 'danger');
    }
    
    redirect(BASE_URL . '/modules/members/list.php');
}
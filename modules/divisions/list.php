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
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Set page title
$pageTitle = 'Members List';

// Get filter parameters
$divisonId = isset($_GET['division_id']) ? (int)$_GET['division_id'] : null;
$searchTerm = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Get all divisions for the filter dropdown
$divisionsQuery = "SELECT * FROM divisions ORDER BY nama_divisi ASC";
$divisionsResult = $conn->query($divisionsQuery);

// Build query with potential filters
$query = "
    SELECT m.*, d.nama_divisi 
    FROM members m
    JOIN divisions d ON m.division_id = d.division_id
";

// Add WHERE clauses if filters are set
$whereClauses = [];
if ($divisonId) {
    $whereClauses[] = "m.division_id = $divisonId";
}
if ($searchTerm) {
    $whereClauses[] = "(m.nama LIKE '%$searchTerm%' OR m.nim LIKE '%$searchTerm%' OR m.jurusan LIKE '%$searchTerm%')";
}

if (!empty($whereClauses)) {
    $query .= " WHERE " . implode(" AND ", $whereClauses);
}

$query .= " ORDER BY m.nama ASC";
$result = $conn->query($query);

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM members";
if ($divisonId) {
    $countQuery .= " WHERE division_id = $divisonId";
}
$countResult = $conn->query($countQuery);
$totalMembers = $countResult ? $countResult->fetch_assoc()['total'] : 0;

// Include header
include_once '../../includes/header.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Members Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Members</li>
            </ol>
        </nav>
    </div>
    <a href="add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Add New Member
    </a>
</div>

<?php displayFlashMessage('member_message'); ?>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= $_SERVER['PHP_SELF'] ?>" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" name="search" placeholder="Search by name, NIM or department" value="<?= $searchTerm ?>">
                </div>
            </div>
            
            <div class="col-md-3">
                <select class="form-select" name="division_id">
                    <option value="">All Divisions</option>
                    <?php if ($divisionsResult && $divisionsResult->num_rows > 0): ?>
                        <?php while($division = $divisionsResult->fetch_assoc()): ?>
                            <option value="<?= $division['division_id'] ?>" <?= $divisonId == $division['division_id'] ? 'selected' : '' ?>>
                                <?= $division['nama_divisi'] ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="col-md-5">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter me-1"></i> Apply Filters
                    </button>
                    <a href="list.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Clear
                    </a>
                    
                    <div class="ms-auto d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-print" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Print
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="exportTableToExcel('membersTable', 'members_list')">
                            <i class="bi bi-file-earmark-excel me-1"></i> Export
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Members Table -->
<div class="card shadow-sm">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Members List</h5>
        <span class="badge bg-primary rounded-pill"><?= $totalMembers ?> total members</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="membersTable">
                <thead class="table-light">
                    <tr>
                        <th width="50">ID</th>
                        <th>Name</th>
                        <th>NIM</th>
                        <th>Division</th>
                        <th>Department</th>
                        <th>Batch</th>
                        <th>Phone</th>
                        <th width="140" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($member = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $member['member_id'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-primary me-2 d-flex align-items-center justify-content-center rounded-circle">
                                            <span class="text-white"><?= strtoupper(substr($member['nama'], 0, 1)) ?></span>
                                        </div>
                                        <div><?= $member['nama'] ?></div>
                                    </div>
                                </td>
                                <td><?= $member['nim'] ?></td>
                                <td>
                                    <span class="badge bg-info"><?= $member['nama_divisi'] ?></span>
                                </td>
                                <td><?= $member['jurusan'] ?: '-' ?></td>
                                <td><?= $member['angkatan'] ?: '-' ?></td>
                                <td><?= $member['no_hp'] ?: '-' ?></td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="edit.php?id=<?= $member['member_id'] ?>" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="../attendance/list.php?member_id=<?= $member['member_id'] ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Attendance">
                                            <i class="bi bi-calendar-check"></i>
                                        </a>
                                        <a href="process.php?action=delete&id=<?= $member['member_id'] ?>" class="btn btn-sm btn-outline-danger btn-delete" data-bs-toggle="tooltip"title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center py-5">
                                    <i class="bi bi-people text-muted mb-3" style="font-size: 2.5rem;"></i>
                                    <h5 class="text-muted mb-3">No members found</h5>
                                    <p class="text-muted mb-3">Add new members to start tracking attendance</p>
                                    <a href="add.php" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i> Add New Member
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#membersTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[1, 'asc']], // Sort by name by default
            dom: '<"row"<"col-md-6"l><"col-md-6"f>>t<"row"<"col-md-6"i><"col-md-6"p>>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
                lengthMenu: "_MENU_ members per page",
                zeroRecords: "No matching members found",
                info: "Showing _START_ to _END_ of _TOTAL_ members",
                infoEmpty: "No members available",
                infoFiltered: "(filtered from _MAX_ total members)"
            }
        });
    });
</script>

<?php include_once '../../includes/footer.php'; ?>
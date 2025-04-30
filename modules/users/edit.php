// modules/users/edit.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Set page title
$pageTitle = 'Edit User';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setFlashMessage('user_message', 'User ID is required', 'danger');
    redirect(BASE_URL . '/modules/users/list.php');
}

$userId = (int) $_GET['id'];

// Get user data
$query = "
    SELECT * FROM users 
    WHERE user_id = $userId
";
$result = $conn->query($query);

// Check if user exists
if (!$result || $result->num_rows === 0) {
    setFlashMessage('user_message', 'User not found', 'danger');
    redirect(BASE_URL . '/modules/users/list.php');
}

$user = $result->fetch_assoc();

// Check permissions - only admin can edit users, and user can edit their own profile
if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_id'] !== $userId) {
    setFlashMessage('user_message', 'You do not have permission to edit this user', 'danger');
    redirect(BASE_URL . '/dashboard.php');
}

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit User</h1>
    <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <a href="list.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    <?php else: ?>
        <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    <?php endif; ?>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">User Information</h6>
    </div>
    <div class="card-body">
        <form action="process.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="username" name="username" value="<?= $user['username'] ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="change_password" name="change_password" value="1">
                        <label class="form-check-label" for="change_password">
                            Change Password
                        </label>
                    </div>
                </div>
            </div>
            
            <div id="password_fields" style="display: none;">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password">
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                </div>
            </div>
            
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="operator" <?= $user['role'] === 'operator' ? 'selected' : '' ?>>Operator</option>
                        <option value="viewer" <?= $user['role'] === 'viewer' ? 'selected' : '' ?>>Viewer</option>
                    </select>
                </div>
            </div>
            <?php else: ?>
                <input type="hidden" name="role" value="<?= $user['role'] ?>">
            <?php endif; ?>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update User
                </button>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="list.php" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('change_password').addEventListener('change', function() {
        var passwordFields = document.getElementById('password_fields');
        if (this.checked) {
            passwordFields.style.display = 'block';
            document.getElementById('password').setAttribute('required', 'required');
            document.getElementById('confirm_password').setAttribute('required', 'required');
        } else {
            passwordFields.style.display = 'none';
            document.getElementById('password').removeAttribute('required');
            document.getElementById('confirm_password').removeAttribute('required');
        }
    });
</script>
<?php include_once '../../includes/footer.php'; ?>
// modules/users/list.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Check if user has admin role
requireRole('admin');

// Set page title
$pageTitle = 'Users List';

// Get all users
$query = "
    SELECT * FROM users
    ORDER BY username ASC
";
$result = $conn->query($query);

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Users Management</h1>
    <a href="add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add New User
    </a>
</div>

<?php displayFlashMessage('user_message'); ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Users List</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered datatable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($user = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $user['user_id'] ?></td>
                                <td><?= $user['username'] ?></td>
                                <td>
                                    <?php if ($user['role'] === 'admin'): ?>
                                        <span class="badge bg-danger">Admin</span>
                                    <?php elseif ($user['role'] === 'operator'): ?>
                                        <span class="badge bg-warning">Operator</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Viewer</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    
                                    <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                        <a href="process.php?action=delete&id=<?= $user['user_id'] ?>" class="btn btn-sm btn-danger btn-delete">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled title="Cannot delete your own account">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    <?php endif; ?>
                                    
                                    <a href="process.php?action=reset_password&id=<?= $user['user_id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-key"></i> Reset Password
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No users found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>

// modules/users/add.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Check if user has admin role
requireRole('admin');

// Set page title
$pageTitle = 'Add User';

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add New User</h1>
    <a href="list.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to List
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">User Information</h6>
    </div>
    <div class="card-body">
        <form action="process.php" method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <small class="text-muted">Minimum 6 characters</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="operator">Operator</option>
                        <option value="viewer">Viewer</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save User
                </button>
                <a href="list.php" class="btn btn-danger">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>

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

// modules/users/process.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Check if action is provided
if (!isset($_POST['action']) && !isset($_GET['action'])) {
    setFlashMessage('user_message', 'Invalid request', 'danger');
    redirect(BASE_URL . '/modules/users/list.php');
}

// Determine the action
$action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];

switch ($action) {
    case 'add':
        // Only admin can add users
        requireRole('admin');
        addUser();
        break;
    case 'edit':
        // Check permissions in edit function
        editUser();
        break;
    case 'delete':
        // Only admin can delete users
        requireRole('admin');
        deleteUser();
        break;
    case 'reset_password':
        // Only admin can reset passwords
        requireRole('admin');
        resetPassword();
        break;
    default:
        setFlashMessage('user_message', 'Invalid action', 'danger');
        redirect(BASE_URL . '/modules/users/list.php');
}

/**
 * Add new user
 */
function addUser() {
    global $conn;
    
    // Check required fields
    if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['confirm_password']) || empty($_POST['role'])) {
        setFlashMessage('user_message', 'Please fill all required fields', 'danger');
        redirect(BASE_URL . '/modules/users/add.php');
    }
    
    // Sanitize and validate input
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $role = sanitize($_POST['role']);
    
    // Validate password
    if (strlen($password) < 6) {
        setFlashMessage('user_message', 'Password must be at least 6 characters long', 'danger');
        redirect(BASE_URL . '/modules/users/add.php');
    }
    
    // Check if passwords match
    if ($password !== $confirmPassword) {
        setFlashMessage('user_message', 'Passwords do not match', 'danger');
        redirect(BASE_URL . '/modules/users/add.php');
    }
    
    // Check if role is valid
    if (!in_array($role, ['admin', 'operator', 'viewer'])) {
        setFlashMessage('user_message', 'Invalid role', 'danger');
        redirect(BASE_URL . '/modules/users/add.php');
    }
    
    // Check if username already exists
    $checkQuery = "SELECT user_id FROM users WHERE username = '$username'";
    $checkResult = $conn->query($checkQuery);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        setFlashMessage('user_message', 'Username already exists. Please choose a different username.', 'danger');
        redirect(BASE_URL . '/modules/users/add.php');
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $query = "
        INSERT INTO users (username, password, role)
        VALUES ('$username', '$hashedPassword', '$role')
    ";
    
    if ($conn->query($query)) {
        setFlashMessage('user_message', 'User added successfully', 'success');
    } else {
        setFlashMessage('user_message', 'Failed to add user: ' . $conn->error, 'danger');
    }
    
    redirect(BASE_URL . '/modules/users/list.php');
}

/**
 * Edit existing user
 */
function editUser() {
    global $conn;
    
    // Check required fields
    if (empty($_POST['user_id']) || empty($_POST['username']) || empty($_POST['role'])) {
        setFlashMessage('user_message', 'Please fill all required fields', 'danger');
        redirect(BASE_URL . '/modules/users/list.php');
    }
    
    // Sanitize and validate input
    $userId = (int) $_POST['user_id'];
    $username = sanitize($_POST['username']);
    $role = sanitize($_POST['role']);
    $changePassword = isset($_POST['change_password']) && $_POST['change_password'] == 1;
    
    // Check if user exists
    $checkQuery = "SELECT user_id, role FROM users WHERE user_id = $userId";
    $checkResult = $conn->query($checkQuery);
    
    if (!$checkResult || $checkResult->num_rows === 0) {
        setFlashMessage('user_message', 'User not found', 'danger');
        redirect(BASE_URL . '/modules/users/list.php');
    }
    
    $userData = $checkResult->fetch_assoc();
    
    // Check permissions - only admin can edit users, and user can edit their own profile
    if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_id'] !== $userId) {
        setFlashMessage('user_message', 'You do not have permission to edit this user', 'danger');
        redirect(BASE_URL . '/dashboard.php');
    }
    
    // Regular users cannot change role
    if ($_SESSION['user_role'] !== 'admin' && $role !== $userData['role']) {
        setFlashMessage('user_message', 'You do not have permission to change user role', 'danger');
        redirect(BASE_URL . '/modules/users/edit.php?id=' . $userId);
    }
    
    // Check if username already exists for other users
    $checkUsernameQuery = "SELECT user_id FROM users WHERE username = '$username' AND user_id != $userId";
    $checkUsernameResult = $conn->query($checkUsernameQuery);
    
    if ($checkUsernameResult && $checkUsernameResult->num_rows > 0) {
        setFlashMessage('user_message', 'Username already exists. Please choose a different username.', 'danger');
        redirect(BASE_URL . '/modules/users/edit.php?id=' . $userId);
    }
    
    // Handle password change if requested
    $passwordSql = '';
    if ($changePassword) {
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Validate password
        if (strlen($password) < 6) {
            setFlashMessage('user_message', 'Password must be at least 6 characters long', 'danger');
            redirect(BASE_URL . '/modules/users/edit.php?id=' . $userId);
        }
        
        // Check if passwords match
        if ($password !== $confirmPassword) {
            setFlashMessage('user_message', 'Passwords do not match', 'danger');
            redirect(BASE_URL . '/modules/users/edit.php?id=' . $userId);
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $passwordSql = ", password = '$hashedPassword'";
    }
    
    // Update user
    $query = "
        UPDATE users
        SET username = '$username',
            role = '$role'
            $passwordSql
        WHERE user_id = $userId
    ";
    
    if ($conn->query($query)) {
        setFlashMessage('user_message', 'User updated successfully', 'success');
        
        // If user updated their own profile, update session data
        if ($_SESSION['user_id'] === $userId) {
            $_SESSION['username'] = $username;
            $_SESSION['user_role'] = $role;
        }
        
        // Redirect to appropriate page
        if ($_SESSION['user_role'] === 'admin' && $_SESSION['user_id'] !== $userId) {
            redirect(BASE_URL . '/modules/users/list.php');
        } else {
            redirect(BASE_URL . '/dashboard.php');
        }
    } else {
        setFlashMessage('user_message', 'Failed to update user: ' . $conn->error, 'danger');
        redirect(BASE_URL . '/modules/users/edit.php?id=' . $userId);
    }
}

/**
 * Delete user
 */
function deleteUser() {
    global $conn;
    
    // Check if ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        setFlashMessage('user_message', 'User ID is required', 'danger');
        redirect(BASE_URL . '/modules/users/list.php');
    }
    
    $userId = (int) $_GET['id'];
    
    // Check if user exists
    $checkQuery = "SELECT user_id FROM users WHERE user_id = $userId";
    $checkResult = $conn->query($checkQuery);
    
    if (!$checkResult || $checkResult->num_rows === 0) {
        setFlashMessage('user_message', 'User not found', 'danger');
        redirect(BASE_URL . '/modules/users/list.php');
    }
    
    // Prevent deleting own account
    if ($_SESSION['user_id'] === $userId) {
        setFlashMessage('user_message', 'You cannot delete your own account', 'danger');
        redirect(BASE_URL . '/modules/users/list.php');
    }
    
    // Delete user
    $query = "DELETE FROM users WHERE user_id = $userId";
    
    if ($conn->query($query)) {
        setFlashMessage('user_message', 'User deleted successfully', 'success');
    } else {
        setFlashMessage('user_message', 'Failed to delete user: ' . $conn->error, 'danger');
    }
    
    redirect(BASE_URL . '/modules/users/list.php');
}

/**
 * Reset user password
 */
function resetPassword() {
    global $conn;
    
    // Check if ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        setFlashMessage('user_message', 'User ID is required', 'danger');
        redirect(BASE_URL . '/modules/users/list.php');
    }
    // Continuing with modules/users/process.php
    $userId = (int) $_GET['id'];
    
    // Check if user exists
    $checkQuery = "SELECT user_id, username FROM users WHERE user_id = $userId";
    $checkResult = $conn->query($checkQuery);
    
    if (!$checkResult || $checkResult->num_rows === 0) {
        setFlashMessage('user_message', 'User not found', 'danger');
        redirect(BASE_URL . '/modules/users/list.php');
    }
    
    $userData = $checkResult->fetch_assoc();
    
    // Generate a random password
    $newPassword = generateRandomPassword(8);
    
    // Hash password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update user password
    $query = "
        UPDATE users
        SET password = '$hashedPassword'
        WHERE user_id = $userId
    ";
    
    if ($conn->query($query)) {
        setFlashMessage(
            'user_message', 
            'Password reset successfully for user "' . $userData['username'] . '". New password: <strong>' . $newPassword . '</strong>', 
            'success'
        );
    } else {
        setFlashMessage('user_message', 'Failed to reset password: ' . $conn->error, 'danger');
    }
    
    redirect(BASE_URL . '/modules/users/list.php');
}
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
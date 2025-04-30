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
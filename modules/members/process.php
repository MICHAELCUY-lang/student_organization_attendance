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
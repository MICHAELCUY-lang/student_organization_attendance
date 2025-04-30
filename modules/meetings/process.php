// modules/meetings/process.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Check if action is provided
if (!isset($_POST['action']) && !isset($_GET['action'])) {
    setFlashMessage('meeting_message', 'Invalid request', 'danger');
    redirect(BASE_URL . '/modules/meetings/list.php');
}

// Determine the action
$action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];

switch ($action) {
    case 'add':
        addMeeting();
        break;
    case 'edit':
        editMeeting();
        break;
    case 'delete':
        deleteMeeting();
        break;
    default:
        setFlashMessage('meeting_message', 'Invalid action', 'danger');
        redirect(BASE_URL . '/modules/meetings/list.php');
}

/**
 * Add new meeting
 */
function addMeeting() {
    global $conn;
    
    // Check required fields
    if (empty($_POST['judul_rapat']) || empty($_POST['tanggal']) || empty($_POST['waktu_mulai']) || empty($_POST['waktu_selesai'])) {
        setFlashMessage('meeting_message', 'Please fill all required fields', 'danger');
        redirect(BASE_URL . '/modules/meetings/add.php');
    }
    
    // Sanitize and validate input
    $judulRapat = sanitize($_POST['judul_rapat']);
    $tanggal = sanitize($_POST['tanggal']);
    $waktuMulai = sanitize($_POST['waktu_mulai']);
    $waktuSelesai = sanitize($_POST['waktu_selesai']);
    $catatanRapat = !empty($_POST['catatan_rapat']) ? sanitize($_POST['catatan_rapat']) : NULL;
    
    // Validate start and end times
    if (strtotime($waktuMulai) >= strtotime($waktuSelesai)) {
        setFlashMessage('meeting_message', 'End time must be after start time', 'danger');
        redirect(BASE_URL . '/modules/meetings/add.php');
    }
    
    // Insert new meeting
    $query = "
        INSERT INTO meetings (judul_rapat, tanggal, waktu_mulai, waktu_selesai, catatan_rapat)
        VALUES ('$judulRapat', '$tanggal', '$waktuMulai', '$waktuSelesai', " . ($catatanRapat ? "'$catatanRapat'" : 'NULL') . ")
    ";
    
    if ($conn->query($query)) {
        $meetingId = $conn->insert_id;
        setFlashMessage('meeting_message', 'Meeting scheduled successfully', 'success');
        
        // Redirect to add attendance if requested
        if (isset($_POST['redirect_to_attendance']) && $_POST['redirect_to_attendance'] == '1') {
            redirect(BASE_URL . '/modules/attendance/add.php?meeting_id=' . $meetingId);
        } else {
            redirect(BASE_URL . '/modules/meetings/list.php');
        }
    } else {
        setFlashMessage('meeting_message', 'Failed to schedule meeting: ' . $conn->error, 'danger');
        redirect(BASE_URL . '/modules/meetings/add.php');
    }
}

/**
 * Edit existing meeting
 */
function editMeeting() {
    global $conn;
    
    // Check required fields
    if (empty($_POST['meeting_id']) || empty($_POST['judul_rapat']) || empty($_POST['tanggal']) || empty($_POST['waktu_mulai']) || empty($_POST['waktu_selesai'])) {
        setFlashMessage('meeting_message', 'Please fill all required fields', 'danger');
        redirect(BASE_URL . '/modules/meetings/list.php');
    }
    
    // Sanitize and validate input
    $meetingId = (int) $_POST['meeting_id'];
    $judulRapat = sanitize($_POST['judul_rapat']);
    $tanggal = sanitize($_POST['tanggal']);
    $waktuMulai = sanitize($_POST['waktu_mulai']);
    $waktuSelesai = sanitize($_POST['waktu_selesai']);
    $catatanRapat = !empty($_POST['catatan_rapat']) ? sanitize($_POST['catatan_rapat']) : NULL;
    
    // Validate start and end times
    if (strtotime($waktuMulai) >= strtotime($waktuSelesai)) {
        setFlashMessage('meeting_message', 'End time must be after start time', 'danger');
        redirect(BASE_URL . '/modules/meetings/edit.php?id=' . $meetingId);
    }
    
    // Update meeting
    $query = "
        UPDATE meetings
        SET judul_rapat = '$judulRapat',
            tanggal = '$tanggal',
            waktu_mulai = '$waktuMulai',
            waktu_selesai = '$waktuSelesai',
            catatan_rapat = " . ($catatanRapat ? "'$catatanRapat'" : 'NULL') . "
        WHERE meeting_id = $meetingId
    ";
    
    if ($conn->query($query)) {
        setFlashMessage('meeting_message', 'Meeting updated successfully', 'success');
    } else {
        setFlashMessage('meeting_message', 'Failed to update meeting: ' . $conn->error, 'danger');
    }
    
    redirect(BASE_URL . '/modules/meetings/list.php');
}

/**
 * Delete meeting
 */
function deleteMeeting() {
    global $conn;
    
    // Check if ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        setFlashMessage('meeting_message', 'Meeting ID is required', 'danger');
        redirect(BASE_URL . '/modules/meetings/list.php');
    }
    
    $meetingId = (int) $_GET['id'];
    
    // Check if meeting exists
    $checkQuery = "SELECT meeting_id FROM meetings WHERE meeting_id = $meetingId";
    $checkResult = $conn->query($checkQuery);
    
    if (!$checkResult || $checkResult->num_rows === 0) {
        setFlashMessage('meeting_message', 'Meeting not found', 'danger');
        redirect(BASE_URL . '/modules/meetings/list.php');
    }
    
    // Delete meeting
    $query = "DELETE FROM meetings WHERE meeting_id = $meetingId";
    
    if ($conn->query($query)) {
        setFlashMessage('meeting_message', 'Meeting deleted successfully', 'success');
    } else {
        setFlashMessage('meeting_message', 'Failed to delete meeting: ' . $conn->error, 'danger');
    }
    
    redirect(BASE_URL . '/modules/meetings/list.php');
}
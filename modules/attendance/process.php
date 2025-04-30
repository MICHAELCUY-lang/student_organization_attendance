<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Check if action is provided
if (!isset($_POST['action']) && !isset($_GET['action'])) {
    setFlashMessage('attendance_message', 'Invalid action', 'danger');
    redirect(BASE_URL . '/modules/attendance/list.php');
}

// Determine the action
$action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];

switch ($action) {
    case 'record':
        recordAttendance();
        break;
    case 'update':
        updateAttendance();
        break;
    case 'delete':
        deleteAttendance();
        break;
    default:
        setFlashMessage('attendance_message', 'Invalid action', 'danger');
        redirect(BASE_URL . '/modules/attendance/list.php');
}

/**
 * Record attendance for multiple members
 */
function recordAttendance() {
    global $conn;
    
    // Check required fields
    if (empty($_POST['meeting_id']) || empty($_POST['members'])) {
        setFlashMessage('attendance_message', 'Please select meeting and at least one member', 'danger');
        redirect(BASE_URL . '/modules/attendance/add.php');
    }
    
    $meetingId = (int) $_POST['meeting_id'];
    $members = $_POST['members'];
    $statuses = $_POST['status'] ?? [];
    $notes = $_POST['notes'] ?? [];
    
    // Validate meeting
    $meetingQuery = "SELECT meeting_id FROM meetings WHERE meeting_id = $meetingId";
    $meetingResult = $conn->query($meetingQuery);
    
    if (!$meetingResult || $meetingResult->num_rows === 0) {
        setFlashMessage('attendance_message', 'Meeting not found', 'danger');
        redirect(BASE_URL . '/modules/attendance/add.php');
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        $success = true;
        $recordsAdded = 0;
        
        foreach ($members as $memberId) {
            $memberId = (int) $memberId;
            $status = isset($statuses[$memberId]) ? sanitize($statuses[$memberId]) : 'hadir';
            $note = isset($notes[$memberId]) ? sanitize($notes[$memberId]) : '';
            
            // Check if record already exists
            $checkQuery = "SELECT attendance_id FROM attendance WHERE meeting_id = $meetingId AND member_id = $memberId";
            $checkResult = $conn->query($checkQuery);
            
            if ($checkResult && $checkResult->num_rows > 0) {
                // Skip this record as it already exists
                continue;
            }
            
            // Insert attendance record
            $insertQuery = "
                INSERT INTO attendance (member_id, meeting_id, status_kehadiran, keterangan)
                VALUES ($memberId, $meetingId, '$status', '$note')
            ";
            
            if (!$conn->query($insertQuery)) {
                $success = false;
                break;
            }
            
            $recordsAdded++;
        }
        
        if ($success) {
            $conn->commit();
            setFlashMessage('attendance_message', $recordsAdded . ' attendance records saved successfully', 'success');
            redirect(BASE_URL . '/modules/attendance/list.php?meeting_id=' . $meetingId);
        } else {
            $conn->rollback();
            setFlashMessage('attendance_message', 'Error saving attendance records', 'danger');
            redirect(BASE_URL . '/modules/attendance/add.php?meeting_id=' . $meetingId);
        }
    } catch (Exception $e) {
        $conn->rollback();
        setFlashMessage('attendance_message', 'Error: ' . $e->getMessage(), 'danger');
        redirect(BASE_URL . '/modules/attendance/add.php?meeting_id=' . $meetingId);
    }
}

/**
 * Update attendance record
 */
function updateAttendance() {
    global $conn;
    
    // Check required fields
    if (empty($_POST['attendance_id']) || empty($_POST['status_kehadiran'])) {
        setFlashMessage('attendance_message', 'Attendance ID and status are required', 'danger');
        redirect(BASE_URL . '/modules/attendance/list.php');
    }
    
    // Sanitize and validate input
    $attendanceId = (int) $_POST['attendance_id'];
    $status = sanitize($_POST['status_kehadiran']);
    $keterangan = isset($_POST['keterangan']) ? sanitize($_POST['keterangan']) : '';
    
    // Check if attendance record exists
    $checkQuery = "
        SELECT a.*, mt.meeting_id 
        FROM attendance a
        JOIN meetings mt ON a.meeting_id = mt.meeting_id
        WHERE a.attendance_id = $attendanceId
    ";
    $checkResult = $conn->query($checkQuery);
    
    if (!$checkResult || $checkResult->num_rows === 0) {
        setFlashMessage('attendance_message', 'Attendance record not found', 'danger');
        redirect(BASE_URL . '/modules/attendance/list.php');
    }
    
    $existingRecord = $checkResult->fetch_assoc();
    $meetingId = $existingRecord['meeting_id'];
    
    // Update attendance record
    $query = "
        UPDATE attendance
        SET status_kehadiran = '$status',
            keterangan = '$keterangan'
        WHERE attendance_id = $attendanceId
    ";
    
    if ($conn->query($query)) {
        setFlashMessage('attendance_message', 'Attendance record updated successfully', 'success');
        
        // Redirect back to the referrer if available
        if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], BASE_URL) !== false) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(BASE_URL . '/modules/attendance/list.php?meeting_id=' . $meetingId);
        }
    } else {
        setFlashMessage('attendance_message', 'Failed to update attendance record: ' . $conn->error, 'danger');
        redirect(BASE_URL . '/modules/attendance/edit.php?id=' . $attendanceId);
    }
}

/**
 * Delete attendance record
 */
function deleteAttendance() {
    global $conn;
    
    // Check if ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        setFlashMessage('attendance_message', 'Attendance ID is required', 'danger');
        redirect(BASE_URL . '/modules/attendance/list.php');
    }
    
    $attendanceId = (int) $_GET['id'];
    
    // Check if attendance record exists
    $checkQuery = "
        SELECT a.*, mt.meeting_id 
        FROM attendance a
        JOIN meetings mt ON a.meeting_id = mt.meeting_id
        WHERE a.attendance_id = $attendanceId
    ";
    $checkResult = $conn->query($checkQuery);
    
    if (!$checkResult || $checkResult->num_rows === 0) {
        setFlashMessage('attendance_message', 'Attendance record not found', 'danger');
        redirect(BASE_URL . '/modules/attendance/list.php');
    }
    
    $existingRecord = $checkResult->fetch_assoc();
    $meetingId = $existingRecord['meeting_id'];
    
    // Delete attendance record
    $query = "DELETE FROM attendance WHERE attendance_id = $attendanceId";
    
    if ($conn->query($query)) {
        setFlashMessage('attendance_message', 'Attendance record deleted successfully', 'success');
    } else {
        setFlashMessage('attendance_message', 'Failed to delete attendance record: ' . $conn->error, 'danger');
    }
    
    // Redirect to the appropriate page
    $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
    if ($referrer && strpos($referrer, 'meeting_id=') !== false) {
        redirect($referrer);
    } else {
        redirect(BASE_URL . '/modules/attendance/list.php?meeting_id=' . $meetingId);
    }
}
?>
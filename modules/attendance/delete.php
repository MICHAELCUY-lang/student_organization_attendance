<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setFlashMessage('attendance_message', 'Attendance ID is required', 'danger');
    redirect(BASE_URL . '/modules/attendance/list.php');
}

$attendanceId = (int) $_GET['id'];

// Check if attendance record exists and get meeting_id for redirect
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
?>
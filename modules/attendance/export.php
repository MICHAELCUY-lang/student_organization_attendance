<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Get filter parameters (same as list.php)
$meetingId = isset($_GET['meeting_id']) ? (int)$_GET['meeting_id'] : null;
$memberId = isset($_GET['member_id']) ? (int)$_GET['member_id'] : null;
$divisonId = isset($_GET['division_id']) ? (int)$_GET['division_id'] : null;
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$dateFrom = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : '';

// Build query based on filters
$query = "
    SELECT a.*, m.nama as member_name, m.nim, mt.judul_rapat, mt.tanggal, 
           d.nama_divisi
    FROM attendance a
    JOIN members m ON a.member_id = m.member_id
    JOIN meetings mt ON a.meeting_id = mt.meeting_id
    JOIN divisions d ON m.division_id = d.division_id
";

// Add WHERE clauses if filters are set
$whereClauses = [];
if ($meetingId) {
    $whereClauses[] = "a.meeting_id = $meetingId";
}
if ($memberId) {
    $whereClauses[] = "a.member_id = $memberId";
}
if ($divisonId) {
    $whereClauses[] = "m.division_id = $divisonId";
}
if ($status) {
    $whereClauses[] = "a.status_kehadiran = '$status'";
}
if ($dateFrom) {
    $whereClauses[] = "mt.tanggal >= '$dateFrom'";
}
if ($dateTo) {
    $whereClauses[] = "mt.tanggal <= '$dateTo'";
}

if (!empty($whereClauses)) {
    $query .= " WHERE " . implode(" AND ", $whereClauses);
}

$query .= " ORDER BY mt.tanggal DESC, mt.waktu_mulai DESC, m.nama ASC";
$result = $conn->query($query);

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="attendance_export_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Create Excel file
echo '<table border="1">';
echo '<tr>';
echo '<th>No</th>';
echo '<th>Meeting</th>';
echo '<th>Date</th>';
echo '<th>Member</th>';
echo '<th>NIM</th>';
echo '<th>Division</th>';
echo '<th>Status</th>';
echo '<th>Notes</th>';
echo '</tr>';

if ($result && $result->num_rows > 0) {
    $no = 1;
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $no++ . '</td>';
        echo '<td>' . $row['judul_rapat'] . '</td>';
        echo '<td>' . formatDate($row['tanggal']) . '</td>';
        echo '<td>' . $row['member_name'] . '</td>';
        echo '<td>' . $row['nim'] . '</td>';
        echo '<td>' . $row['nama_divisi'] . '</td>';
        
        // Format status
        $status = '';
        if ($row['status_kehadiran'] == 'hadir') {
            $status = 'Present';
        } elseif ($row['status_kehadiran'] == 'telat') {
            $status = 'Late';
        } elseif ($row['status_kehadiran'] == 'izin') {
            $status = 'Excused';
        } else {
            $status = 'Absent';
        }
        
        echo '<td>' . $status . '</td>';
        echo '<td>' . ($row['keterangan'] ?: '-') . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="8">No records found</td></tr>';
}

echo '</table>';
exit;
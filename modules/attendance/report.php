// modules/attendance/report.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Set page title
$pageTitle = 'Attendance Reports';

// Get all divisions for filtering
$divisionsQuery = "SELECT * FROM divisions ORDER BY nama_divisi ASC";
$divisionsResult = $conn->query($divisionsQuery);

// Initialize filters
$divisionId = isset($_GET['division_id']) ? (int) $_GET['division_id'] : null;
$startDate = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : '';

// Build query based on filters
$query = "
    SELECT 
        m.member_id, m.nama, m.nim, d.nama_divisi,
        COUNT(DISTINCT mt.meeting_id) as total_meetings,
        SUM(CASE WHEN a.status_kehadiran = 'hadir' OR a.status_kehadiran = 'telat' THEN 1 ELSE 0 END) as total_present,
        SUM(CASE WHEN a.status_kehadiran = 'izin' THEN 1 ELSE 0 END) as total_excused,
        SUM(CASE WHEN a.status_kehadiran = 'alpa' THEN 1 ELSE 0 END) as total_absent,
        COALESCE(SUM(CASE WHEN a.status_kehadiran = 'hadir' OR a.status_kehadiran = 'telat' THEN 1 ELSE 0 END) / COUNT(DISTINCT mt.meeting_id) * 100, 0) as attendance_rate
    FROM 
        members m
    JOIN 
        divisions d ON m.division_id = d.division_id
    LEFT JOIN 
        attendance a ON m.member_id = a.member_id
    LEFT JOIN 
        meetings mt ON a.meeting_id = mt.meeting_id
";

// Add WHERE conditions based on filters
$whereConditions = [];

if ($divisionId) {
    $whereConditions[] = "m.division_id = $divisionId";
}

if ($startDate && $endDate) {
    $whereConditions[] = "mt.tanggal BETWEEN '$startDate' AND '$endDate'";
} elseif ($startDate) {
    $whereConditions[] = "mt.tanggal >= '$startDate'";
} elseif ($endDate) {
    $whereConditions[] = "mt.tanggal <= '$endDate'";
}

if (!empty($whereConditions)) {
    $query .= " WHERE " . implode(" AND ", $whereConditions);
}

$query .= " GROUP BY m.member_id ORDER BY d.nama_divisi ASC, m.nama ASC";

$result = $conn->query($query);

// Get total meetings count for percentage calculation
$totalMeetingsQuery = "
    SELECT COUNT(*) as total FROM meetings
";

if ($startDate && $endDate) {
    $totalMeetingsQuery .= " WHERE tanggal BETWEEN '$startDate' AND '$endDate'";
} elseif ($startDate) {
    $totalMeetingsQuery .= " WHERE tanggal >= '$startDate'";
} elseif ($endDate) {
    $totalMeetingsQuery .= " WHERE tanggal <= '$endDate'";
}

$totalMeetingsResult = $conn->query($totalMeetingsQuery);
$totalMeetings = $totalMeetingsResult->fetch_assoc()['total'];

// Include header
include_once '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Attendance Reports</h1>
    <div>
        <button type="button" class="btn btn-outline-primary btn-print" onclick="window.print()">
            <i class="bi bi-printer"></i> Print Report
        </button>
        <button type="button" class="btn btn-outline-success" onclick="exportTableToExcel('attendanceTable', 'attendance_report')">
            <i class="bi bi-file-earmark-excel"></i> Export to Excel
        </button>
    </div>
</div>

<!-- Filter Card -->
<div class="card shadow mb-4 no-print">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Filter Options</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= $_SERVER['PHP_SELF'] ?>" class="row">
            <div class="col-md-3 mb-3">
                <label for="division_id" class="form-label">Division</label>
                <select class="form-select" id="division_id" name="division_id">
                    <option value="">All Divisions</option>
                    <?php if ($divisionsResult && $divisionsResult->num_rows > 0): ?>
                        <?php while ($division = $divisionsResult->fetch_assoc()): ?>
                            <option value="<?= $division['division_id'] ?>" <?= $divisionId == $division['division_id'] ? 'selected' : '' ?>>
                                <?= $division['nama_divisi'] ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $startDate ?>">
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $endDate ?>">
            </div>
            
            <div class="col-md-3 d-flex align-items-end mb-3">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-filter"></i> Apply Filters
                </button>
                <a href="report.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Report Summary -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Report Summary</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-3">
                        <h5>Total Meetings</h5>
                        <h2 class="text-primary"><?= $totalMeetings ?></h2>
                    </div>
                    
                    <div class="col-md-4 text-center mb-3">
                        <h5>Date Range</h5>
                        <h6 class="text-muted">
                            <?php if ($startDate && $endDate): ?>
                                <?= formatDate($startDate) ?> to <?= formatDate($endDate) ?>
                            <?php elseif ($startDate): ?>
                                From <?= formatDate($startDate) ?>
                            <?php elseif ($endDate): ?>
                                Until <?= formatDate($endDate) ?>
                            <?php else: ?>
                                All Time
                            <?php endif; ?>
                        </h6>
                    </div>
                    
                    <div class="col-md-4 text-center mb-3">
                        <h5>Division</h5>
                        <h6 class="text-muted">
                            <?php 
                            if ($divisionId) {
                                $divName = $conn->query("SELECT nama_divisi FROM divisions WHERE division_id = $divisionId")->fetch_assoc()['nama_divisi'];
                                echo $divName;
                            } else {
                                echo 'All Divisions';
                            }
                            ?>
                        </h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Report Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold">Attendance Report</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="attendanceTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>NIM</th>
                        <th>Division</th>
                        <th>Present</th>
                        <th>Excused</th>
                        <th>Absent</th>
                        <th>Total Meetings</th>
                        <th>Attendance Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php $no = 1; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $row['nama'] ?></td>
                                <td><?= $row['nim'] ?></td>
                                <td><?= $row['nama_divisi'] ?></td>
                                <td><?= $row['total_present'] ?></td>
                                <td><?= $row['total_excused'] ?></td>
                                <td><?= $row['total_absent'] ?></td>
                                <td><?= $row['total_meetings'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                style="width: <?= round($row['attendance_rate']) ?>%" 
                                                aria-valuenow="<?= round($row['attendance_rate']) ?>" 
                                                aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <span><?= round($row['attendance_rate']) ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No data found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Function to export table to Excel
    function exportTableToExcel(tableID, filename = '') {
        var downloadLink;
        var dataType = 'application/vnd.ms-excel';
        var tableSelect = document.getElementById(tableID);
        var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
        
        // Specify file name
        filename = filename ? filename + '.xls' : 'attendance_report.xls';
        
        // Create download link element
        downloadLink = document.createElement("a");
        
        document.body.appendChild(downloadLink);
        
        if (navigator.msSaveOrOpenBlob) {
            var blob = new Blob(['\ufeff', tableHTML], {
                type: dataType
            });
            navigator.msSaveOrOpenBlob(blob, filename);
        } else {
            // Create a link to the file
            downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
        
            // Setting the file name
            downloadLink.download = filename;
            
            // Triggering the function
            downloadLink.click();
        }
    }
</script>

<?php include_once '../../includes/footer.php'; ?>
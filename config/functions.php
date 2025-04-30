<?php
/**
 * Display flash message
 * 
 * @param string $name
 * @param string $message
 * @param string $class
 * @return void
 */
function setFlashMessage($name, $message, $class = 'success') {
    if (!empty($name)) {
        $_SESSION[$name] = $message;
        $_SESSION[$name . '_class'] = $class;
    }
}

/**
 * Display flash message
 * 
 * @param string $name
 * @return void
 */
function displayFlashMessage($name) {
    if (isset($_SESSION[$name])) {
        $class = isset($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : 'success';
        echo '<div class="alert alert-' . $class . ' alert-dismissible fade show" role="alert">';
        echo $_SESSION[$name];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION[$name]);
        unset($_SESSION[$name . '_class']);
    }
}

/**
 * Redirect to specific page
 * 
 * @param string $location
 * @return void
 */
function redirect($location) {
    header("Location: " . $location);
    exit();
}

/**
 * Check if user is logged in
 * 
 * @return boolean
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check user role
 * 
 * @param string $role
 * @return boolean
 */
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == $role;
}

/**
 * Sanitize input
 * 
 * @param string $input
 * @return string
 */
function sanitize($input) {
    global $conn;
    return $conn->real_escape_string(htmlspecialchars(trim($input)));
}

/**
 * Format date
 * 
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

/**
 * Format time
 * 
 * @param string $time
 * @param string $format
 * @return string
 */
function formatTime($time, $format = 'H:i') {
    return date($format, strtotime($time));
}

/**
 * Get attendance status badge
 * 
 * @param string $status
 * @return string
 */
function getStatusBadge($status) {
    switch ($status) {
        case 'hadir':
            return '<span class="badge bg-success">Hadir</span>';
        case 'izin':
            return '<span class="badge bg-warning">Izin</span>';
        case 'alpa':
            return '<span class="badge bg-danger">Alpa</span>';
        case 'telat':
            return '<span class="badge bg-info">Telat</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}

/**
 * Get division name by ID
 * 
 * @param int $divisionId
 * @return string
 */
function getDivisionName($divisionId) {
    global $conn;
    $divisionId = (int) $divisionId;
    $result = $conn->query("SELECT nama_divisi FROM divisions WHERE division_id = $divisionId");
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc()['nama_divisi'];
    }
    return 'Unknown';
}

/**
 * Get member name by ID
 * 
 * @param int $memberId
 * @return string
 */
function getMemberName($memberId) {
    global $conn;
    $memberId = (int) $memberId;
    $result = $conn->query("SELECT nama FROM members WHERE member_id = $memberId");
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc()['nama'];
    }
    return 'Unknown';
}

/**
 * Get meeting title by ID
 * 
 * @param int $meetingId
 * @return string
 */
function getMeetingTitle($meetingId) {
    global $conn;
    $meetingId = (int) $meetingId;
    $result = $conn->query("SELECT judul_rapat FROM meetings WHERE meeting_id = $meetingId");
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc()['judul_rapat'];
    }
    return 'Unknown';
}

/**
 * Calculate attendance percentage
 * 
 * @param int $present
 * @param int $total
 * @return float
 */
function calculateAttendancePercentage($present, $total) {
    if ($total > 0) {
        return round(($present / $total) * 100, 2);
    }
    return 0;
}

/**
 * Generate random password
 * 
 * @param int $length
 * @return string
 */
function generateRandomPassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}
?>
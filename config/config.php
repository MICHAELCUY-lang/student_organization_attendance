<?php
// Application configuration
define('APP_NAME', 'Student Organization Attendance System');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/student_organization_attendance');
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/student_organization_attendance/uploads/documents/');
define('TIMEZONE', 'Asia/Jakarta');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Session
session_start();

// Include database connection
require_once __DIR__ . '/database.php';

// Include functions
require_once __DIR__ . '/functions.php';
?>
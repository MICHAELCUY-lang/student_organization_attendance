<?php
require_once 'config/config.php';

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
setFlashMessage('logout_success', 'You have been successfully logged out', 'success');
redirect(BASE_URL . '/login.php');
?>
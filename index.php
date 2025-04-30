<?php
require_once 'config/config.php';

// Redirect to dashboard if logged in, otherwise to login page
if (isLoggedIn()) {
    redirect(BASE_URL . '/dashboard.php');
} else {
    redirect(BASE_URL . '/login.php');
}
?>
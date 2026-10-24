<?php
session_start();
require_once '../config.php';

// Destroy session
session_destroy();

// Redirect to login
redirect(ADMIN_URL . '/login.php');
?>

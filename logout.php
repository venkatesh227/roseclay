<?php
session_start();
require_once 'config.php';

// Destroy session
session_destroy();

// Redirect to homepage
redirect('index.php');
?>

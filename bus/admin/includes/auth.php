<?php
session_start();

// Consider both legacy flag and new session keys
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
?> 
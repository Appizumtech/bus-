<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'customer') {
	header('Location: /bus/src/login.php');
	exit;
}
?>
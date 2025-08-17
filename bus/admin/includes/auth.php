<?php
session_start();

// Admin area guard: allow super_admin, owner, agent via either legacy or role session
$role = $_SESSION['role'] ?? '';
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_logged_in'])) {
	header('Location: login.php');
	exit;
}
if (!in_array($role, ['super_admin','owner','agent'])) {
	header('Location: login.php');
	exit;
}
?> 
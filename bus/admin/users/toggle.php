<?php
require '../includes/header.php';
require '../includes/db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['super_admin','owner'])) {
	echo '<div class="alert alert-danger">Not authorized.</div>';
	require '../includes/footer.php';
	exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: index.php'); exit; }

$target = $pdo->prepare('SELECT id, role, assigned_owner_id, active FROM users WHERE id = ?');
$target->execute([$id]);
$user = $target->fetch();
if (!$user) { header('Location: index.php'); exit; }

// owner can manage only their agents/customers
if (($_SESSION['role'] ?? '') === 'owner') {
	$ownerId = (int)($_SESSION['user_id'] ?? 0);
	if (!in_array($user['role'], ['agent','customer']) || (int)($user['assigned_owner_id'] ?? 0) !== $ownerId) {
		echo '<div class="alert alert-danger">Not authorized to update this user.</div>';
		require '../includes/footer.php';
		exit;
	}
}

$newActive = (int)(!((int)$user['active']));
$upd = $pdo->prepare('UPDATE users SET active = ? WHERE id = ?');
$upd->execute([$newActive, $id]);

header('Location: index.php');
exit;
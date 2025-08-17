<?php
require '../includes/header.php';
require '../includes/db.php';

$type = $_GET['type'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$busId = isset($_GET['bus_id']) ? (int)$_GET['bus_id'] : 0;

if ($id > 0 && $busId > 0 && in_array($type, ['boarding','dropping'])) {
	$table = $type === 'boarding' ? 'boarding_points' : 'dropping_points';
	$stmt = $pdo->prepare("DELETE FROM $table WHERE id = ? AND bus_id = ?");
	$stmt->execute([$id, $busId]);
}

header('Location: points.php?bus_id=' . $busId);
exit;
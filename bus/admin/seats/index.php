<?php
require '../includes/header.php';
require '../includes/db.php';

$role = $_SESSION['role'] ?? '';
$userId = (int)($_SESSION['user_id'] ?? 0);

// Fetch buses for filter
if ($role === 'owner') {
	$busStmt = $pdo->prepare('SELECT id, name FROM buses WHERE owner_id = ? ORDER BY travel_date DESC');
	$busStmt->execute([$userId]);
	$buses = $busStmt->fetchAll();
} else {
	$buses = $pdo->query('SELECT id, name FROM buses ORDER BY travel_date DESC')->fetchAll();
}

$busId = isset($_GET['bus_id']) ? (int)$_GET['bus_id'] : 0;
$busRow = null;
if ($busId > 0) {
	$busCheck = $pdo->prepare('SELECT * FROM buses WHERE id = ?');
	$busCheck->execute([$busId]);
	$busRow = $busCheck->fetch();
	if (!$busRow) { $busId = 0; }
	// Owner authorization
	if ($busRow && $role === 'owner' && (int)$busRow['owner_id'] !== $userId) {
		echo '<div class="alert alert-danger">Not authorized for this bus.</div>';
		require '../includes/footer.php';
		exit;
	}
}

if ($busId > 0 && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status']) && in_array($role, ['super_admin','owner'])) {
	$pdo->beginTransaction();
	try {
		$upd = $pdo->prepare('UPDATE seats SET status = ? WHERE id = ? AND bus_id = ?');
		foreach ((array)$_POST['status'] as $seatId => $status) {
			$seatId = (int)$seatId;
			if ($seatId > 0 && in_array($status, ['available','booked','reserved'])) {
				$upd->execute([$status, $seatId, $busId]);
			}
		}
		$pdo->commit();
		header('Location: index.php?bus_id=' . $busId . '&saved=1');
		exit;
	} catch (Throwable $t) {
		$pdo->rollBack();
		echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($t->getMessage()) . '</div>';
	}
}

$seats = [];
if ($busId > 0) {
	$seatStmt = $pdo->prepare('SELECT * FROM seats WHERE bus_id = ? ORDER BY deck, seat_number');
	$seatStmt->execute([$busId]);
	$seats = $seatStmt->fetchAll();
}
?>
<div class="container">
	<h1>Seat Management</h1>
	<form class="row g-2 mb-3" method="get">
		<div class="col-md-6">
			<label class="form-label">Bus</label>
			<select name="bus_id" class="form-select" onchange="this.form.submit()">
				<option value="0">Select Bus</option>
				<?php foreach ($buses as $b): ?>
				<option value="<?= $b['id'] ?>"<?= $busId===(int)$b['id']?' selected':''; ?>><?= htmlspecialchars($b['name']) ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</form>

	<?php if (isset($_GET['saved'])): ?>
	<div class="alert alert-success">Seat statuses updated.</div>
	<?php endif; ?>

	<?php if ($busId > 0 && $busRow): ?>
		<div class="card mb-3">
			<div class="card-body">
				<p class="mb-0"><strong>Bus:</strong> <?= htmlspecialchars($busRow['name']) ?> | <strong>Route:</strong> <?= htmlspecialchars($busRow['source']) ?> → <?= htmlspecialchars($busRow['destination']) ?> | <strong>Date:</strong> <?= htmlspecialchars($busRow['travel_date']) ?></p>
			</div>
		</div>

		<form method="post">
			<table class="table table-striped">
				<thead>
					<tr>
						<th>#</th>
						<th>Seat</th>
						<th>Type</th>
						<th>Deck</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($seats as $s): ?>
					<tr>
						<td><?= $s['id'] ?></td>
						<td><?= htmlspecialchars($s['seat_number']) ?></td>
						<td><?= htmlspecialchars($s['seat_type']) ?></td>
						<td><?= htmlspecialchars($s['deck']) ?></td>
						<td>
							<?php if (in_array($role, ['super_admin','owner'])): ?>
							<select name="status[<?= $s['id'] ?>]" class="form-select form-select-sm" style="max-width: 160px;">
								<?php foreach (['available','reserved','booked'] as $st): ?>
								<option value="<?= $st ?>"<?= $s['status']===$st?' selected':''; ?>><?= $st ?></option>
								<?php endforeach; ?>
							</select>
							<?php else: ?>
							<span class="badge bg-secondary"><?= htmlspecialchars($s['status']) ?></span>
							<?php endif; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php if (in_array($role, ['super_admin','owner'])): ?>
			<button class="btn btn-primary">Save Changes</button>
			<?php endif; ?>
		</form>
	<?php elseif ($busId === 0): ?>
		<div class="alert alert-info">Select a bus to manage its seats.</div>
	<?php else: ?>
		<div class="alert alert-warning">No seats found for this bus.</div>
	<?php endif; ?>
</div>
<?php require '../includes/footer.php'; ?>
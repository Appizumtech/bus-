<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

$userId = (int)($_SESSION['user_id'] ?? 0);
$bookings = $pdo->prepare('SELECT b.*, bs.name AS bus_name, bs.source, bs.destination FROM bookings b JOIN buses bs ON b.bus_id = bs.id WHERE b.user_id = ? ORDER BY b.created_at DESC');
$bookings->execute([$userId]);
$rows = $bookings->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>User Dashboard</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
	<nav class="navbar navbar-dark bg-dark mb-4">
		<div class="container">
			<span class="navbar-brand">User Panel</span>
			<a class="btn btn-outline-light" href="logout.php">Logout</a>
		</div>
	</nav>
	<div class="container">
		<h1 class="mb-3">My Bookings</h1>
		<?php if (empty($rows)): ?>
			<div class="alert alert-info">No bookings found.</div>
		<?php else: ?>
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Ref</th>
						<th>Bus</th>
						<th>Route</th>
						<th>Date</th>
						<th>Status</th>
						<th>Amount</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($rows as $r): ?>
					<tr>
						<td><?= htmlspecialchars($r['booking_ref']) ?></td>
						<td><?= htmlspecialchars($r['bus_name']) ?></td>
						<td><?= htmlspecialchars($r['source']) ?> → <?= htmlspecialchars($r['destination']) ?></td>
						<td><?= htmlspecialchars($r['travel_date']) ?></td>
						<td><?= htmlspecialchars($r['status']) ?></td>
						<td>₹<?= number_format((float)$r['amount'], 2) ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</body>
</html>
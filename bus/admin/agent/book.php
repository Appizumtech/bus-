<?php
require '../includes/header.php';
require '../includes/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent') {
	echo '<div class="alert alert-danger">Agents only.</div>';
	require '../includes/footer.php';
	exit;
}

$settings = $pdo->query('SELECT * FROM admin_settings WHERE id = 1')->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$busId = (int)($_POST['bus_id'] ?? 0);
	$seatIds = array_filter(array_map('intval', explode(',', $_POST['seat_ids'] ?? '')));
	$travelDate = $_POST['travel_date'] ?? '';
	if ($busId > 0 && $travelDate && !empty($seatIds)) {
		$pdo->beginTransaction();
		try {
			$ref = 'BK' . date('YmdHis') . rand(100,999);
			// compute fare per seat from bus
			$busStmt = $pdo->prepare('SELECT fare FROM buses WHERE id = ?');
			$busStmt->execute([$busId]);
			$bus = $busStmt->fetch();
			$fare = (float)($bus['fare'] ?? 0);
			$amount = $fare * count($seatIds) + (float)($settings['per_ticket_charge'] ?? 0) * count($seatIds);
			$ins = $pdo->prepare('INSERT INTO bookings (booking_ref, bus_id, user_id, status, amount, travel_date) VALUES (?,?,?,?,?,?)');
			$ins->execute([$ref, $busId, $_SESSION['user_id'], 'confirmed', $amount, $travelDate]);
			$bookingId = (int)$pdo->lastInsertId();
			$bs = $pdo->prepare('INSERT INTO booking_seats (booking_id, seat_id, fare) VALUES (?,?,?)');
			foreach ($seatIds as $sid) { $bs->execute([$bookingId, $sid, $fare]); }
			$pdo->commit();
			header('Location: /bus/admin/tickets/print.php?booking_id=' . $bookingId);
			exit;
		} catch (Throwable $t) {
			$pdo->rollBack();
			echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($t->getMessage()) . '</div>';
		}
	}
}

$buses = $pdo->query('SELECT id, name, travel_date FROM buses ORDER BY travel_date DESC')->fetchAll();
?>
<div class="container">
    <h1>Agent Booking</h1>
    <form method="post" class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Bus</label>
            <select name="bus_id" class="form-select" required>
                <option value="">Select</option>
                <?php foreach ($buses as $b): ?>
                <option value="<?= $b['id'] ?>"><?php echo htmlspecialchars($b['name'] . ' (' . $b['travel_date'] . ')'); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Travel Date</label>
            <input type="date" class="form-control" name="travel_date" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Seat IDs (comma separated)</label>
            <input type="text" class="form-control" name="seat_ids" placeholder="e.g. 12,13,14" required>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Create Booking</button>
        </div>
    </form>
    <p class="text-muted mt-3">Per ticket charge applied: ₹<?= number_format((float)($settings['per_ticket_charge'] ?? 0), 2) ?></p>
</div>
<?php require '../includes/footer.php'; ?>
<?php
require '../includes/db.php';
session_start();

$bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
if ($bookingId <= 0) {
	echo 'Invalid booking';
	exit;
}

// Authorization check
$role = $_SESSION['role'] ?? '';
$userId = (int)($_SESSION['user_id'] ?? 0);

$auth = $pdo->prepare('SELECT b.user_id, bs.owner_id FROM bookings b JOIN buses bs ON b.bus_id = bs.id WHERE b.id = ?');
$auth->execute([$bookingId]);
$authRow = $auth->fetch();
if (!$authRow) { echo 'Booking not found'; exit; }
if ($role === 'owner' && (int)$authRow['owner_id'] !== $userId) { echo 'Not authorized'; exit; }
if ($role === 'agent' && (int)$authRow['user_id'] !== $userId) { echo 'Not authorized'; exit; }

// Ensure a ticket exists
$ticketStmt = $pdo->prepare('SELECT * FROM tickets WHERE booking_id = ? LIMIT 1');
$ticketStmt->execute([$bookingId]);
$ticket = $ticketStmt->fetch();
if (!$ticket) {
	$ticketNo = 'T' . date('YmdHis') . rand(100,999);
	$issuedBy = $_SESSION['user_id'] ?? null;
	$ins = $pdo->prepare('INSERT INTO tickets (ticket_no, booking_id, issued_by_user_id) VALUES (?,?,?)');
	$ins->execute([$ticketNo, $bookingId, $issuedBy]);
	$ticket = [
		'ticket_no' => $ticketNo,
		'booking_id' => $bookingId,
	];
}

// Fetch booking with bus and points
$booking = $pdo->prepare('SELECT b.*, bs.name AS bus_name, bs.source, bs.destination, bs.departure_time, bs.arrival_time FROM bookings b JOIN buses bs ON b.bus_id = bs.id WHERE b.id = ?');
$booking->execute([$bookingId]);
$b = $booking->fetch();
if (!$b) {
	echo 'Booking not found';
	exit;
}

$seats = $pdo->prepare('SELECT s.seat_number FROM booking_seats bs JOIN seats s ON bs.seat_id = s.id WHERE bs.booking_id = ?');
$seats->execute([$bookingId]);
$seatNumbers = array_map(function($r){ return $r['seat_number']; }, $seats->fetchAll());
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Ticket <?= htmlspecialchars($ticket['ticket_no']) ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
	@media print { .no-print { display:none; } }
	</style>
</head>
<body class="p-4">
	<div class="container">
		<div class="d-flex justify-content-between align-items-center mb-3">
			<h3>Bus Ticket</h3>
			<button class="btn btn-sm btn-secondary no-print" onclick="window.print()">Print</button>
		</div>
		<div class="card">
			<div class="card-body">
				<div class="row">
					<div class="col-md-6">
						<p><strong>Ticket No:</strong> <?= htmlspecialchars($ticket['ticket_no']) ?></p>
						<p><strong>Booking Ref:</strong> <?= htmlspecialchars($b['booking_ref']) ?></p>
						<p><strong>Bus:</strong> <?= htmlspecialchars($b['bus_name']) ?></p>
						<p><strong>Route:</strong> <?= htmlspecialchars($b['source']) ?> → <?= htmlspecialchars($b['destination']) ?></p>
					</div>
					<div class="col-md-6">
						<p><strong>Travel Date:</strong> <?= htmlspecialchars($b['travel_date']) ?></p>
						<p><strong>Departure:</strong> <?= htmlspecialchars($b['departure_time']) ?></p>
						<p><strong>Arrival:</strong> <?= htmlspecialchars($b['arrival_time']) ?></p>
						<p><strong>Seats:</strong> <?= htmlspecialchars(implode(', ', $seatNumbers)) ?></p>
					</div>
				</div>
				<hr>
				<p><strong>Total Amount:</strong> ₹<?= number_format((float)$b['amount'], 2) ?></p>
			</div>
		</div>
	</div>
</body>
</html>
<?php
require '../includes/header.php';
require '../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$bookingStmt = $pdo->prepare('SELECT b.*, bs.name AS bus_name, bs.source, bs.destination, bs.departure_time, bs.arrival_time, u.name AS booked_by FROM bookings b JOIN buses bs ON b.bus_id = bs.id LEFT JOIN users u ON b.user_id = u.id WHERE b.id = ?');
$bookingStmt->execute([$id]);
$booking = $bookingStmt->fetch();
if (!$booking) { echo '<div class="alert alert-danger">Booking not found</div>'; require '../includes/footer.php'; exit; }

$seats = $pdo->prepare('SELECT s.seat_number FROM booking_seats bs JOIN seats s ON bs.seat_id = s.id WHERE bs.booking_id = ? ORDER BY s.seat_number');
$seats->execute([$id]);
$seatNumbers = array_map(function($r){ return $r['seat_number']; }, $seats->fetchAll());
?>
<div class="container">
    <h1>Booking #<?= $booking['id'] ?></h1>
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Booking Ref:</strong> <?= htmlspecialchars($booking['booking_ref']) ?></p>
                    <p><strong>Bus:</strong> <?= htmlspecialchars($booking['bus_name']) ?></p>
                    <p><strong>Route:</strong> <?= htmlspecialchars($booking['source']) ?> → <?= htmlspecialchars($booking['destination']) ?></p>
                    <p><strong>Seats:</strong> <?= htmlspecialchars(implode(', ', $seatNumbers)) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Travel Date:</strong> <?= htmlspecialchars($booking['travel_date']) ?></p>
                    <p><strong>Departure:</strong> <?= htmlspecialchars($booking['departure_time']) ?></p>
                    <p><strong>Arrival:</strong> <?= htmlspecialchars($booking['arrival_time']) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($booking['status']) ?></p>
                </div>
            </div>
            <hr>
            <p><strong>Amount:</strong> ₹<?= number_format((float)$booking['amount'], 2) ?></p>
            <p><strong>Booked By:</strong> <?= htmlspecialchars($booking['booked_by'] ?? '-') ?></p>
            <a href="/bus/admin/tickets/print.php?booking_id=<?= $booking['id'] ?>" class="btn btn-secondary">Print Ticket</a>
        </div>
    </div>
</div>
<?php require '../includes/footer.php'; ?>
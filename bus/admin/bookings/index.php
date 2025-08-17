<?php
require '../includes/header.php';
require '../includes/db.php';

$role = $_SESSION['role'] ?? '';
$userId = $_SESSION['user_id'] ?? 0;

$where = [];
$params = [];
if ($role === 'owner') {
	$where[] = 'bs.owner_id = ?';
	$params[] = $userId;
} elseif ($role === 'agent') {
	$where[] = 'b.user_id = ?';
	$params[] = $userId;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$bookings = $pdo->prepare("
    SELECT b.*, bs.name AS bus_name, bs.source, bs.destination, u.name AS booked_by
    FROM bookings b
    JOIN buses bs ON b.bus_id = bs.id
    LEFT JOIN users u ON b.user_id = u.id
    $whereSql
    ORDER BY b.created_at DESC
");
$bookings->execute($params);
$bookings = $bookings->fetchAll();
?>

<div class="container">
    <h1>Bookings</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Booking Ref</th>
                <th>Bus</th>
                <th>Route</th>
                <th>Travel Date</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Booked By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $booking): ?>
            <tr>
                <td><?= $booking['id'] ?></td>
                <td><?= htmlspecialchars($booking['booking_ref']) ?></td>
                <td><?= htmlspecialchars($booking['bus_name']) ?></td>
                <td><?= htmlspecialchars($booking['source']) ?> → <?= htmlspecialchars($booking['destination']) ?></td>
                <td><?= htmlspecialchars($booking['travel_date']) ?></td>
                <td><?= htmlspecialchars($booking['status']) ?></td>
                <td>₹<?= number_format($booking['amount'], 2) ?></td>
                <td><?= htmlspecialchars($booking['booked_by'] ?? '-') ?></td>
                <td>
                    <a href="view.php?id=<?= $booking['id'] ?>" class="btn btn-sm btn-info">View</a>
                    <a href="/bus/admin/tickets/print.php?booking_id=<?= $booking['id'] ?>" class="btn btn-sm btn-secondary">Print</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require '../includes/footer.php'; ?> 
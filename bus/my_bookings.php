<?php
require __DIR__ . '/admin/includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT b.id, b.booking_ref, b.status, b.amount, b.travel_date, bs.name AS bus_name
                        FROM bookings b
                        JOIN buses bs ON bs.id = b.bus_id
                        WHERE b.user_id = ?
                        ORDER BY b.id DESC');
$stmt->execute([ $userId ]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
    <?php include 'includes/header-links.php'; ?>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container py-4">
    <h3>My Bookings</h3>
    <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Ref</th>
                    <th>Bus</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($bookings as $bk): ?>
                <tr>
                    <td><?php echo htmlspecialchars($bk['booking_ref']); ?></td>
                    <td><?php echo htmlspecialchars($bk['bus_name']); ?></td>
                    <td><?php echo htmlspecialchars($bk['travel_date']); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($bk['status'])); ?></td>
                    <td><?php echo '₹' . number_format((float)$bk['amount'], 2); ?></td>
                    <td>
                        <?php if ($bk['status'] === 'confirmed'): ?>
                            <form method="post" action="/api/cancel_booking.php" onsubmit="return confirm('Cancel this booking?');">
                                <input type="hidden" name="booking_id" value="<?php echo (int)$bk['id']; ?>">
                                <button class="btn btn-sm btn-danger" type="submit">Cancel</button>
                            </form>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/footer-links.php'; ?>
</body>
</html>


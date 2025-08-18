<?php
session_start();
header('Content-Type: text/html');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /my_bookings.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$bookingId = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
if ($bookingId <= 0) {
    header('Location: /my_bookings.php');
    exit;
}

require_once __DIR__ . '/../admin/includes/db.php';

try {
    $userId = (int)$_SESSION['user_id'];
    $pdo->beginTransaction();

    // Verify ownership and status
    $sel = $pdo->prepare('SELECT id, bus_id, status FROM bookings WHERE id = ? AND user_id = ? FOR UPDATE');
    $sel->execute([ $bookingId, $userId ]);
    $booking = $sel->fetch();
    if (!$booking || $booking['status'] !== 'confirmed') {
        $pdo->rollBack();
        header('Location: /my_bookings.php');
        exit;
    }

    // Fetch seats for this booking
    $seatStmt = $pdo->prepare('SELECT seat_id FROM booking_seats WHERE booking_id = ?');
    $seatStmt->execute([ $bookingId ]);
    $seatIds = $seatStmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($seatIds)) {
        $inClause = implode(',', array_fill(0, count($seatIds), '?'));
        // Free seats: set to available
        $upd = $pdo->prepare("UPDATE seats SET status = 'available' WHERE id IN ($inClause)");
        $upd->execute($seatIds);
        // Increase available seats on bus
        $inc = $pdo->prepare('UPDATE buses SET available_seats = available_seats + ? WHERE id = ?');
        $inc->execute([ count($seatIds), (int)$booking['bus_id'] ]);
    }

    // Mark booking cancelled
    $updBk = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
    $updBk->execute([ $bookingId ]);

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
}

header('Location: /my_bookings.php');
exit;
?>


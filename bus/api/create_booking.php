<?php
session_start();
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([ 'success' => false, 'message' => 'Method Not Allowed' ]);
        exit;
    }

    $busId = isset($_POST['bus_id']) ? (int)$_POST['bus_id'] : 0;
    $seatIdsCsv = $_POST['seat_ids'] ?? '';
    $passengerName = trim($_POST['passenger_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0.0;

    if ($busId <= 0 || empty($seatIdsCsv) || $amount <= 0 || $passengerName === '') {
        echo json_encode([ 'success' => false, 'message' => 'Missing required fields' ]);
        exit;
    }

    $seatIds = array_values(array_filter(array_map('intval', explode(',', $seatIdsCsv))));
    if (empty($seatIds)) {
        echo json_encode([ 'success' => false, 'message' => 'No seats selected' ]);
        exit;
    }

    require_once __DIR__ . '/../admin/includes/db.php';
    $sessionId = session_id();

    $pdo->beginTransaction();

    // Validate bus and fare
    $busStmt = $pdo->prepare('SELECT id, travel_date, fare FROM buses WHERE id = ? FOR UPDATE');
    $busStmt->execute([ $busId ]);
    $bus = $busStmt->fetch();
    if (!$bus) {
        $pdo->rollBack();
        echo json_encode([ 'success' => false, 'message' => 'Invalid bus' ]);
        exit;
    }

    // Ensure seats are locked by this session and not booked
    $inClause = implode(',', array_fill(0, count($seatIds), '?'));

    $locksStmt = $pdo->prepare("SELECT seat_id FROM seat_locks WHERE seat_id IN ($inClause) AND session_id = ? FOR UPDATE");
    $locksStmt->execute(array_merge($seatIds, [ $sessionId ]));
    $lockedSeatIds = $locksStmt->fetchAll(PDO::FETCH_COLUMN);
    sort($lockedSeatIds);

    $sortedSeatIds = $seatIds;
    sort($sortedSeatIds);
    if ($lockedSeatIds !== $sortedSeatIds) {
        $pdo->rollBack();
        echo json_encode([ 'success' => false, 'message' => 'Selected seats are not locked by this session' ]);
        exit;
    }

    $seatCheck = $pdo->prepare("SELECT id, status FROM seats WHERE id IN ($inClause) FOR UPDATE");
    $seatCheck->execute($seatIds);
    $seatRows = $seatCheck->fetchAll();
    foreach ($seatRows as $row) {
        if ($row['status'] === 'booked') {
            $pdo->rollBack();
            echo json_encode([ 'success' => false, 'message' => 'One or more seats already booked' ]);
            exit;
        }
    }

    // Create passenger record (simple)
    $insPassenger = $pdo->prepare('INSERT INTO passengers (name, phone, email) VALUES (?, ?, ?)');
    $insPassenger->execute([ $passengerName, $phone, $email ]);
    $passengerId = (int)$pdo->lastInsertId();

    // Create booking
    $bookingRef = 'BK' . date('Ymd') . random_int(1000, 9999);
    $insBooking = $pdo->prepare('INSERT INTO bookings (booking_ref, bus_id, user_id, status, amount, travel_date) VALUES (?, ?, NULL, ?, ?, ?)');
    $insBooking->execute([ $bookingRef, $busId, 'pending', $amount, $bus['travel_date'] ]);
    $bookingId = (int)$pdo->lastInsertId();

    // Link passenger
    $linkPass = $pdo->prepare('INSERT INTO booking_passengers (booking_id, passenger_id) VALUES (?, ?)');
    $linkPass->execute([ $bookingId, $passengerId ]);

    // Assign seats and mark as booked
    $seatFare = (float)$bus['fare'];
    $insSeat = $pdo->prepare('INSERT INTO booking_seats (booking_id, seat_id, fare) VALUES (?, ?, ?)');
    $updSeat = $pdo->prepare("UPDATE seats SET status = 'booked' WHERE id = ?");
    foreach ($seatIds as $sid) {
        $insSeat->execute([ $bookingId, $sid, $seatFare ]);
        $updSeat->execute([ $sid ]);
    }

    // Decrement bus available seats
    $decBus = $pdo->prepare('UPDATE buses SET available_seats = GREATEST(available_seats - ?, 0) WHERE id = ?');
    $decBus->execute([ count($seatIds), $busId ]);

    // Remove locks for these seats
    $delLocks = $pdo->prepare("DELETE FROM seat_locks WHERE seat_id IN ($inClause)");
    $delLocks->execute($seatIds);

    // Issue ticket
    $ticketNo = 'T' . date('Ymd') . random_int(10000, 99999);
    $insTicket = $pdo->prepare('INSERT INTO tickets (ticket_no, booking_id, issued_by_user_id) VALUES (?, ?, NULL)');
    $insTicket->execute([ $ticketNo, $bookingId ]);

    // Confirm booking
    $updBooking = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
    $updBooking->execute([ $bookingId ]);

    $pdo->commit();

    echo json_encode([ 'success' => true, 'booking_id' => $bookingId, 'booking_ref' => $bookingRef, 'ticket_no' => $ticketNo ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([ 'success' => false, 'message' => 'Server error', 'error' => $e->getMessage() ]);
}
?>


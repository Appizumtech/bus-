<?php
session_start();
header('Content-Type: application/json');

$response = [ 'success' => false, 'message' => 'Unknown error' ];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([ 'success' => false, 'message' => 'Method Not Allowed' ]);
        exit;
    }

    $seatId = isset($_POST['seat_id']) ? (int)$_POST['seat_id'] : 0;
    $busId = isset($_POST['bus_id']) ? (int)$_POST['bus_id'] : 0;
    if ($seatId <= 0 || $busId <= 0) {
        echo json_encode([ 'success' => false, 'message' => 'Invalid parameters' ]);
        exit;
    }

    require_once __DIR__ . '/../admin/includes/db.php'; // provides $pdo

    // Ensure seat_locks table exists
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS seat_locks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            seat_id INT NOT NULL,
            bus_id INT NOT NULL,
            session_id VARCHAR(128) NOT NULL,
            locked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NOT NULL,
            UNIQUE KEY uniq_seat (seat_id),
            KEY idx_expires (expires_at),
            CONSTRAINT fk_lock_seat FOREIGN KEY (seat_id) REFERENCES seats(id) ON DELETE CASCADE,
            CONSTRAINT fk_lock_bus FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    );

    $sessionId = session_id();
    $ttlSeconds = 600; // 10 minutes

    $pdo->beginTransaction();

    // Collect expired locks to free seats
    $expiredStmt = $pdo->prepare('SELECT seat_id FROM seat_locks WHERE expires_at < NOW() FOR UPDATE');
    $expiredStmt->execute();
    $expiredSeatIds = $expiredStmt->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($expiredSeatIds)) {
        $inClause = implode(',', array_fill(0, count($expiredSeatIds), '?'));
        $delStmt = $pdo->prepare("DELETE FROM seat_locks WHERE seat_id IN ($inClause)");
        $delStmt->execute($expiredSeatIds);
        $updStmt = $pdo->prepare("UPDATE seats SET status = 'available' WHERE id IN ($inClause) AND status <> 'booked'");
        $updStmt->execute($expiredSeatIds);
    }

    // Lock seat row
    $seatStmt = $pdo->prepare('SELECT id, bus_id, status FROM seats WHERE id = ? AND bus_id = ? FOR UPDATE');
    $seatStmt->execute([ $seatId, $busId ]);
    $seatRow = $seatStmt->fetch();
    if (!$seatRow) {
        $pdo->rollBack();
        echo json_encode([ 'success' => false, 'message' => 'Seat not found' ]);
        exit;
    }

    if ($seatRow['status'] === 'booked') {
        $pdo->rollBack();
        echo json_encode([ 'success' => false, 'message' => 'Seat already booked', 'status' => 'booked' ]);
        exit;
    }

    // Check existing lock
    $lockStmt = $pdo->prepare('SELECT id, session_id, expires_at FROM seat_locks WHERE seat_id = ? FOR UPDATE');
    $lockStmt->execute([ $seatId ]);
    $lock = $lockStmt->fetch();

    $newExpiry = (new DateTimeImmutable('+'.$ttlSeconds.' seconds'))->format('Y-m-d H:i:s');

    if ($lock) {
        if ($lock['session_id'] === $sessionId) {
            // Extend own lock
            $extStmt = $pdo->prepare('UPDATE seat_locks SET expires_at = ? WHERE id = ?');
            $extStmt->execute([ $newExpiry, $lock['id'] ]);
            // Ensure seat status is reserved
            if ($seatRow['status'] !== 'reserved') {
                $updSeat = $pdo->prepare("UPDATE seats SET status = 'reserved' WHERE id = ?");
                $updSeat->execute([ $seatId ]);
            }
            $pdo->commit();
            echo json_encode([ 'success' => true, 'message' => 'Seat lock extended' ]);
            exit;
        } else {
            $pdo->rollBack();
            echo json_encode([ 'success' => false, 'message' => 'Seat is locked by another user', 'status' => 'reserved' ]);
            exit;
        }
    }

    // Create new lock
    $insLock = $pdo->prepare('INSERT INTO seat_locks (seat_id, bus_id, session_id, expires_at) VALUES (?, ?, ?, ?)');
    $insLock->execute([ $seatId, $busId, $sessionId, $newExpiry ]);

    // Update seat status to reserved
    $updSeat = $pdo->prepare("UPDATE seats SET status = 'reserved' WHERE id = ?");
    $updSeat->execute([ $seatId ]);

    $pdo->commit();
    echo json_encode([ 'success' => true, 'message' => 'Seat locked' ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([ 'success' => false, 'message' => 'Server error', 'error' => $e->getMessage() ]);
}
?>


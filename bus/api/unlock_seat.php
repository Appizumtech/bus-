<?php
session_start();
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([ 'success' => false, 'message' => 'Method Not Allowed' ]);
        exit;
    }

    $seatId = isset($_POST['seat_id']) ? (int)$_POST['seat_id'] : 0;
    if ($seatId <= 0) {
        echo json_encode([ 'success' => false, 'message' => 'Invalid seat id' ]);
        exit;
    }

    require_once __DIR__ . '/../admin/includes/db.php';

    $sessionId = session_id();

    $pdo->beginTransaction();

    // Only unlock seats locked by this session
    $sel = $pdo->prepare('SELECT id FROM seat_locks WHERE seat_id = ? AND session_id = ? FOR UPDATE');
    $sel->execute([ $seatId, $sessionId ]);
    $lock = $sel->fetch();
    if (!$lock) {
        $pdo->rollBack();
        echo json_encode([ 'success' => false, 'message' => 'No lock found for this seat' ]);
        exit;
    }

    $del = $pdo->prepare('DELETE FROM seat_locks WHERE id = ?');
    $del->execute([ $lock['id'] ]);

    // Reset seat to available only if not booked
    $upd = $pdo->prepare("UPDATE seats SET status = 'available' WHERE id = ? AND status <> 'booked'");
    $upd->execute([ $seatId ]);

    $pdo->commit();
    echo json_encode([ 'success' => true ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([ 'success' => false, 'message' => 'Server error', 'error' => $e->getMessage() ]);
}
?>


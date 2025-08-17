<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $passenger_name = $_POST['passenger_name'] ?? '';
    $age = $_POST['age'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $bus_id = (int)($_POST['bus_id'] ?? 0);
    $fare = (float)($_POST['fare'] ?? 0);

    // Validate the data
    if (empty($passenger_name) || empty($age) || empty($gender) || empty($phone) || empty($email) || empty($bus_id) || empty($fare)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: booking.php?bus_id=" . $bus_id);
        exit();
    }

    // Persist booking to DB
    require __DIR__ . '/admin/includes/db.php';

    try {
        $pdo->beginTransaction();
        $booking_ref = 'BK' . date('YmdHis') . rand(100, 999);
        $travel_date_stmt = $pdo->prepare('SELECT travel_date FROM buses WHERE id = ?');
        $travel_date_stmt->execute([$bus_id]);
        $travel_date = $travel_date_stmt->fetchColumn();
        if (!$travel_date) { throw new Exception('Invalid bus selected'); }

        $userId = isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'customer' ? (int)$_SESSION['user_id'] : null;

        $insBooking = $pdo->prepare('INSERT INTO bookings (booking_ref, bus_id, user_id, status, amount, travel_date) VALUES (?,?,?,?,?,?)');
        $insBooking->execute([$booking_ref, $bus_id, $userId, 'confirmed', $fare, $travel_date]);
        $bookingId = (int)$pdo->lastInsertId();

        // Create passenger and link
        $insPassenger = $pdo->prepare('INSERT INTO passengers (name, age, gender, phone, email) VALUES (?,?,?,?,?)');
        $insPassenger->execute([$passenger_name, $age, $gender, $phone, $email]);
        $passengerId = (int)$pdo->lastInsertId();

        $link = $pdo->prepare('INSERT INTO booking_passengers (booking_id, passenger_id) VALUES (?, ?)');
        $link->execute([$bookingId, $passengerId]);

        $pdo->commit();

        // Store booking details in session for confirmation page
        $_SESSION['booking'] = [
            'reference' => $booking_ref,
            'passenger_name' => $passenger_name,
            'age' => $age,
            'gender' => $gender,
            'phone' => $phone,
            'email' => $email,
            'bus_id' => $bus_id,
            'fare' => $fare,
            'booking_date' => date('Y-m-d H:i:s')
        ];

        header("Location: booking_confirmation.php");
        exit();
    } catch (Throwable $t) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Booking error: ' . $t->getMessage();
        header("Location: booking.php?bus_id=" . $bus_id);
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?> 
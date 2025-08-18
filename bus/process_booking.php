<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$passenger_name = trim($_POST['passenger_name'] ?? '');
$age = (int)($_POST['age'] ?? 0);
$gender = trim($_POST['gender'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$bus_id = (int)($_POST['bus_id'] ?? 0);
$seat_ids_csv = trim($_POST['seat_ids'] ?? '');
$amount = (float)($_POST['amount'] ?? 0);

if ($passenger_name === '' || !$age || $gender === '' || $phone === '' || $email === '' || !$bus_id || $seat_ids_csv === '' || $amount <= 0) {
    $_SESSION['error'] = 'All fields are required';
    header('Location: booking.php?bus_id=' . urlencode((string)$bus_id));
    exit();
}

require_once __DIR__ . '/admin/includes/db.php';

// Delegate to API logic internally to keep single source of truth
$_POST = [
    'bus_id' => $bus_id,
    'seat_ids' => $seat_ids_csv,
    'passenger_name' => $passenger_name,
    'phone' => $phone,
    'email' => $email,
    'amount' => $amount
];

ob_start();
include __DIR__ . '/api/create_booking.php';
$raw = ob_get_clean();
$resp = json_decode($raw, true);

if (!$resp || empty($resp['success'])) {
    $_SESSION['error'] = $resp['message'] ?? 'Unable to create booking';
    header('Location: booking.php?bus_id=' . urlencode((string)$bus_id));
    exit();
}

// Load booking details for confirmation
$_SESSION['booking'] = [
    'reference' => $resp['booking_ref'] ?? '',
    'passenger_name' => $passenger_name,
    'age' => $age,
    'gender' => $gender,
    'phone' => $phone,
    'email' => $email,
    'bus_id' => $bus_id,
    'fare' => $amount,
    'booking_date' => date('Y-m-d H:i:s')
];

header('Location: booking_confirmation.php');
exit();
?>
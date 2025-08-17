<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $passenger_name = $_POST['passenger_name'] ?? '';
    $age = $_POST['age'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $bus_id = $_POST['bus_id'] ?? '';
    $fare = $_POST['fare'] ?? '';

    // Validate the data
    if (empty($passenger_name) || empty($age) || empty($gender) || empty($phone) || empty($email) || empty($bus_id) || empty($fare)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: booking.php?bus_id=" . $bus_id);
        exit();
    }

    // Here you would typically:
    // 1. Sanitize the input data
    // 2. Save the booking details to database
    // 3. Generate a booking reference number
    // 4. Send confirmation email
    // For now, we'll just simulate a successful booking

    // Generate a random booking reference number
    $booking_ref = 'BK' . date('Ymd') . rand(1000, 9999);

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

    // Redirect to booking confirmation page
    header("Location: booking_confirmation.php");
    exit();
} else {
    // If someone tries to access this file directly
    header("Location: index.php");
    exit();
}
?> 
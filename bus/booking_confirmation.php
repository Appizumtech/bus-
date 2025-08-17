<?php
session_start();
$booking = $_SESSION['booking'] ?? null;
if (!$booking) {
	header('Location: index.php');
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Booking Confirmation</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
	<div class="container">
		<h1>Booking Confirmed</h1>
		<div class="card">
			<div class="card-body">
				<p><strong>Reference:</strong> <?= htmlspecialchars($booking['reference']) ?></p>
				<p><strong>Name:</strong> <?= htmlspecialchars($booking['passenger_name']) ?></p>
				<p><strong>Bus ID:</strong> <?= htmlspecialchars($booking['bus_id']) ?></p>
				<p><strong>Fare:</strong> ₹<?= number_format((float)$booking['fare'], 2) ?></p>
				<p><strong>Date:</strong> <?= htmlspecialchars($booking['booking_date']) ?></p>
				<a class="btn btn-secondary" href="/">Home</a>
			</div>
		</div>
	</div>
</body>
</html>
<?php
require 'includes/header.php';
require 'includes/db.php';

$role = $_SESSION['role'] ?? '';
$userId = (int)($_SESSION['user_id'] ?? 0);

if ($role === 'owner') {
	$totalBookingsStmt = $pdo->prepare("SELECT COUNT(*) FROM bookings b JOIN buses bs ON b.bus_id = bs.id WHERE bs.owner_id = ?");
	$totalBookingsStmt->execute([$userId]);
	$totalBookings = (int)$totalBookingsStmt->fetchColumn();

	$totalRevenueStmt = $pdo->prepare("SELECT COALESCE(SUM(b.amount),0) FROM bookings b JOIN buses bs ON b.bus_id = bs.id WHERE b.status = 'confirmed' AND bs.owner_id = ?");
	$totalRevenueStmt->execute([$userId]);
	$totalRevenue = (float)$totalRevenueStmt->fetchColumn();

	$activeBusesStmt = $pdo->prepare('SELECT COUNT(*) FROM buses WHERE owner_id = ?');
	$activeBusesStmt->execute([$userId]);
	$activeBuses = (int)$activeBusesStmt->fetchColumn();
} elseif ($role === 'agent') {
	$totalBookingsStmt = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE user_id = ?');
	$totalBookingsStmt->execute([$userId]);
	$totalBookings = (int)$totalBookingsStmt->fetchColumn();

	$totalRevenueStmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM bookings WHERE status = 'confirmed' AND user_id = ?");
	$totalRevenueStmt->execute([$userId]);
	$totalRevenue = (float)$totalRevenueStmt->fetchColumn();

	$activeBuses = (int)$pdo->query('SELECT COUNT(*) FROM buses')->fetchColumn();
} else {
	$totalBookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
	$totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM bookings WHERE status = 'confirmed'")->fetchColumn();
	$activeBuses = (int)$pdo->query('SELECT COUNT(*) FROM buses')->fetchColumn();
}
?>

<div class="container">
    <h1>Dashboard</h1>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Bookings</h5>
                    <p class="display-6"><?php echo $totalBookings; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue</h5>
                    <p class="display-6">₹<?php echo number_format($totalRevenue, 2); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Active Buses</h5>
                    <p class="display-6"><?php echo $activeBuses; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?> 
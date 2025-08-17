<?php
require '../includes/header.php';
require '../includes/db.php';

$role = $_SESSION['role'] ?? '';
$userId = $_SESSION['user_id'] ?? 0;

if ($role === 'owner') {
	$stmt = $pdo->prepare("SELECT id, name, source, destination, travel_date FROM buses WHERE owner_id = ? ORDER BY travel_date DESC");
	$stmt->execute([$userId]);
	$buses = $stmt->fetchAll();
} else {
	$buses = $pdo->query("SELECT id, name, source, destination, travel_date FROM buses ORDER BY travel_date DESC")->fetchAll();
}
?>
<div class="container">
    <h1>Routes & Points</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Bus</th>
                <th>Route</th>
                <th>Date</th>
                <th>Manage Points</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($buses as $b): ?>
            <tr>
                <td><?= htmlspecialchars($b['name']) ?></td>
                <td><?= htmlspecialchars($b['source']) ?> → <?= htmlspecialchars($b['destination']) ?></td>
                <td><?= htmlspecialchars($b['travel_date']) ?></td>
                <td><a class="btn btn-sm btn-secondary" href="/bus/admin/routes/points.php?bus_id=<?= $b['id'] ?>">Boarding/Dropping Points</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require '../includes/footer.php'; ?>
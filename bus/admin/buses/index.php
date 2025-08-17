<?php
require '../includes/header.php';
require '../includes/db.php';

$role = $_SESSION['role'] ?? '';
$userId = $_SESSION['user_id'] ?? 0;

$sql = "SELECT bs.*, u.name AS owner_name FROM buses bs LEFT JOIN users u ON bs.owner_id = u.id";
$params = [];
if ($role === 'owner') {
	$sql .= ' WHERE bs.owner_id = ?';
	$params[] = $userId;
}
$sql .= ' ORDER BY bs.travel_date DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$buses = $stmt->fetchAll();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Buses</h1>
        <a href="create.php" class="btn btn-primary">Add New Bus</a>
    </div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Owner</th>
                <th>Route</th>
                <th>Date</th>
                <th>Fare</th>
                <th>Seats</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($buses as $bus): ?>
            <tr>
                <td><?= $bus['id'] ?></td>
                <td><?= htmlspecialchars($bus['name']) ?></td>
                <td><?= htmlspecialchars($bus['owner_name'] ?? '-') ?></td>
                <td><?= htmlspecialchars($bus['source']) ?> → <?= htmlspecialchars($bus['destination']) ?></td>
                <td><?= htmlspecialchars($bus['travel_date']) ?></td>
                <td>₹<?= number_format($bus['fare'], 2) ?></td>
                <td><?= (int)$bus['available_seats'] ?>/<?= (int)$bus['total_seats'] ?></td>
                <td>
                    <a href="edit.php?id=<?= $bus['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="/bus/admin/routes/points.php?bus_id=<?= $bus['id'] ?>" class="btn btn-sm btn-secondary">Points</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require '../includes/footer.php'; ?> 
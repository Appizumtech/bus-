<?php
require '../includes/header.php';
require '../includes/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
	echo '<div class="alert alert-danger">Super admin only.</div>';
	require '../includes/footer.php';
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$ownerId = (int)($_POST['owner_id'] ?? 0);
	$amount = (float)($_POST['amount'] ?? 0);
	$start = $_POST['start_date'] ?? '';
	$end = $_POST['end_date'] ?? '';
	if ($ownerId > 0 && $amount > 0 && $start && $end) {
		$pdo->prepare('INSERT INTO subscriptions (owner_id, amount, start_date, end_date, status) VALUES (?,?,?,?,"active")')
			->execute([$ownerId, $amount, $start, $end]);
	}
}

$owners = $pdo->query("SELECT id, name FROM users WHERE role = 'owner' ORDER BY name")->fetchAll();
$subs = $pdo->query("SELECT s.*, u.name AS owner_name FROM subscriptions s JOIN users u ON s.owner_id = u.id ORDER BY s.created_at DESC")->fetchAll();
?>
<div class="container">
    <h1>Owner Subscriptions</h1>
    <form method="post" class="row g-2 mb-3">
        <div class="col-md-4">
            <label class="form-label">Owner</label>
            <select name="owner_id" class="form-select" required>
                <option value="">Select Owner</option>
                <?php foreach ($owners as $o): ?>
                <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Amount (₹)</label>
            <input type="number" step="0.01" class="form-control" name="amount" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Start Date</label>
            <input type="date" class="form-control" name="start_date" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">End Date</label>
            <input type="date" class="form-control" name="end_date" required>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Add Subscription</button>
        </div>
    </form>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Owner</th>
                <th>Plan</th>
                <th>Amount</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subs as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['owner_name']) ?></td>
                <td><?= htmlspecialchars($s['plan_name']) ?></td>
                <td>₹<?= number_format($s['amount'], 2) ?></td>
                <td><?= htmlspecialchars($s['start_date']) ?></td>
                <td><?= htmlspecialchars($s['end_date']) ?></td>
                <td><?= htmlspecialchars($s['status']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require '../includes/footer.php'; ?>
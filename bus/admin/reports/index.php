<?php
require '../includes/header.php';
require '../includes/db.php';

$period = $_GET['period'] ?? 'daily';
$ownerId = isset($_GET['owner_id']) ? (int)$_GET['owner_id'] : 0;
$agentId = isset($_GET['agent_id']) ? (int)$_GET['agent_id'] : 0;

switch ($period) {
	case 'weekly':
		$dateFilter = 'YEARWEEK(b.created_at, 1) = YEARWEEK(CURDATE(), 1)';
		break;
	case 'monthly':
		$dateFilter = 'YEAR(b.created_at) = YEAR(CURDATE()) AND MONTH(b.created_at) = MONTH(CURDATE())';
		break;
	case 'yearly':
		$dateFilter = 'YEAR(b.created_at) = YEAR(CURDATE())';
		break;
	case 'daily':
	default:
		$dateFilter = 'DATE(b.created_at) = CURDATE()';
}

$where = [$dateFilter];
$params = [];
if ($ownerId > 0) {
	$where[] = 'bs.owner_id = ?';
	$params[] = $ownerId;
}
if ($agentId > 0) {
	$where[] = 'b.user_id = ?';
	$params[] = $agentId;
}
$whereSql = 'WHERE ' . implode(' AND ', $where);

$sql = "SELECT COUNT(*) AS num_bookings, COALESCE(SUM(b.amount),0) AS total_amount FROM bookings b JOIN buses bs ON b.bus_id = bs.id $whereSql";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$summary = $stmt->fetch();

$owners = $pdo->query("SELECT id, name FROM users WHERE role = 'owner' ORDER BY name")->fetchAll();
$agents = $pdo->query("SELECT id, name FROM users WHERE role = 'agent' ORDER BY name")->fetchAll();

$subscriptionSummary = null;
if (($_SESSION['role'] ?? '') === 'super_admin' && in_array($period, ['monthly','yearly'])) {
	if ($period === 'monthly') {
		$subSql = "SELECT COALESCE(SUM(amount),0) FROM subscriptions WHERE YEAR(start_date) = YEAR(CURDATE()) AND MONTH(start_date) = MONTH(CURDATE())";
	} else {
		$subSql = "SELECT COALESCE(SUM(amount),0) FROM subscriptions WHERE YEAR(start_date) = YEAR(CURDATE())";
	}
	$subscriptionSummary = (float)$pdo->query($subSql)->fetchColumn();
}
?>
<div class="container">
    <h1>Reports</h1>
    <form class="row g-2 mb-3">
        <div class="col-md-2">
            <label class="form-label">Period</label>
            <select name="period" class="form-select">
                <option value="daily"<?= $period==='daily'?' selected':''; ?>>Daily</option>
                <option value="weekly"<?= $period==='weekly'?' selected':''; ?>>Weekly</option>
                <option value="monthly"<?= $period==='monthly'?' selected':''; ?>>Monthly</option>
                <option value="yearly"<?= $period==='yearly'?' selected':''; ?>>Yearly</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Owner</label>
            <select name="owner_id" class="form-select">
                <option value="0">All</option>
                <?php foreach ($owners as $o): ?>
                <option value="<?= $o['id'] ?>"<?= $ownerId===$o['id']?' selected':''; ?>><?= htmlspecialchars($o['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Agent</label>
            <select name="agent_id" class="form-select">
                <option value="0">All</option>
                <?php foreach ($agents as $a): ?>
                <option value="<?= $a['id'] ?>"<?= $agentId===$a['id']?' selected':''; ?>><?= htmlspecialchars($a['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100">Apply</button>
        </div>
    </form>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Bookings</h5>
                    <p class="display-6"><?php echo (int)($summary['num_bookings'] ?? 0); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Amount</h5>
                    <p class="display-6">₹<?php echo number_format((float)($summary['total_amount'] ?? 0), 2); ?></p>
                </div>
            </div>
        </div>
    </div>
<div class="row g-3 mt-1">
	<?php if ($subscriptionSummary !== null): ?>
	<div class="col-md-6">
		<div class="card">
			<div class="card-body">
				<h5 class="card-title">Subscription Revenue</h5>
				<p class="display-6">₹<?= number_format($subscriptionSummary, 2) ?></p>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>
</div>
<?php require '../includes/footer.php'; ?>
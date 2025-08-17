<?php
require 'includes/header.php';
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$perTicket = (float)($_POST['per_ticket_charge'] ?? 0);
	$yearly = (float)($_POST['subscription_yearly_amount'] ?? 0);
	$pdo->prepare('UPDATE admin_settings SET per_ticket_charge = ?, subscription_yearly_amount = ? WHERE id = 1')
		->execute([$perTicket, $yearly]);
	header('Location: settings.php?saved=1');
	exit;
}

$settings = $pdo->query('SELECT * FROM admin_settings WHERE id = 1')->fetch();
?>
<div class="container">
    <h1>Admin Settings</h1>
    <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success">Settings saved.</div>
    <?php endif; ?>
    <form method="post" class="row g-3" style="max-width: 540px;">
        <div class="col-12">
            <label class="form-label">Per Ticket Charge (₹)</label>
            <input type="number" step="0.01" class="form-control" name="per_ticket_charge" value="<?= htmlspecialchars($settings['per_ticket_charge'] ?? 0) ?>">
        </div>
        <div class="col-12">
            <label class="form-label">Yearly Subscription Amount (₹)</label>
            <input type="number" step="0.01" class="form-control" name="subscription_yearly_amount" value="<?= htmlspecialchars($settings['subscription_yearly_amount'] ?? 0) ?>">
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Save</button>
        </div>
    </form>
</div>
<?php require 'includes/footer.php'; ?>
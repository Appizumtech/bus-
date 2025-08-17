<?php
require '../includes/header.php';
require '../includes/db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['super_admin','owner'])) {
	echo '<div class="alert alert-danger">Not authorized.</div>';
	require '../includes/footer.php';
	exit;
}

$role = $_SESSION['role'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim($_POST['name'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';
	$userRole = $_POST['role'] ?? 'customer';
	$assignedOwnerId = null;
	if ($role === 'owner') {
		// owner can create only agent/customer, assign to self
		if (!in_array($userRole, ['agent','customer'])) {
			$userRole = 'agent';
		}
		$assignedOwnerId = (int)($_SESSION['user_id'] ?? 0);
	} else {
		// super admin: can assign owner for agents
		if ($userRole === 'agent') {
			$assignedOwnerId = isset($_POST['assigned_owner_id']) && $_POST['assigned_owner_id'] !== '' ? (int)$_POST['assigned_owner_id'] : null;
		}
	}
	if ($name && $email && $password) {
		$ins = $pdo->prepare('INSERT INTO users (name, email, password, role, assigned_owner_id, active) VALUES (?,?,?,?,?,1)');
		try {
			$ins->execute([$name, $email, $password, $userRole, $assignedOwnerId]);
			header('Location: index.php?created=1');
			exit;
		} catch (Throwable $t) {
			$msg = 'Error: ' . $t->getMessage();
		}
	}
}

$owners = $pdo->query("SELECT id, name FROM users WHERE role = 'owner' ORDER BY name")->fetchAll();
?>
<div class="container">
	<h1>Create User</h1>
	<?php if ($msg): ?><div class="alert alert-danger"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
	<form method="post" class="row g-3" style="max-width:720px;">
		<div class="col-md-6">
			<label class="form-label">Name</label>
			<input class="form-control" name="name" required>
		</div>
		<div class="col-md-6">
			<label class="form-label">Email</label>
			<input type="email" class="form-control" name="email" required>
		</div>
		<div class="col-md-6">
			<label class="form-label">Password</label>
			<input type="text" class="form-control" name="password" required>
		</div>
		<div class="col-md-6">
			<label class="form-label">Role</label>
			<select name="role" class="form-select">
				<?php if ($role === 'super_admin'): ?>
				<option value="super_admin">Super Admin</option>
				<option value="owner">Owner</option>
				<?php endif; ?>
				<option value="agent">Agent</option>
				<option value="customer" selected>Customer</option>
			</select>
		</div>
		<?php if ($role === 'super_admin'): ?>
		<div class="col-md-6" id="ownerSelect">
			<label class="form-label">Assigned Owner (for Agent)</label>
			<select name="assigned_owner_id" class="form-select">
				<option value="">None</option>
				<?php foreach ($owners as $o): ?>
				<option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php endif; ?>
		<div class="col-12">
			<button class="btn btn-primary">Create</button>
			<a href="index.php" class="btn btn-secondary">Cancel</a>
		</div>
	</form>
</div>
<?php require '../includes/footer.php'; ?>
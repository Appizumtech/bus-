<?php
require '../includes/header.php';
require '../includes/db.php';

$filterRole = $_GET['role'] ?? '';
$params = [];
$whereParts = [];

if ($filterRole) {
	$whereParts[] = 'u.role = ?';
	$params[] = $filterRole;
}

// Owners can only see their agents/customers
if (($_SESSION['role'] ?? '') === 'owner') {
	$whereParts[] = 'u.assigned_owner_id = ?';
	$params[] = (int)($_SESSION['user_id'] ?? 0);
	// If no explicit role filter, default to agent/customer only for owners
	if (!$filterRole) {
		$whereParts[] = "u.role IN ('agent','customer')";
	}
}

$whereSql = $whereParts ? ('WHERE ' . implode(' AND ', $whereParts)) : '';

$stmt = $pdo->prepare("SELECT u.*, o.name AS owner_name FROM users u LEFT JOIN users o ON u.assigned_owner_id = o.id $whereSql ORDER BY u.created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Users</h1>
        <div>
            <a class="btn btn-sm btn-outline-secondary" href="?">All</a>
            <a class="btn btn-sm btn-outline-secondary" href="?role=owner">Owners</a>
            <a class="btn btn-sm btn-outline-secondary" href="?role=agent">Agents</a>
            <a class="btn btn-sm btn-outline-secondary" href="?role=customer">Customers</a>
            <a class="btn btn-sm btn-outline-secondary" href="?role=super_admin">Super Admin</a>
            <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['super_admin','owner'])): ?>
            <a class="btn btn-sm btn-primary" href="create.php">Create User</a>
            <?php endif; ?>
        </div>
    </div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Owner</th>
                <th>Status</th>
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['super_admin','owner'])): ?>
                <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td><?= htmlspecialchars($u['owner_name'] ?? '-') ?></td>
                <td><?= $u['active'] ? 'Active' : 'Inactive' ?></td>
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['super_admin','owner'])): ?>
                <td>
                    <a class="btn btn-sm btn-outline-secondary" href="toggle.php?id=<?= $u['id'] ?>"><?= $u['active'] ? 'Deactivate' : 'Activate' ?></a>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require '../includes/footer.php'; ?>
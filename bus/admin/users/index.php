<?php
require '../includes/header.php';
require '../includes/db.php';

$role = $_GET['role'] ?? '';
$params = [];
$where = '';
if ($role) {
	$where = 'WHERE role = ?';
	$params[] = $role;
}

$stmt = $pdo->prepare("SELECT u.*, o.name AS owner_name FROM users u LEFT JOIN users o ON u.assigned_owner_id = o.id $where ORDER BY u.created_at DESC");
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
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require '../includes/footer.php'; ?>
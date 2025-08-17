<?php
require '../includes/header.php';
require '../includes/db.php';

$busId = isset($_GET['bus_id']) ? (int)$_GET['bus_id'] : 0;
if ($busId <= 0) {
	echo '<div class="alert alert-danger">Invalid bus</div>';
	require '../includes/footer.php';
	exit;
}

$bus = $pdo->prepare('SELECT * FROM buses WHERE id = ?');
$bus->execute([$busId]);
$busRow = $bus->fetch();
if (!$busRow) {
	echo '<div class="alert alert-danger">Bus not found</div>';
	require '../includes/footer.php';
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$type = $_POST['type'] ?? '';
	$name = trim($_POST['name'] ?? '');
	$time = $_POST['time'] ?? null;
	$address = trim($_POST['address'] ?? '');
	if ($type === 'boarding') {
		$stmt = $pdo->prepare('INSERT INTO boarding_points (bus_id, name, time, address) VALUES (?,?,?,?)');
		$stmt->execute([$busId, $name, $time, $address]);
	} elseif ($type === 'dropping') {
		$stmt = $pdo->prepare('INSERT INTO dropping_points (bus_id, name, time, address) VALUES (?,?,?,?)');
		$stmt->execute([$busId, $name, $time, $address]);
	}
	header('Location: points.php?bus_id=' . $busId);
	exit;
}

$boarding = $pdo->prepare('SELECT * FROM boarding_points WHERE bus_id = ? ORDER BY time');
$boarding->execute([$busId]);
$boardingPoints = $boarding->fetchAll();

$dropping = $pdo->prepare('SELECT * FROM dropping_points WHERE bus_id = ? ORDER BY time');
$dropping->execute([$busId]);
$droppingPoints = $dropping->fetchAll();
?>
<div class="container">
    <h1>Manage Points - <?= htmlspecialchars($busRow['name']) ?></h1>
    <p><?= htmlspecialchars($busRow['source']) ?> → <?= htmlspecialchars($busRow['destination']) ?> (<?= htmlspecialchars($busRow['travel_date']) ?>)</p>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Boarding Points</div>
                <div class="card-body">
                    <form method="post" class="row g-2 mb-3">
                        <input type="hidden" name="type" value="boarding" />
                        <div class="col-md-5"><input class="form-control" name="name" placeholder="Name" required></div>
                        <div class="col-md-3"><input type="time" class="form-control" name="time"></div>
                        <div class="col-md-4"><input class="form-control" name="address" placeholder="Address"></div>
                        <div class="col-12"><button class="btn btn-sm btn-primary">Add Boarding</button></div>
                    </form>
                    <ul class="list-group">
                        <?php foreach ($boardingPoints as $p): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?= htmlspecialchars($p['name']) ?> <?= $p['time'] ? '(' . htmlspecialchars($p['time']) . ')' : '' ?> - <?= htmlspecialchars($p['address'] ?? '') ?></span>
                            <a class="btn btn-sm btn-outline-danger" href="remove_point.php?type=boarding&id=<?= $p['id'] ?>&bus_id=<?= $busId ?>">Remove</a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Dropping Points</div>
                <div class="card-body">
                    <form method="post" class="row g-2 mb-3">
                        <input type="hidden" name="type" value="dropping" />
                        <div class="col-md-5"><input class="form-control" name="name" placeholder="Name" required></div>
                        <div class="col-md-3"><input type="time" class="form-control" name="time"></div>
                        <div class="col-md-4"><input class="form-control" name="address" placeholder="Address"></div>
                        <div class="col-12"><button class="btn btn-sm btn-primary">Add Dropping</button></div>
                    </form>
                    <ul class="list-group">
                        <?php foreach ($droppingPoints as $p): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?= htmlspecialchars($p['name']) ?> <?= $p['time'] ? '(' . htmlspecialchars($p['time']) . ')' : '' ?> - <?= htmlspecialchars($p['address'] ?? '') ?></span>
                            <a class="btn btn-sm btn-outline-danger" href="remove_point.php?type=dropping&id=<?= $p['id'] ?>&bus_id=<?= $busId ?>">Remove</a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require '../includes/footer.php'; ?>
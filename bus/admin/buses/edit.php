<?php
require '../includes/header.php';
require '../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$busStmt = $pdo->prepare('SELECT * FROM buses WHERE id = ?');
$busStmt->execute([$id]);
$bus = $busStmt->fetch();
if (!$bus) { echo '<div class="alert alert-danger">Bus not found</div>'; require '../includes/footer.php'; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim($_POST['name'] ?? '');
	$source = trim($_POST['source'] ?? '');
	$destination = trim($_POST['destination'] ?? '');
	$travel_date = $_POST['travel_date'] !== '' ? $_POST['travel_date'] : null;
	$departure_time = $_POST['departure_time'] ?? '';
	$arrival_time = $_POST['arrival_time'] ?? '';
	$bus_type = $_POST['bus_type'] ?? '';
	$total_seats = (int)($_POST['total_seats'] ?? 0);
	$fare = (float)($_POST['fare'] ?? 0);
	$seat_layout = $_POST['seat_layout'] ?? '2x2';
	$deck_type = $_POST['deck_type'] ?? 'lower_only';
	$owner_id = isset($_POST['owner_id']) && $_POST['owner_id'] !== '' ? (int)$_POST['owner_id'] : null;

	$upd = $pdo->prepare('UPDATE buses SET owner_id = ?, name = ?, source = ?, destination = ?, travel_date = ?, departure_time = ?, arrival_time = ?, bus_type = ?, total_seats = ?, fare = ?, seat_layout = ?, deck_type = ? WHERE id = ?');
	$upd->execute([$owner_id, $name, $source, $destination, $travel_date, $departure_time, $arrival_time, $bus_type, $total_seats, $fare, $seat_layout, $deck_type, $id]);
	$bus = array_merge($bus, $_POST);
	$bus['owner_id'] = $owner_id;
	$bus['travel_date'] = $travel_date;
	$bus['deck_type'] = $deck_type;
	echo '<div class="alert alert-success">Updated.</div>';
}

$owners = $pdo->query("SELECT id, name FROM users WHERE role = 'owner' ORDER BY name")->fetchAll();
?>
<div class="container">
    <h1>Edit Bus</h1>
    <form method="post" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Name</label>
            <input class="form-control" name="name" value="<?= htmlspecialchars($bus['name']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">From</label>
            <input class="form-control" name="source" value="<?= htmlspecialchars($bus['source']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">To</label>
            <input class="form-control" name="destination" value="<?= htmlspecialchars($bus['destination']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Travel Date (optional)</label>
            <input type="date" class="form-control" name="travel_date" value="<?= htmlspecialchars($bus['travel_date']) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Departure Time</label>
            <input type="time" class="form-control" name="departure_time" value="<?= htmlspecialchars($bus['departure_time']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Arrival Time</label>
            <input type="time" class="form-control" name="arrival_time" value="<?= htmlspecialchars($bus['arrival_time']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Type</label>
            <select name="bus_type" class="form-select" required>
                <?php foreach (["AC Sleeper","AC Seater","Non-AC Sleeper","Non-AC Seater"] as $t): ?>
                    <option value="<?= $t ?>"<?= $bus['bus_type']===$t?' selected':''; ?>><?= $t ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Total Seats</label>
            <input type="number" class="form-control" name="total_seats" value="<?= (int)$bus['total_seats'] ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Fare (₹)</label>
            <input type="number" step="0.01" class="form-control" name="fare" value="<?= htmlspecialchars($bus['fare']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Seat Layout</label>
            <select name="seat_layout" class="form-select" required>
                <?php foreach (["2x2","2x1","3x2"] as $l): ?>
                    <option value="<?= $l ?>"<?= $bus['seat_layout']===$l?' selected':''; ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Deck</label>
            <select name="deck_type" class="form-select" required>
                <?php foreach (["lower_only"=>"Lower Only","upper_and_lower"=>"Upper & Lower"] as $val=>$label): ?>
                    <option value="<?= $val ?>"<?= ($bus['deck_type'] ?? 'lower_only')===$val?' selected':''; ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Owner</label>
            <select name="owner_id" class="form-select">
                <option value="">None</option>
                <?php foreach ($owners as $o): ?>
                <option value="<?= $o['id'] ?>"<?= (string)$bus['owner_id']===(string)$o['id']?' selected':''; ?>><?= htmlspecialchars($o['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Save</button>
        </div>
    </form>
</div>
<?php require '../includes/footer.php'; ?>
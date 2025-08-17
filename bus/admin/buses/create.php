<?php
require '../includes/header.php';
require '../includes/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim($_POST['name'] ?? '');
	$source = trim($_POST['source'] ?? '');
	$destination = trim($_POST['destination'] ?? '');
	$travel_date = $_POST['travel_date'] ?? '';
	$departure_time = $_POST['departure_time'] ?? '';
	$arrival_time = $_POST['arrival_time'] ?? '';
	$bus_type = $_POST['bus_type'] ?? '';
	$total_seats = (int)($_POST['total_seats'] ?? 0);
	$fare = (float)($_POST['fare'] ?? 0);
	$seat_layout = $_POST['seat_layout'] ?? '2x2';
	$owner_id = isset($_POST['owner_id']) && $_POST['owner_id'] !== '' ? (int)$_POST['owner_id'] : null;

	// If logged-in user is owner, force owner_id to self
	if (($_SESSION['role'] ?? '') === 'owner') {
		$owner_id = (int)($_SESSION['user_id'] ?? 0);
	}

	$generate_seats = isset($_POST['generate_seats']);

	$ins = $pdo->prepare('INSERT INTO buses (owner_id, name, source, destination, travel_date, departure_time, arrival_time, bus_type, total_seats, available_seats, fare, seat_layout) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
	$ins->execute([$owner_id, $name, $source, $destination, $travel_date, $departure_time, $arrival_time, $bus_type, $total_seats, $total_seats, $fare, $seat_layout]);
	$busId = (int)$pdo->lastInsertId();

	if ($generate_seats && $busId > 0) {
		$seatType = stripos($bus_type, 'Sleeper') !== false ? 'sleeper' : 'seater';
		$deckOptions = $seatType === 'sleeper' ? ['lower','upper'] : ['lower'];
		$perDeck = $seatType === 'sleeper' ? (int)ceil($total_seats/2) : $total_seats;
		$insertSeat = $pdo->prepare('INSERT INTO seats (bus_id, seat_number, seat_type, deck, status) VALUES (?,?,?,?,"available")');
		if ($seatType === 'sleeper') {
			for ($i=1; $i<=$perDeck; $i++) { $insertSeat->execute([$busId, 'L'.$i, 'sleeper', 'lower']); }
			for ($i=1; $i<=$perDeck; $i++) { $insertSeat->execute([$busId, 'U'.$i, 'sleeper', 'upper']); }
		} else {
			for ($i=1; $i<=$total_seats; $i++) { $insertSeat->execute([$busId, 'S'.$i, 'seater', 'lower']); }
		}
	}

	$message = 'Bus created successfully.';
}

$owners = $pdo->query("SELECT id, name FROM users WHERE role = 'owner' ORDER BY name")->fetchAll();
?>
<div class="container">
    <h1>Add New Bus</h1>
    <?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="post" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Name</label>
            <input class="form-control" name="name" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">From</label>
            <input class="form-control" name="source" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">To</label>
            <input class="form-control" name="destination" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Travel Date</label>
            <input type="date" class="form-control" name="travel_date" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Departure Time</label>
            <input type="time" class="form-control" name="departure_time" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Arrival Time</label>
            <input type="time" class="form-control" name="arrival_time" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Type</label>
            <select name="bus_type" class="form-select" required>
                <option value="AC Sleeper">AC Sleeper</option>
                <option value="AC Seater">AC Seater</option>
                <option value="Non-AC Seater">Non-AC Seater</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Total Seats</label>
            <input type="number" class="form-control" name="total_seats" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Fare (₹)</label>
            <input type="number" step="0.01" class="form-control" name="fare" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Seat Layout</label>
            <select name="seat_layout" class="form-select" required>
                <option value="2x2">2x2</option>
                <option value="2x1">2x1</option>
                <option value="3x2">3x2</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Owner</label>
            <?php if (($_SESSION['role'] ?? '') === 'owner'): ?>
            <input type="text" class="form-control" value="Myself" disabled>
            <?php else: ?>
            <select name="owner_id" class="form-select">
                <option value="">None</option>
                <?php foreach ($owners as $o): ?>
                <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
        </div>
        <div class="col-md-4 d-flex align-items-center">
            <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" name="generate_seats" id="generate_seats" checked>
                <label class="form-check-label" for="generate_seats">Auto-generate seats</label>
            </div>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Create</button>
        </div>
    </form>
</div>
<?php require '../includes/footer.php'; ?>
<?php
require '../includes/header.php';
require '../includes/db.php';

$message = '';

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
	$generate_seats = isset($_POST['generate_seats']);

	// auto-assign owner if current user is owner
	$owner_id = null;
	if (isset($_SESSION['role']) && $_SESSION['role'] === 'owner') {
		$owner_id = (int)$_SESSION['user_id'];
	} else {
		$owner_id = isset($_POST['owner_id']) && $_POST['owner_id'] !== '' ? (int)$_POST['owner_id'] : null;
	}

	$ins = $pdo->prepare('INSERT INTO buses (owner_id, name, source, destination, travel_date, departure_time, arrival_time, bus_type, total_seats, available_seats, fare, seat_layout, deck_type) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
	$ins->execute([$owner_id, $name, $source, $destination, $travel_date, $departure_time, $arrival_time, $bus_type, $total_seats, $total_seats, $fare, $seat_layout, $deck_type]);
	$busId = (int)$pdo->lastInsertId();

	if ($generate_seats && $busId > 0) {
		$isSleeper = stripos($bus_type, 'Sleeper') !== false; // AC/Non-AC can be sleeper or seater
		$seatType = $isSleeper ? 'sleeper' : 'seater';
		$hasUpper = $deck_type === 'upper_and_lower';
		$insertSeat = $pdo->prepare('INSERT INTO seats (bus_id, seat_number, seat_type, deck, status) VALUES (?,?,?,?,"available")');
		if ($hasUpper) {
			$perDeck = (int)ceil($total_seats / 2);
			for ($i=1; $i<=$perDeck; $i++) { $insertSeat->execute([$busId, 'L'.$i, $seatType, 'lower']); }
			for ($i=1; $i<=$perDeck; $i++) { $insertSeat->execute([$busId, 'U'.$i, $seatType, 'upper']); }
		} else {
			for ($i=1; $i<=$total_seats; $i++) { $insertSeat->execute([$busId, ($seatType==='sleeper'?'L':'S').$i, $seatType, 'lower']); }
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
            <label class="form-label">Travel Date (optional)</label>
            <input type="date" class="form-control" name="travel_date">
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
                <option value="Non-AC Sleeper">Non-AC Sleeper</option>
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
        <div class="col-md-3">
            <label class="form-label">Deck</label>
            <select name="deck_type" class="form-select" required>
                <option value="lower_only">Lower Only</option>
                <option value="upper_and_lower">Upper & Lower</option>
            </select>
        </div>
        <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner'): ?>
        <div class="col-md-4">
            <label class="form-label">Owner</label>
            <select name="owner_id" class="form-select">
                <option value="">None</option>
                <?php foreach ($owners as $o): ?>
                <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
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
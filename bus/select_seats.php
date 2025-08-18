<?php
session_start();
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bus_booking";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['bus_id'])) {
    header("Location: index.php");
    exit();
}

$bus_id = $conn->real_escape_string($_GET['bus_id']);

// Cleanup expired locks: free seats back to available
$checkTable = $conn->query("SHOW TABLES LIKE 'seat_locks'");
if ($checkTable && $checkTable->num_rows > 0) {
    $expired = $conn->query("SELECT seat_id FROM seat_locks WHERE expires_at < NOW()");
    $expiredIds = [];
    if ($expired) {
        while ($row = $expired->fetch_assoc()) { $expiredIds[] = (int)$row['seat_id']; }
        if (!empty($expiredIds)) {
            $idList = implode(',', $expiredIds);
            $conn->query("DELETE FROM seat_locks WHERE seat_id IN ($idList)");
            $conn->query("UPDATE seats SET status = 'available' WHERE id IN ($idList) AND status <> 'booked'");
        }
    }
}

// Get bus details
$sql = "SELECT * FROM buses WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bus_id);
$stmt->execute();
$bus = $stmt->get_result()->fetch_assoc();

// Get seats
$sql = "SELECT * FROM seats WHERE bus_id = ? ORDER BY deck, seat_number";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bus_id);
$stmt->execute();
$seats = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Select Seats - <?php echo htmlspecialchars($bus['name']); ?></title>
    <?php include 'includes/header-links.php'; ?>
    <style>
        .seat-selection {
            padding: 40px 0;
            background: #f8f9fa;
        }
        .bus-layout {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .deck {
            margin-bottom: 30px;
        }
        .deck-title {
            margin-bottom: 20px;
            color: #192080;
            font-weight: 600;
        }
        .seats-grid {
            display: grid;
            gap: 10px;
            margin-bottom: 20px;
        }
        .sleeper-layout {
            grid-template-columns: repeat(3, 1fr);
        }
        .seater-layout {
            grid-template-columns: repeat(4, 1fr);
        }
        .seat {
            aspect-ratio: 1.5;
            border: 2px solid #192080;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fff;
        }
        .seat.available:hover {
            background: #e8f0fe;
        }
        .seat.selected {
            background: #192080;
            color: #fff;
        }
        .seat.booked {
            background: #dc3545;
            color: #fff;
            cursor: not-allowed;
            opacity: 0.7;
        }
        .seat.reserved {
            background: #ffc107;
            cursor: not-allowed;
        }
        .legend {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }
        .selected-seats {
            margin-top: 20px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .proceed-btn {
            background: #192080;
            color: #fff;
            padding: 10px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .proceed-btn:hover {
            background: #131860;
        }
        .proceed-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="back_re">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="title">
                        <h2>Select Your Seats</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="seat-selection">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <div class="bus-layout">
                        <?php if (isset($_SESSION['error'])) { echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>'; unset($_SESSION['error']); } ?>
                        <div class="legend">
                            <div class="legend-item">
                                <div class="legend-color" style="border: 2px solid #192080"></div>
                                <span>Available</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background: #192080"></div>
                                <span>Selected</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background: #dc3545"></div>
                                <span>Booked</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background: #ffc107"></div>
                                <span>Reserved</span>
                            </div>
                        </div>

                        <?php
                        $current_deck = '';
                        while ($seat = $seats->fetch_assoc()) {
                            if ($current_deck != $seat['deck']) {
                                if ($current_deck != '') {
                                    echo '</div>'; // Close previous seats-grid
                                }
                                $current_deck = $seat['deck'];
                                echo '<div class="deck">';
                                echo '<h4 class="deck-title">' . ucfirst($current_deck) . ' Deck</h4>';
                                echo '<div class="seats-grid ' . ($bus['bus_type'] == 'AC Sleeper' ? 'sleeper-layout' : 'seater-layout') . '">';
                            }
                            ?>
                            <div class="seat <?php echo $seat['status']; ?>" 
                                 data-seat-id="<?php echo $seat['id']; ?>"
                                 data-seat-number="<?php echo $seat['seat_number']; ?>"
                                 data-seat-type="<?php echo $seat['seat_type']; ?>">
                                <?php echo $seat['seat_number']; ?>
                            </div>
                            <?php
                        }
                        if ($current_deck != '') {
                            echo '</div></div>'; // Close last seats-grid and deck
                        }
                        ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="selected-seats">
                        <h4>Selected Seats</h4>
                        <div id="selected-seats-list">
                            <p>No seats selected</p>
                        </div>
                        <hr>
                        <div class="fare-details">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Base Fare (per seat)</span>
                                <span>₹<?php echo number_format($bus['fare'], 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Number of Seats</span>
                                <span id="seat-count">0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Fare</span>
                                <span id="total-fare">₹0.00</span>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <form action="booking.php" method="POST" id="booking-form">
                                <input type="hidden" name="bus_id" value="<?php echo $bus_id; ?>">
                                <input type="hidden" name="selected_seat_ids" id="selected-seat-ids-input">
                                <input type="hidden" name="selected_seat_numbers" id="selected-seat-numbers-input">
                                <button type="submit" class="proceed-btn" id="proceed-btn" disabled>
                                    Proceed to Book
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/footer-links.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectedSeatIdToNumber = new Map();
            const baseFare = <?php echo $bus['fare']; ?>;

            function renderSelectedList() {
                const list = document.getElementById('selected-seats-list');
                const count = document.getElementById('seat-count');
                const total = document.getElementById('total-fare');
                const proceedBtn = document.getElementById('proceed-btn');
                const idsInput = document.getElementById('selected-seat-ids-input');
                const numbersInput = document.getElementById('selected-seat-numbers-input');

                if (selectedSeatIdToNumber.size === 0) {
                    list.innerHTML = '<p>No seats selected</p>';
                    proceedBtn.disabled = true;
                } else {
                    const seatNumbers = Array.from(selectedSeatIdToNumber.values());
                    list.innerHTML = seatNumbers.map(n => `<div class="selected-seat-item">${n}</div>`).join('');
                    proceedBtn.disabled = false;
                }

                count.textContent = selectedSeatIdToNumber.size;
                total.textContent = '₹' + (selectedSeatIdToNumber.size * baseFare).toFixed(2);
                idsInput.value = Array.from(selectedSeatIdToNumber.keys()).join(',');
                numbersInput.value = Array.from(selectedSeatIdToNumber.values()).join(',');
            }

            function handleSeatClick(el) {
                const seatId = el.dataset.seatId;
                const seatNumber = el.dataset.seatNumber;
                const isSelected = el.classList.contains('selected');

                if (!isSelected) {
                    $.post('api/lock_seat.php', { seat_id: seatId, bus_id: <?php echo (int)$bus_id; ?> })
                        .done(function(resp) {
                            if (resp && resp.success) {
                                el.classList.add('selected');
                                selectedSeatIdToNumber.set(seatId, seatNumber);
                                renderSelectedList();
                            } else {
                                alert(resp && resp.message ? resp.message : 'Unable to lock seat.');
                                if (resp && resp.status) {
                                    el.classList.remove('available', 'selected', 'reserved', 'booked');
                                    el.classList.add(resp.status);
                                }
                            }
                        })
                        .fail(function() {
                            alert('Network error while locking seat.');
                        });
                } else {
                    $.post('api/unlock_seat.php', { seat_id: seatId })
                        .done(function(resp) {
                            if (resp && resp.success) {
                                el.classList.remove('selected');
                                selectedSeatIdToNumber.delete(seatId);
                                renderSelectedList();
                            } else {
                                alert(resp && resp.message ? resp.message : 'Unable to unlock seat.');
                            }
                        })
                        .fail(function() {
                            alert('Network error while unlocking seat.');
                        });
                }
            }

            document.querySelectorAll('.seat.available, .seat.reserved, .seat.booked').forEach(function(el) {
                if (!el.classList.contains('booked') && !el.classList.contains('reserved')) {
                    el.addEventListener('click', function() { handleSeatClick(el); });
                }
            });
        });
    </script>
</body>
</html> 
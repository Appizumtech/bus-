<?php
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
                                <input type="hidden" name="selected_seats" id="selected-seats-input">
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
            const selectedSeats = new Set();
            const baseFare = <?php echo $bus['fare']; ?>;
            
            function updateSelectedSeats() {
                const list = document.getElementById('selected-seats-list');
                const count = document.getElementById('seat-count');
                const total = document.getElementById('total-fare');
                const proceedBtn = document.getElementById('proceed-btn');
                const selectedSeatsInput = document.getElementById('selected-seats-input');
                
                if (selectedSeats.size === 0) {
                    list.innerHTML = '<p>No seats selected</p>';
                    proceedBtn.disabled = true;
                } else {
                    const seatsArray = Array.from(selectedSeats);
                    list.innerHTML = seatsArray.map(seatNumber => 
                        `<div class="selected-seat-item">${seatNumber}</div>`
                    ).join('');
                    proceedBtn.disabled = false;
                }
                
                count.textContent = selectedSeats.size;
                total.textContent = '₹' + (selectedSeats.size * baseFare).toFixed(2);
                selectedSeatsInput.value = Array.from(selectedSeats).join(',');
            }

            document.querySelectorAll('.seat.available').forEach(seat => {
                seat.addEventListener('click', function() {
                    const seatNumber = this.dataset.seatNumber;
                    
                    if (this.classList.contains('selected')) {
                        this.classList.remove('selected');
                        selectedSeats.delete(seatNumber);
                    } else {
                        this.classList.add('selected');
                        selectedSeats.add(seatNumber);
                    }
                    
                    updateSelectedSeats();
                });
            });
        });
    </script>
</body>
</html> 
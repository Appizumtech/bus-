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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <title>Search Results - Bus Booking</title>
    <?php include 'includes/header-links.php'; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="back_re">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="title">
                        <h2>Search Results</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="search-results py-5">
        <div class="container">
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $from = $conn->real_escape_string($_POST['from']);
                $to = $conn->real_escape_string($_POST['to']);
                $date = $conn->real_escape_string($_POST['date']);

                // Query to fetch matching buses
                $sql = "SELECT * FROM buses WHERE source = ? AND destination = ? AND travel_date = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $from, $to, $date);
                $stmt->execute();
                $result = $stmt->get_result();
                ?>
                
                <div class="search-summary mb-4">
                    <h4>Buses from <?php echo htmlspecialchars($from); ?> to <?php echo htmlspecialchars($to); ?></h4>
                    <p>Date: <?php echo htmlspecialchars($date); ?></p>
                </div>

                <div class="row">
                    <?php
                    if ($result->num_rows > 0) {
                        while($bus = $result->fetch_assoc()) {
                        ?>
                        <div class="col-md-6 mb-4">
                            <div class="card bus-card">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($bus['name']); ?></h5>
                                    <div class="bus-details">
                                        <div class="row">
                                            <div class="col-6">
                                                <p><i class="fa-solid fa-clock"></i> Departure: <?php echo htmlspecialchars($bus['departure_time']); ?></p>
                                                <p><i class="fa-solid fa-clock"></i> Arrival: <?php echo htmlspecialchars($bus['arrival_time']); ?></p>
                                            </div>
                                            <div class="col-6">
                                                <p><i class="fa-solid fa-users"></i> Available Seats: <?php echo htmlspecialchars($bus['available_seats']); ?></p>
                                                <p><i class="fa-solid fa-bus"></i> Type: <?php echo htmlspecialchars($bus['bus_type']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bus-rate mt-3">
                                        <div class="bs-price">
                                            <p>Rs.<?php echo htmlspecialchars($bus['fare']); ?>/-</p>
                                        </div>
                                        <div class="bk-btn">
                                            <a href="select_seats.php?bus_id=<?php echo htmlspecialchars($bus['id']); ?>" class="site-btn">Book Now</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        }
                    } else {
                        echo '<div class="col-12"><div class="alert alert-info">No buses found for the selected route and date. Please try different dates or routes.</div></div>';
                    }
                    ?>
                </div>
                <?php
                $stmt->close();
            } else {
                echo '<div class="alert alert-warning">No search parameters provided.</div>';
            }
            $conn->close();
            ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/footer-links.php'; ?>

    <style>
    .bus-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .bus-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .bus-details {
        margin: 15px 0;
    }
    .bus-details p {
        margin-bottom: 8px;
    }
    .bus-details i {
        margin-right: 8px;
        color: #192080;
    }
    .bus-rate {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #eee;
    }
    .bs-price p {
        font-size: 1.2em;
        font-weight: bold;
        color: #192080;
        margin: 0;
    }
    .search-summary {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 30px;
    }
    .search-summary h4 {
        color: #192080;
        margin-bottom: 5px;
    }
    .search-summary p {
        margin: 0;
        color: #666;
    }
    </style>
</body>
</html> 
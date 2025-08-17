<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <title>Book Your Ticket - Bus Booking</title>
    <?php include 'includes/header-links.php'; ?>
</head>
<body class="main-layout">
    <?php include 'includes/header.php'; ?>

    <div class="back_re">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="title">
                        <h2>Book Your Ticket</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="booking-section py-5">
        <div class="container">
            <?php
            if (isset($_GET['bus_id'])) {
                $bus_id = $_GET['bus_id'];
                // Here you would typically fetch bus details from database
                // For now using sample data
                $bus_details = [
                    'name' => 'Luxury Express',
                    'from' => 'Hazaribagh',
                    'to' => 'Ranchi',
                    'departure' => '06:00 AM',
                    'arrival' => '10:00 AM',
                    'price' => 1500,
                    'date' => date('Y-m-d')
                ];
            ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="booking-form-wrapper">
                        <div class="journey-details mb-4">
                            <h4>Journey Details</h4>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Bus:</strong> <?php echo htmlspecialchars($bus_details['name']); ?></p>
                                            <p><strong>From:</strong> <?php echo htmlspecialchars($bus_details['from']); ?></p>
                                            <p><strong>To:</strong> <?php echo htmlspecialchars($bus_details['to']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Date:</strong> <?php echo htmlspecialchars($bus_details['date']); ?></p>
                                            <p><strong>Departure:</strong> <?php echo htmlspecialchars($bus_details['departure']); ?></p>
                                            <p><strong>Arrival:</strong> <?php echo htmlspecialchars($bus_details['arrival']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form action="process_booking.php" method="POST" class="passenger-form">
                            <h4>Passenger Details</h4>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Full Name</label>
                                                <input type="text" class="form-control" name="passenger_name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Age</label>
                                                <input type="number" class="form-control" name="age" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Gender</label>
                                                <select class="form-control" name="gender" required>
                                                    <option value="">Select Gender</option>
                                                    <option value="male">Male</option>
                                                    <option value="female">Female</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Phone Number</label>
                                                <input type="tel" class="form-control" name="phone" required>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" class="form-control" name="email" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="bus_id" value="<?php echo htmlspecialchars($bus_id); ?>">
                            <input type="hidden" name="fare" value="<?php echo htmlspecialchars($bus_details['price']); ?>">
                            
                            <div class="text-right mt-4">
                                <button type="submit" class="btn btn-primary">Proceed to Payment</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="fare-summary">
                        <div class="card">
                            <div class="card-header">
                                <h5>Fare Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="fare-details">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Base Fare</span>
                                        <span>₹<?php echo htmlspecialchars($bus_details['price']); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tax</span>
                                        <span>₹<?php echo $bus_details['price'] * 0.05; ?></span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between total-fare">
                                        <strong>Total Amount</strong>
                                        <strong>₹<?php echo $bus_details['price'] + ($bus_details['price'] * 0.05); ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            } else {
                echo '<div class="alert alert-danger">Invalid booking request. Please select a bus first.</div>';
            }
            ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/footer-links.php'; ?>

    <style>
    .booking-section {
        background-color: #f8f9fa;
    }
    .booking-form-wrapper {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
    }
    .journey-details .card,
    .passenger-form .card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        font-weight: 500;
        margin-bottom: 5px;
        color: #333;
    }
    .fare-summary .card {
        position: sticky;
        top: 20px;
    }
    .fare-summary .card-header {
        background-color: #192080;
        color: white;
        padding: 15px;
    }
    .fare-summary .card-header h5 {
        margin: 0;
    }
    .total-fare {
        color: #192080;
        font-size: 1.1em;
    }
    .btn-primary {
        background-color: #192080;
        border-color: #192080;
        padding: 10px 30px;
    }
    .btn-primary:hover {
        background-color: #141A6A;
        border-color: #141A6A;
    }
    </style>
</body>
</html> 
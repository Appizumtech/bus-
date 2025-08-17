<?php
require 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Bus Booking Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="buses/index.php">Buses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookings/index.php">Bookings</a>
                    </li>
                    <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['super_admin','owner'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="users/index.php">Users</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="routes/index.php">Routes & Points</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">Settings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports/index.php">Reports</a>
                        </li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="subscriptions/index.php">Subscriptions</a>
                        </li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'agent'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="agent/book.php">Agent Booking</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <span class="navbar-text me-3">
                    <?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?>
                </span>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4"> 
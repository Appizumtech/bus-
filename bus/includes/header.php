<!-- header -->
<header>
    <div class="top-header">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="t-hdr">
                        <ul>
                            <li><i class="fa-solid fa-phone"></i> <a href="tel:+919431163344">+919431163344</a></li>
                            <li><i class="fa-solid fa-envelope"></i><a href="mailto:highwayhzb@gmail.com">highwayhzb@gmail.com</a></li>                                
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end align-items-center gap-3">
                        <p class="mb-0"><i class="fa-solid fa-location-dot"></i> New Forest Colony, Hazaribagh, Jharkhand 825301</p>
                        <?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a class="btn btn-sm btn-outline-light" href="logout.php">Logout</a>
                        <?php else: ?>
                            <a class="btn btn-sm btn-outline-light" href="login.php">Login</a>
                            <a class="btn btn-sm btn-primary" href="register.php">Sign Up</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- header inner -->
    <div class="header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-4 col-lg-3 col-md-3 col-sm-3 col logo_section">
                    <div class="full">
                        <div class="center-desk">
                            <div class="logo">
                                <a href="index.php"><img src="images/logo2.png" alt="#"></a>
                            </div>
                            <p>Highway Tour And Travel</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8 col-lg-9 col-md-9 col-sm-9">
                    <nav class="navigation navbar navbar-expand-md navbar-dark">
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample04" aria-controls="navbarsExample04" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarsExample04">
                            <ul class="navbar-nav mr-auto">
                                <li class="nav-item">
                                    <a class="nav-link" href="index.php">Home</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="about.php">About</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="bus.php">Our Bus</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="gallery.php">Gallery</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="contact.php">Contact</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="my_bookings.php">My Bookings</a>
                                </li>
                                <li><a href="#book-ticket" class="hdr-btn">Book Now</a></li>
                                <li><a href="" class="hdr-btn">Cancel Ticket</a></li>
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- end header inner --> 
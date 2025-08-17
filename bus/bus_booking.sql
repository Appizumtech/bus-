-- Create the database
CREATE DATABASE IF NOT EXISTS bus_booking;
USE bus_booking;

-- Create the buses table
CREATE TABLE IF NOT EXISTS buses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    source VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    travel_date DATE NOT NULL,
    departure_time TIME NOT NULL,
    arrival_time TIME NOT NULL,
    bus_type VARCHAR(50) NOT NULL,
    total_seats INT NOT NULL,
    available_seats INT NOT NULL,
    fare DECIMAL(10,2) NOT NULL,
    seat_layout VARCHAR(20) NOT NULL, -- 2x2, 2x1, etc.
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create seats table
CREATE TABLE IF NOT EXISTS seats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_id INT NOT NULL,
    seat_number VARCHAR(10) NOT NULL,
    seat_type ENUM('sleeper', 'seater') NOT NULL,
    deck ENUM('lower', 'upper') NOT NULL,
    status ENUM('available', 'booked', 'reserved') NOT NULL DEFAULT 'available',
    FOREIGN KEY (bus_id) REFERENCES buses(id),
    UNIQUE KEY unique_seat (bus_id, seat_number)
);

-- Insert sample data
INSERT INTO buses (name, source, destination, travel_date, departure_time, arrival_time, bus_type, total_seats, available_seats, fare, seat_layout) VALUES
('Luxury Express', 'Hazaribagh', 'Ranchi', '2024-03-16', '06:00:00', '10:00:00', 'AC Sleeper', 50, 50, 1500.00, '2x1'),
('Super Deluxe', 'Hazaribagh', 'Ranchi', '2024-03-16', '08:00:00', '12:00:00', 'AC Seater', 45, 45, 1800.00, '2x2'),
('Royal Cruiser', 'Hazaribagh', 'Patna', '2024-03-16', '20:00:00', '06:00:00', 'AC Sleeper', 40, 40, 2500.00, '2x1'),
('City Express', 'Hazaribagh', 'Dhanbad', '2024-03-16', '07:00:00', '11:00:00', 'Non-AC Seater', 56, 56, 800.00, '2x2'),
('Night Rider', 'Hazaribagh', 'Kolkata', '2024-03-16', '18:00:00', '08:00:00', 'AC Sleeper', 35, 35, 3000.00, '2x1');

-- Insert sample seats for Luxury Express (Bus ID 1)
INSERT INTO seats (bus_id, seat_number, seat_type, deck) VALUES
-- Lower Deck
(1, 'L1', 'sleeper', 'lower'),
(1, 'L2', 'sleeper', 'lower'),
(1, 'L3', 'sleeper', 'lower'),
(1, 'L4', 'sleeper', 'lower'),
(1, 'L5', 'sleeper', 'lower'),
(1, 'L6', 'sleeper', 'lower'),
(1, 'L7', 'sleeper', 'lower'),
(1, 'L8', 'sleeper', 'lower'),
(1, 'L9', 'sleeper', 'lower'),
(1, 'L10', 'sleeper', 'lower'),
-- Upper Deck
(1, 'U1', 'sleeper', 'upper'),
(1, 'U2', 'sleeper', 'upper'),
(1, 'U3', 'sleeper', 'upper'),
(1, 'U4', 'sleeper', 'upper'),
(1, 'U5', 'sleeper', 'upper'),
(1, 'U6', 'sleeper', 'upper'),
(1, 'U7', 'sleeper', 'upper'),
(1, 'U8', 'sleeper', 'upper'),
(1, 'U9', 'sleeper', 'upper'),
(1, 'U10', 'sleeper', 'upper'); 

-- Users table for roles (super_admin, owner, agent, customer)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin','owner','agent','customer') NOT NULL DEFAULT 'customer',
    assigned_owner_id INT NULL, -- for agents linked to an owner
    phone VARCHAR(30) NULL,
    company_name VARCHAR(150) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_owner_id) REFERENCES users(id)
);

-- Seed a default super admin (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Super Admin', 'admin@example.com', 'admin123', 'super_admin')
ON DUPLICATE KEY UPDATE email = email;

-- Extend buses with an optional owner link
ALTER TABLE buses ADD COLUMN IF NOT EXISTS owner_id INT NULL AFTER id;
ALTER TABLE buses ADD CONSTRAINT fk_buses_owner FOREIGN KEY (owner_id) REFERENCES users(id);

-- Bookings core table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_ref VARCHAR(32) NOT NULL UNIQUE,
    bus_id INT NOT NULL,
    user_id INT NULL, -- who made the booking (agent/customer)
    status ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
    amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    travel_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bus_id) REFERENCES buses(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Passengers table
CREATE TABLE IF NOT EXISTS passengers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    age INT NULL,
    gender ENUM('male','female','other') NULL,
    phone VARCHAR(30) NULL,
    email VARCHAR(150) NULL
);

-- Booking passengers (supports multiple passengers per booking)
CREATE TABLE IF NOT EXISTS booking_passengers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    passenger_id INT NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (passenger_id) REFERENCES passengers(id) ON DELETE CASCADE
);

-- Seats allocated for a booking
CREATE TABLE IF NOT EXISTS booking_seats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    seat_id INT NOT NULL,
    fare DECIMAL(10,2) NOT NULL,
    boarding_point_id INT NULL,
    dropping_point_id INT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (seat_id) REFERENCES seats(id) ON DELETE CASCADE
);

-- Route points per bus
CREATE TABLE IF NOT EXISTS boarding_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    time TIME NULL,
    address VARCHAR(255) NULL,
    FOREIGN KEY (bus_id) REFERENCES buses(id),
    UNIQUE KEY uniq_bp (bus_id, name)
);

CREATE TABLE IF NOT EXISTS dropping_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    time TIME NULL,
    address VARCHAR(255) NULL,
    FOREIGN KEY (bus_id) REFERENCES buses(id),
    UNIQUE KEY uniq_dp (bus_id, name)
);

-- Issued tickets for bookings
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_no VARCHAR(32) NOT NULL UNIQUE,
    booking_id INT NOT NULL,
    issued_by_user_id INT NULL,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by_user_id) REFERENCES users(id)
);

-- Admin settings (single row)
CREATE TABLE IF NOT EXISTS admin_settings (
    id TINYINT PRIMARY KEY CHECK (id = 1),
    per_ticket_charge DECIMAL(10,2) NOT NULL DEFAULT 0,
    subscription_yearly_amount DECIMAL(10,2) NOT NULL DEFAULT 0
);

INSERT INTO admin_settings (id, per_ticket_charge, subscription_yearly_amount)
VALUES (1, 0, 0)
ON DUPLICATE KEY UPDATE id = id;

-- Owner subscriptions
CREATE TABLE IF NOT EXISTS subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    plan_name VARCHAR(100) NOT NULL DEFAULT 'Yearly',
    amount DECIMAL(10,2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active','expired','cancelled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id)
); 

-- Allow buses without a fixed travel date
ALTER TABLE buses MODIFY travel_date DATE NULL;

-- Deck type: single or double deck
ALTER TABLE buses ADD COLUMN IF NOT EXISTS deck_type ENUM('lower_only','upper_and_lower') NOT NULL DEFAULT 'lower_only' AFTER seat_layout; 
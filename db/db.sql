-- UniRide database schema
-- Data: 28-01-2026
--
--
--
-- Account demo inseriti automaticamente dall'app (seed):
-- - sofia.rossi@unibo.test, marco.bianchi@unibo.test, giulia.conti@unibo.test, luca.ferretti@unibo.test / Password123!
-- - admin@unibo.test / Password123!


CREATE DATABASE IF NOT EXISTS uniride CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE uniride;

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS users (
	id INT AUTO_INCREMENT PRIMARY KEY,
	email VARCHAR(255) NOT NULL,
	password_hash VARCHAR(255) NOT NULL,
	full_name VARCHAR(255) NOT NULL,
	university VARCHAR(255) NOT NULL,
	role VARCHAR(20) NOT NULL DEFAULT 'user',
	phone VARCHAR(30) NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	UNIQUE KEY uniq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rides (
	id INT AUTO_INCREMENT PRIMARY KEY,
	driver_id INT NOT NULL,
	from_city VARCHAR(255) NOT NULL,
	to_city VARCHAR(255) NOT NULL,
	depart_at DATETIME NOT NULL,
	seats_total INT NOT NULL,
	seats_available INT NOT NULL,
	notes TEXT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_rides_driver FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE,
	INDEX idx_rides_depart_at (depart_at),
	INDEX idx_rides_route (from_city, to_city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ride_requests (
	id INT AUTO_INCREMENT PRIMARY KEY,
	ride_id INT NOT NULL,
	passenger_id INT NOT NULL,
	status ENUM('pending','accepted','rejected','canceled') NOT NULL,
	message TEXT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	UNIQUE KEY uniq_request (ride_id, passenger_id),
	CONSTRAINT fk_req_ride FOREIGN KEY (ride_id) REFERENCES rides(id) ON DELETE CASCADE,
	CONSTRAINT fk_req_passenger FOREIGN KEY (passenger_id) REFERENCES users(id) ON DELETE CASCADE,
	INDEX idx_requests_status (status),
	INDEX idx_requests_ride (ride_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ride_stops (
	id INT AUTO_INCREMENT PRIMARY KEY,
	ride_id INT NOT NULL,
	stop_order INT NOT NULL,
	stop_city VARCHAR(255) NOT NULL,
	created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	UNIQUE KEY uniq_stop_order (ride_id, stop_order),
	UNIQUE KEY uniq_stop_city (ride_id, stop_city),
	CONSTRAINT fk_stops_ride FOREIGN KEY (ride_id) REFERENCES rides(id) ON DELETE CASCADE,
	INDEX idx_stops_ride (ride_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


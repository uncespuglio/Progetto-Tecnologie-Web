<?php

declare(strict_types=1);

function db(): PDO
{
	static $pdo = null;
	if ($pdo instanceof PDO) {
		return $pdo;
	}

	$host = getenv('DB_HOST') ?: 'localhost';
	$port = getenv('DB_PORT') ?: '3306';
	$dbName = getenv('DB_NAME') ?: 'uniride';
	$user = getenv('DB_USER') ?: 'root';
	$pass = getenv('DB_PASS') ?: '';
	$charset = getenv('DB_CHARSET') ?: 'utf8mb4';
	$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $dbName, $charset);

	try {
		$pdo = new PDO(
			$dsn,
			$user,
			$pass,
			[
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES => false,
			]
		);
	} catch (PDOException $e) {
		http_response_code(500);
		$msg = 'Connessione MySQL fallita. Controlla DB_HOST/DB_PORT/DB_NAME/DB_USER/DB_PASS.';
		$msg .= "\nTentativo: user={$user}, db={$dbName}, host={$host}, port={$port}";
		$detail = 'Errore: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		echo $msg . "\n" . $detail;
		exit;
	}

	return $pdo;
}

function ensure_schema(PDO $pdo): void
{
	$pdo->exec('CREATE TABLE IF NOT EXISTS users (
		id INT AUTO_INCREMENT PRIMARY KEY,
		email VARCHAR(255) NOT NULL UNIQUE,
		password_hash VARCHAR(255) NOT NULL,
		full_name VARCHAR(255) NOT NULL,
		university VARCHAR(255) NOT NULL,
		role VARCHAR(20) NOT NULL DEFAULT \'user\',
		phone VARCHAR(30) NULL,
		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

	$pdo->exec('CREATE TABLE IF NOT EXISTS rides (
		id INT AUTO_INCREMENT PRIMARY KEY,
		driver_id INT NOT NULL,
		from_city VARCHAR(255) NOT NULL,
		to_city VARCHAR(255) NOT NULL,
		depart_at DATETIME NOT NULL,
		seats_total INT NOT NULL,
		seats_available INT NOT NULL,
		price_cents INT NOT NULL DEFAULT 0,
		notes TEXT NULL,
		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		CONSTRAINT fk_rides_driver FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

	$pdo->exec('CREATE TABLE IF NOT EXISTS ride_requests (
		id INT AUTO_INCREMENT PRIMARY KEY,
		ride_id INT NOT NULL,
		passenger_id INT NOT NULL,
		status ENUM(\'pending\',\'accepted\',\'rejected\',\'canceled\') NOT NULL,
		message TEXT NULL,
		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		UNIQUE KEY uniq_request (ride_id, passenger_id),
		CONSTRAINT fk_req_ride FOREIGN KEY (ride_id) REFERENCES rides(id) ON DELETE CASCADE,
		CONSTRAINT fk_req_passenger FOREIGN KEY (passenger_id) REFERENCES users(id) ON DELETE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

	$pdo->exec('CREATE TABLE IF NOT EXISTS ride_stops (
		id INT AUTO_INCREMENT PRIMARY KEY,
		ride_id INT NOT NULL,
		stop_order INT NOT NULL,
		stop_city VARCHAR(255) NOT NULL,
		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		UNIQUE KEY uniq_stop_order (ride_id, stop_order),
		UNIQUE KEY uniq_stop_city (ride_id, stop_city),
		CONSTRAINT fk_stops_ride FOREIGN KEY (ride_id) REFERENCES rides(id) ON DELETE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

	if (!mysql_column_exists($pdo, 'users', 'role')) {
		$pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user'");
	}
	if (!mysql_column_exists($pdo, 'users', 'phone')) {
		$pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(30) NULL");
	}
}

function mysql_column_exists(PDO $pdo, string $table, string $column): bool
{
	$dbName = (string)$pdo->query('SELECT DATABASE()')->fetchColumn();
	if ($dbName === '') {
		return true;
	}
	$stmt = $pdo->prepare(
		'SELECT COUNT(*)
		 FROM information_schema.COLUMNS
		 WHERE TABLE_SCHEMA = :db
		 AND TABLE_NAME = :t
		 AND COLUMN_NAME = :c'
	);
	$stmt->execute([':db' => $dbName, ':t' => $table, ':c' => $column]);
	return (int)$stmt->fetchColumn() > 0;
}


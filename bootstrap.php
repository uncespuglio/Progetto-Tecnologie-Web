<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);

function load_dotenv(string $path): void
{
	if (!is_file($path) || !is_readable($path)) {
		return;
	}
	$lines = file($path, FILE_IGNORE_NEW_LINES);
	if (!is_array($lines)) {
		return;
	}
	foreach ($lines as $line) {
		$line = trim($line);
		if ($line === '' || str_starts_with($line, '#')) {
			continue;
		}
		$pos = strpos($line, '=');
		if ($pos === false) {
			continue;
		}
		$key = trim(substr($line, 0, $pos));
		$val = trim(substr($line, $pos + 1));
		if ($key === '') {
			continue;
		}
		if ((str_starts_with($val, '"') && str_ends_with($val, '"')) || (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
			$val = substr($val, 1, -1);
		}
		if (getenv($key) !== false) {
			continue;
		}
		putenv($key . '=' . $val);
		$_ENV[$key] = $val;
		$_SERVER[$key] = $val;
	}
}

load_dotenv(BASE_PATH . '/.env');

ini_set('display_errors', '1');
error_reporting(E_ALL);

ini_set('session.use_strict_mode', '1');

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
	'lifetime' => 0,
	'path' => '/',
	'httponly' => true,
	'samesite' => 'Lax',
	'secure' => $isHttps,
]);
session_start();

require_once BASE_PATH . '/db/database.php';

$pdo = db();
ensure_schema($pdo);
seed_demo_data($pdo);
csrf_token();

function unibo_university(): string
{
	return 'Università di Bologna (UNIBO)';
}

function allowed_cities(): array
{
	return ['Bologna', 'Forlì', 'Cesena', 'Ravenna', 'Rimini'];
}

function normalize_city(string $city): ?string
{
	$city = trim($city);
	if ($city === '') {
		return null;
	}
	$k = mb_strtolower($city);
	$map = [
		'bologna' => 'Bologna',
		'forli' => 'Forlì',
		'forlì' => 'Forlì',
		'cesena' => 'Cesena',
		'ravenna' => 'Ravenna',
		'rimini' => 'Rimini',
	];
	return $map[$k] ?? null;
}

function maps_station_for_city(string $city): string
{
	$city = normalize_city($city) ?? $city;
	$map = [
		'Bologna' => 'Stazione Bologna Centrale',
		'Forlì' => 'Stazione di Forlì',
		'Cesena' => 'Stazione di Cesena',
		'Ravenna' => 'Stazione di Ravenna',
		'Rimini' => 'Stazione di Rimini',
	];
	return $map[$city] ?? ('Stazione di ' . trim($city));
}

function maps_unibo_campus_for_city(string $city): string
{
	$city = normalize_city($city) ?? $city;
	$map = [
		// Nota: usiamo query testuali stabili (nome campus) per Google Maps.
		'Bologna' => 'Università di Bologna - Via Zamboni 33',
		'Forlì' => 'Campus di Forlì - Università di Bologna',
		'Cesena' => 'Nuovo Campus Universitario Cesena - Università di Bologna',
		'Ravenna' => 'Campus di Ravenna - Università di Bologna',
		'Rimini' => 'Campus di Rimini - Università di Bologna',
	];
	return $map[$city] ?? ('Università di Bologna ' . trim($city));
}

function maps_place_for_city(string $city, string $mode = 'campus'): string
{
	return ($mode === 'station') ? maps_station_for_city($city) : maps_unibo_campus_for_city($city);
}

function google_maps_directions_url(string $fromCity, string $toCity, array $stops = [], string $mode = 'campus'): string
{
	$origin = maps_place_for_city($fromCity, $mode);
	$destination = maps_place_for_city($toCity, $mode);

	$waypoints = [];
	foreach ($stops as $s) {
		$city = normalize_city((string)$s);
		if ($city === null) {
			continue;
		}
		if ($city === normalize_city($fromCity) || $city === normalize_city($toCity)) {
			continue;
		}
		$place = maps_place_for_city($city, $mode);
		if (!in_array($place, $waypoints, true)) {
			$waypoints[] = $place;
		}
	}

	$params = [
		'api' => '1',
		'origin' => $origin,
		'destination' => $destination,
		'travelmode' => 'driving',
	];
	if ($waypoints) {
		$params['waypoints'] = implode('|', $waypoints);
	}

	return 'https://www.google.com/maps/dir/?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
}

function is_unibo_email(string $email): bool
{
	$email = mb_strtolower(trim($email));
	if ($email === '') {
		return false;
	}
	$allowedDomains = [
		'@studio.unibo.it',
		'@unibo.test',
	];
	foreach ($allowedDomains as $d) {
		if (str_ends_with($email, $d)) {
			return true;
		}
	}
	return false;
}

function e(?string $value): string
{
	return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_post(): bool
{
	return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function redirect(string $to): never
{
	header('Location: ' . $to);
	exit;
}

function url(string $path = ''): string
{
	$base = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
	$script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
	return $script . $path;
}

function flash(string $key, ?string $message = null): ?string
{
	if ($message !== null) {
		$_SESSION['_flash'][$key] = $message;
		return null;
	}

	$message = $_SESSION['_flash'][$key] ?? null;
	unset($_SESSION['_flash'][$key]);
	return $message;
}

function csrf_token(): string
{
	if (empty($_SESSION['_csrf'])) {
		$_SESSION['_csrf'] = bin2hex(random_bytes(16));
	}
	return (string)$_SESSION['_csrf'];
}

function csrf_field(): string
{
	return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
	if (!is_post()) {
		return;
	}
	$sent = $_POST['_csrf'] ?? '';
	$valid = $_SESSION['_csrf'] ?? '';
	if (!is_string($sent) || !hash_equals((string)$valid, $sent)) {
		http_response_code(419);
		echo 'CSRF token non valido. Suggerimenti: usa sempre lo stesso host (solo localhost oppure solo 127.0.0.1), abilita i cookie, e ricarica la pagina del form prima di reinviare.';
		exit;
	}
}

function seed_demo_data(PDO $pdo): void
{
	$demoPassword = 'Password123!';
	$hash = password_hash($demoPassword, PASSWORD_DEFAULT);

	$accounts = [
		[
			'email' => 'admin@unibo.test',
			'full_name' => 'Admin UNIBO',
			'role' => 'admin',
			'phone' => null,
		],
		[
			'email' => 'sofia.rossi@unibo.test',
			'full_name' => 'Sofia Rossi',
			'role' => 'user',
			'phone' => '+39 333 000 0001',
		],
		[
			'email' => 'marco.bianchi@unibo.test',
			'full_name' => 'Marco Bianchi',
			'role' => 'user',
			'phone' => '+39 333 000 0002',
		],
		[
			'email' => 'giulia.conti@unibo.test',
			'full_name' => 'Giulia Conti',
			'role' => 'user',
			'phone' => '+39 333 000 0003',
		],
		[
			'email' => 'luca.ferretti@unibo.test',
			'full_name' => 'Luca Ferretti',
			'role' => 'user',
			'phone' => '+39 333 000 0004',
		],
	];

	$uni = unibo_university();
	$stmtUpsert = $pdo->prepare(
		"INSERT INTO users (email, password_hash, full_name, university, role, phone)
		 VALUES (:email, :hash, :name, :uni, :role, :phone)
		 ON DUPLICATE KEY UPDATE
			password_hash = VALUES(password_hash),
			full_name = VALUES(full_name),
			university = VALUES(university),
			role = VALUES(role),
			phone = VALUES(phone)"
	);
	foreach ($accounts as $a) {
		try {
			$stmtUpsert->execute([
				':email' => $a['email'],
				':hash' => $hash,
				':name' => $a['full_name'],
				':uni' => $uni,
				':role' => $a['role'],
				':phone' => $a['phone'],
			]);
		} catch (Throwable $e) {
			// Non bloccare l'app se il seed fallisce.
		}
	}

	try {
		$pdo->prepare('UPDATE users SET university = :u WHERE email IN (:e1, :e2)')
			->execute([':u' => unibo_university(), ':e1' => 'user@uniride.test', ':e2' => 'admin@uniride.test']);
	} catch (Throwable $e) {
	}

	try {
		$ridesCount = (int)$pdo->query('SELECT COUNT(*) FROM rides')->fetchColumn();
		$stopsCount = (int)$pdo->query('SELECT COUNT(*) FROM ride_stops')->fetchColumn();
		if ($ridesCount > 0 && $stopsCount > 0) {
			return;
		}
		if ($ridesCount > 0 && $stopsCount === 0) {
			$insertStop = $pdo->prepare('INSERT INTO ride_stops (ride_id, stop_order, stop_city) VALUES (:ride, :ord, :city)');
			$stmt = $pdo->prepare("SELECT id FROM rides WHERE from_city = 'Bologna' AND to_city = 'Rimini' ORDER BY depart_at ASC LIMIT 1");
			$stmt->execute();
			$rideId = (int)$stmt->fetchColumn();
			if ($rideId > 0) {
				try { $insertStop->execute([':ride' => $rideId, ':ord' => 1, ':city' => 'Forlì']); } catch (Throwable $e) {}
				try { $insertStop->execute([':ride' => $rideId, ':ord' => 2, ':city' => 'Cesena']); } catch (Throwable $e) {}
			}
			$stmt = $pdo->prepare("SELECT id FROM rides WHERE from_city = 'Bologna' AND to_city = 'Cesena' ORDER BY depart_at ASC LIMIT 1");
			$stmt->execute();
			$rideId = (int)$stmt->fetchColumn();
			if ($rideId > 0) {
				try { $insertStop->execute([':ride' => $rideId, ':ord' => 1, ':city' => 'Forlì']); } catch (Throwable $e) {}
			}
			return;
		}
	} catch (Throwable $e) {
		return;
	}

	$userIds = [];
	foreach ($accounts as $a) {
		try {
			$stmt = $pdo->prepare('SELECT id FROM users WHERE email = :e');
			$stmt->execute([':e' => $a['email']]);
			$userIds[$a['email']] = (int)$stmt->fetchColumn();
		} catch (Throwable $e) {
			$userIds[$a['email']] = 0;
		}
	}

	$baseDate = date('Y-m-d');
	$rides = [
		['driver' => 'sofia.rossi@unibo.test', 'from' => 'Bologna', 'to' => 'Rimini', 'days' => 1, 'time' => '08:10:00', 'seats' => 3, 'price' => 500, 'notes' => 'Ritrovo: Stazione Bologna Centrale.', 'stops' => ['Forlì', 'Cesena']],
		['driver' => 'marco.bianchi@unibo.test', 'from' => 'Rimini', 'to' => 'Bologna', 'days' => 1, 'time' => '17:40:00', 'seats' => 2, 'price' => 600, 'notes' => 'Solo zaino, no valigie grandi.'],
		['driver' => 'giulia.conti@unibo.test', 'from' => 'Bologna', 'to' => 'Cesena', 'days' => 2, 'time' => '07:30:00', 'seats' => 3, 'price' => 450, 'notes' => null, 'stops' => ['Forlì']],
		['driver' => 'luca.ferretti@unibo.test', 'from' => 'Cesena', 'to' => 'Bologna', 'days' => 2, 'time' => '18:15:00', 'seats' => 3, 'price' => 450, 'notes' => null],
		['driver' => 'sofia.rossi@unibo.test', 'from' => 'Forlì', 'to' => 'Bologna', 'days' => 3, 'time' => '08:00:00', 'seats' => 2, 'price' => 400, 'notes' => 'Passo vicino al Campus di Forlì.'],
		['driver' => 'marco.bianchi@unibo.test', 'from' => 'Bologna', 'to' => 'Ravenna', 'days' => 3, 'time' => '16:20:00', 'seats' => 3, 'price' => 550, 'notes' => null, 'stops' => ['Rimini']],
		['driver' => 'giulia.conti@unibo.test', 'from' => 'Ravenna', 'to' => 'Bologna', 'days' => 4, 'time' => '07:45:00', 'seats' => 3, 'price' => 550, 'notes' => 'Ritrovo: Stazione Ravenna.'],
		['driver' => 'luca.ferretti@unibo.test', 'from' => 'Bologna', 'to' => 'Forlì', 'days' => 4, 'time' => '18:05:00', 'seats' => 2, 'price' => 400, 'notes' => null],
		['driver' => 'sofia.rossi@unibo.test', 'from' => 'Bologna', 'to' => 'Rimini', 'days' => 5, 'time' => '09:05:00', 'seats' => 3, 'price' => 500, 'notes' => 'Partenza zona Fiera.', 'stops' => ['Cesena']],
		['driver' => 'giulia.conti@unibo.test', 'from' => 'Rimini', 'to' => 'Cesena', 'days' => 5, 'time' => '19:10:00', 'seats' => 2, 'price' => 350, 'notes' => null],
	];

	try {
		$stmtRide = $pdo->prepare(
			'INSERT INTO rides (driver_id, from_city, to_city, depart_at, seats_total, seats_available, price_cents, notes)
			 VALUES (:driver, :from, :to, :depart, :st, :sa, :price, :notes)'
		);
		$stmtStop = $pdo->prepare(
			'INSERT INTO ride_stops (ride_id, stop_order, stop_city) VALUES (:ride, :ord, :city)'
		);
		foreach ($rides as $r) {
			$driverId = (int)($userIds[$r['driver']] ?? 0);
			if ($driverId <= 0) {
				continue;
			}
			$depart = date('Y-m-d', strtotime($baseDate . ' +' . (int)$r['days'] . ' day')) . ' ' . $r['time'];
			$stmtRide->execute([
				':driver' => $driverId,
				':from' => $r['from'],
				':to' => $r['to'],
				':depart' => $depart,
				':st' => (int)$r['seats'],
				':sa' => (int)$r['seats'],
				':price' => (int)$r['price'],
				':notes' => $r['notes'],
			]);
			$rideId = (int)$pdo->lastInsertId();
			if ($rideId > 0 && !empty($r['stops']) && is_array($r['stops'])) {
				$ord = 1;
				foreach ($r['stops'] as $c) {
					$city = normalize_city((string)$c);
					if ($city === null) {
						continue;
					}
					$stmtStop->execute([':ride' => $rideId, ':ord' => $ord, ':city' => $city]);
					$ord++;
				}
			}
		}
	} catch (Throwable $e) {
	}
}

function current_user(PDO $pdo): ?array
{
	$userId = $_SESSION['user_id'] ?? null;
	if (!is_int($userId) && !ctype_digit((string)$userId)) {
		return null;
	}
	$stmt = $pdo->prepare('SELECT id, email, full_name, university, role, phone FROM users WHERE id = :id');
	$stmt->execute([':id' => (int)$userId]);
	$user = $stmt->fetch(PDO::FETCH_ASSOC);
	return $user ?: null;
}

function is_admin(?array $user): bool
{
	return $user !== null && ((string)($user['role'] ?? 'user') === 'admin');
}

function require_admin(PDO $pdo): void
{
	$user = current_user($pdo);
	if (!is_admin($user)) {
		http_response_code(403);
		flash('error', 'Accesso negato.');
		redirect(url('?p=home'));
	}
}

function require_login(PDO $pdo): void
{
	if (current_user($pdo) === null) {
		flash('error', 'Devi effettuare il login per continuare.');
		redirect(url('?p=login'));
	}
}

function render(string $view, array $data = []): void
{
	extract($data, EXTR_SKIP);
	$viewFile = BASE_PATH . '/template/' . ltrim($view, '/');
	if (!is_file($viewFile)) {
		http_response_code(500);
		echo 'Vista non trovata: ' . e($view);
		return;
	}

	ob_start();
	require $viewFile;
	$content = ob_get_clean();
	require BASE_PATH . '/template/layout.php';
}


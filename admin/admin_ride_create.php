<?php

declare(strict_types=1);

require_admin($pdo);

$users = $pdo->query("SELECT id, full_name, email FROM users ORDER BY full_name ASC, email ASC")->fetchAll();

$ride = [
	'driver_id' => 0,
	'from_city' => '',
	'to_city' => '',
	'depart_at' => '',
	'seats_total' => 3,
	'seats_available' => 3,
	'notes' => '',
];

$stops = [];
$departLocal = '';

if (is_post()) {
	verify_csrf();

	$driver_id = (int)($_POST['driver_id'] ?? 0);
	$fromRaw = trim((string)($_POST['from_city'] ?? ''));
	$toRaw = trim((string)($_POST['to_city'] ?? ''));
	$depart_raw = trim((string)($_POST['depart_at'] ?? ''));
	$seats_total = (int)($_POST['seats_total'] ?? 0);
	$seats_available = (int)($_POST['seats_available'] ?? 0);
	$notes = trim((string)($_POST['notes'] ?? ''));
	$stopsRaw = $_POST['stops'] ?? [];

	$from = normalize_city($fromRaw) ?? '';
	$to = normalize_city($toRaw) ?? '';
	$stopsNew = [];
	if (is_array($stopsRaw)) {
		foreach ($stopsRaw as $s) {
			$city = normalize_city((string)$s);
			if ($city === null) {
				continue;
			}
			if (!in_array($city, $stopsNew, true)) {
				$stopsNew[] = $city;
			}
		}
	}

	$depart_at = str_replace('T', ' ', $depart_raw);

	$errors = [];
	if ($driver_id <= 0) $errors[] = 'Seleziona un conducente.';
	if ($from === '' || $to === '') $errors[] = 'Partenza e destinazione sono obbligatorie.';
	if ($from !== '' && $to !== '' && $from === $to) $errors[] = 'Partenza e destinazione devono essere diverse.';
	foreach ($stopsNew as $s) {
		if ($s === $from || $s === $to) $errors[] = 'Le tappe non possono coincidere con partenza/arrivo.';
	}
	if ($depart_raw === '') $errors[] = 'Data/ora sono obbligatorie.';
	if ($seats_total <= 0) $errors[] = 'Posti totali non validi.';
	if ($seats_available < 0 || $seats_available > $seats_total) $errors[] = 'Posti disponibili non validi.';

	if (!$errors) {
		$pdo->beginTransaction();
		try {
			$stmt = $pdo->prepare(
				"INSERT INTO rides (driver_id, from_city, to_city, depart_at, seats_total, seats_available, notes)
				 VALUES (?, ?, ?, ?, ?, ?, ?)"
			);
			$stmt->execute([
				$driver_id,
				$from,
				$to,
				$depart_at,
				$seats_total,
				$seats_available,
				$notes === '' ? null : $notes,
			]);
			$rideId = (int)$pdo->lastInsertId();

			if ($rideId > 0) {
				$pdo->prepare('DELETE FROM ride_stops WHERE ride_id = ?')->execute([$rideId]);
				if ($stopsNew) {
					$stmtStop = $pdo->prepare('INSERT INTO ride_stops (ride_id, stop_order, stop_city) VALUES (:ride, :ord, :city)');
					$ord = 1;
					foreach ($stopsNew as $city) {
						$stmtStop->execute([':ride' => $rideId, ':ord' => $ord, ':city' => $city]);
						$ord++;
					}
				}
			}

			$pdo->commit();
			flash('ok', 'Passaggio creato.');
			redirect(url('?p=admin_rides'));
		} catch (Throwable $e) {
			$pdo->rollBack();
			flash('error', 'Errore creazione passaggio.');
		}
	}

	flash('error', implode(' ', $errors));

	$ride['driver_id'] = $driver_id;
	$ride['from_city'] = $from;
	$ride['to_city'] = $to;
	$ride['depart_at'] = $depart_at;
	$ride['seats_total'] = $seats_total;
	$ride['seats_available'] = $seats_available;
	$ride['notes'] = $notes;
	$stops = $stopsNew;
	$departLocal = $depart_raw;
}

render('admin/admin_ride_create.php', [
	'ride' => $ride,
	'users' => $users,
	'departLocal' => $departLocal,
	'allowedCities' => allowed_cities(),
	'stops' => $stops,
]);

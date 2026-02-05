<?php

require_login($pdo);

$user = current_user($pdo);

if (is_post()) {
	verify_csrf();

	$fromRaw = trim((string)($_POST['from_city'] ?? ''));
	$toRaw = trim((string)($_POST['to_city'] ?? ''));
	$departRaw = trim((string)($_POST['depart_at'] ?? ''));
	$seatsTotal = (int)($_POST['seats_total'] ?? 0);
	$notes = trim((string)($_POST['notes'] ?? ''));
	$stopsRaw = $_POST['stops'] ?? [];

	$from = normalize_city($fromRaw) ?? '';
	$to = normalize_city($toRaw) ?? '';
	$stops = [];
	if (is_array($stopsRaw)) {
		foreach ($stopsRaw as $s) {
			$city = normalize_city((string)$s);
			if ($city === null) {
				continue;
			}
			if (!in_array($city, $stops, true)) {
				$stops[] = $city;
			}
		}
	}

	if ($from === '' || $to === '' || $departRaw === '' || $seatsTotal <= 0) {
		flash('error', 'Compila tratta, data/ora e posti.');
		redirect(url('?p=ride_create'));
	}
	if ($from === $to) {
		flash('error', 'Partenza e arrivo devono essere diversi.');
		redirect(url('?p=ride_create'));
	}
	foreach ($stops as $s) {
		if ($s === $from || $s === $to) {
			flash('error', 'Le tappe non possono coincidere con partenza/arrivo.');
			redirect(url('?p=ride_create'));
		}
	}

	$departAt = str_replace('T', ' ', $departRaw);

	$pdo->beginTransaction();
	try {
		$stmt = $pdo->prepare(
			'INSERT INTO rides (driver_id, from_city, to_city, depart_at, seats_total, seats_available, notes)
			 VALUES (:driver, :from, :to, :depart, :st, :sa, :notes)'
		);
		$stmt->execute([
		':driver' => (int)$user['id'],
		':from' => $from,
		':to' => $to,
		':depart' => $departAt,
		':st' => $seatsTotal,
		':sa' => $seatsTotal,
		':notes' => $notes === '' ? null : $notes,
		]);
		$rideId = (int)$pdo->lastInsertId();
		if ($rideId > 0 && $stops) {
			$stmtStop = $pdo->prepare('INSERT INTO ride_stops (ride_id, stop_order, stop_city) VALUES (:ride, :ord, :city)');
			$ord = 1;
			foreach ($stops as $city) {
				$stmtStop->execute([':ride' => $rideId, ':ord' => $ord, ':city' => $city]);
				$ord++;
			}
		}
		$pdo->commit();
	} catch (Throwable $e) {
		$pdo->rollBack();
		flash('error', 'Errore pubblicazione passaggio.');
		redirect(url('?p=ride_create'));
	}
	flash('ok', 'Passaggio pubblicato.');
	redirect(url('?p=ride&id=' . $rideId));
}
render('user/ride_create.php', [
	'allowedCities' => allowed_cities(),
]);

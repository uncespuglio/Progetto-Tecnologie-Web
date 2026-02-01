<?php

require_admin($pdo);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
	flash('error', 'ID passaggio non valido.');
	redirect(url('?p=admin_rides'));
}

$ride = $pdo->prepare(
	"SELECT r.*, u.full_name AS driver_name, u.email AS driver_email
	 FROM rides r
	 JOIN users u ON u.id = r.driver_id
	 WHERE r.id = ?"
);
$ride->execute([$id]);
$ride = $ride->fetch();
if (!$ride) {
	flash('error', 'Passaggio non trovato.');
	redirect(url('?p=admin_rides'));
}

$users = $pdo->query("SELECT id, full_name, email FROM users ORDER BY full_name ASC, email ASC")->fetchAll();

$stmt = $pdo->prepare('SELECT stop_city FROM ride_stops WHERE ride_id = ? ORDER BY stop_order ASC');
$stmt->execute([$id]);
$stops = array_map(static fn($row) => (string)$row['stop_city'], $stmt->fetchAll());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verify_csrf();

	$driver_id = (int)($_POST['driver_id'] ?? 0);
	$fromRaw = trim((string)($_POST['from_city'] ?? ''));
	$toRaw = trim((string)($_POST['to_city'] ?? ''));
	$depart_raw = trim((string)($_POST['depart_at'] ?? ''));
	$seats_total = (int)($_POST['seats_total'] ?? 0);
	$seats_available = (int)($_POST['seats_available'] ?? 0);
	$price_eur = trim((string)($_POST['price_eur'] ?? '0'));
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

	$price_cents = (int)round(((float)str_replace(',', '.', $price_eur)) * 100);

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
	if ($price_cents < 0) $errors[] = 'Prezzo non valido.';

	if (!$errors) {
		$pdo->beginTransaction();
		try {
			$stmt = $pdo->prepare(
				"UPDATE rides
				 SET driver_id = ?, from_city = ?, to_city = ?, depart_at = ?,
					 seats_total = ?, seats_available = ?, price_cents = ?, notes = ?
				 WHERE id = ?"
			);
			$stmt->execute([
				$driver_id,
				$from,
				$to,
				$depart_at,
				$seats_total,
				$seats_available,
				$price_cents,
				$notes,
				$id,
			]);

			$pdo->prepare('DELETE FROM ride_stops WHERE ride_id = ?')->execute([$id]);
			if ($stopsNew) {
				$stmtStop = $pdo->prepare('INSERT INTO ride_stops (ride_id, stop_order, stop_city) VALUES (:ride, :ord, :city)');
				$ord = 1;
				foreach ($stopsNew as $city) {
					$stmtStop->execute([':ride' => $id, ':ord' => $ord, ':city' => $city]);
					$ord++;
				}
			}
			$pdo->commit();
		} catch (Throwable $e) {
			$pdo->rollBack();
			flash('error', 'Errore aggiornamento passaggio.');
			redirect(url('?p=admin_ride_edit&id=' . $id));
		}

		flash('ok', 'Passaggio aggiornato.');
		redirect(url('?p=admin_rides'));
	}

	flash('error', implode(' ', $errors));

	$ride['driver_id'] = $driver_id;
	$ride['from_city'] = $from;
	$ride['to_city'] = $to;
	$ride['depart_at'] = $depart_at;
	$ride['seats_total'] = $seats_total;
	$ride['seats_available'] = $seats_available;
	$ride['price_cents'] = $price_cents;
	$ride['notes'] = $notes;
	$stops = $stopsNew;
}

$departLocal = str_replace(' ', 'T', (string)$ride['depart_at']);

render('admin/admin_ride_edit.php', [
	'ride' => $ride,
	'users' => $users,
	'departLocal' => $departLocal,
	'allowedCities' => allowed_cities(),
	'stops' => $stops,
]);

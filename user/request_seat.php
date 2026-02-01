<?php

require_login($pdo);

$user = current_user($pdo);

if (!is_post()) {
	redirect(url('?p=home'));
}

verify_csrf();

$rideId = (int)($_POST['ride_id'] ?? 0);
$message = trim((string)($_POST['message'] ?? ''));

if ($rideId <= 0) {
	flash('error', 'Passaggio non valido.');
	redirect(url('?p=search'));
}

$stmt = $pdo->prepare('SELECT id, driver_id, seats_available FROM rides WHERE id = :id');
$stmt->execute([':id' => $rideId]);
$ride = $stmt->fetch();
if (!$ride) {
	flash('error', 'Passaggio non trovato.');
	redirect(url('?p=search'));
}
if ((int)$ride['driver_id'] === (int)$user['id']) {
	flash('error', 'Non puoi richiedere un posto nel tuo passaggio.');
	redirect(url('?p=ride&id=' . $rideId));
}
if ((int)$ride['seats_available'] <= 0) {
	flash('error', 'Posti esauriti.');
	redirect(url('?p=ride&id=' . $rideId));
}

try {
	$stmt = $pdo->prepare(
		"INSERT INTO ride_requests (ride_id, passenger_id, status, message)
		 VALUES (:ride, :pid, 'pending', :msg)"
	);
	$stmt->execute([
		':ride' => $rideId,
		':pid' => (int)$user['id'],
		':msg' => $message === '' ? null : $message,
	]);
	flash('ok', 'Richiesta inviata.');
} catch (PDOException $e) {
	flash('error', 'Hai già una richiesta per questo passaggio.');
}

redirect(url('?p=ride&id=' . $rideId));

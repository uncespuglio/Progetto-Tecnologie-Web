<?php

require_login($pdo);

$user = current_user($pdo);

if (!is_post()) {
	redirect(url('?p=home'));
}

verify_csrf();

$requestId = (int)($_POST['request_id'] ?? 0);
$action = (string)($_POST['action'] ?? '');

if ($requestId <= 0 || !in_array($action, ['accept', 'reject', 'cancel'], true)) {
	flash('error', 'Azione non valida.');
	redirect(url('?p=my_rides'));
}

$stmt = $pdo->prepare(
	"SELECT rr.*, r.driver_id, r.seats_available, r.depart_at
	 FROM ride_requests rr
	 JOIN rides r ON r.id = rr.ride_id
	 WHERE rr.id = :id"
);
$stmt->execute([':id' => $requestId]);
$row = $stmt->fetch();
if (!$row) {
	flash('error', 'Richiesta non trovata.');
	redirect(url('?p=my_rides'));
}

$rideId = (int)$row['ride_id'];
$isDriver = ((int)$row['driver_id'] === (int)$user['id']);
$isPassenger = ((int)$row['passenger_id'] === (int)$user['id']);
$currentStatus = (string)$row['status'];

$rideDepart = (string)($row['depart_at'] ?? '');
if ($rideDepart !== '') {
	$stmtNow = $pdo->query('SELECT NOW()');
	$now = (string)$stmtNow->fetchColumn();
	if ($now !== '' && $rideDepart < $now) {
		flash('error', 'Il passaggio è già passato.');
		redirect(url('?p=ride&id=' . $rideId));
	}
}

if (($action === 'accept' || $action === 'reject') && !$isDriver) {
	flash('error', 'Non autorizzato.');
	redirect(url('?p=ride&id=' . $rideId));
}
if ($action === 'cancel' && !$isPassenger) {
	flash('error', 'Non autorizzato.');
	redirect(url('?p=ride&id=' . $rideId));
}

$pdo->beginTransaction();
try {
	if ($action === 'accept') {
		if ($currentStatus !== 'pending') {
			throw new RuntimeException('Stato non aggiornabile.');
		}

		$affected = $pdo->prepare('UPDATE rides SET seats_available = seats_available - 1 WHERE id = :ride AND seats_available > 0');
		$affected->execute([':ride' => $rideId]);
		if ($affected->rowCount() !== 1) {
			throw new RuntimeException('Posti esauriti.');
		}
		$pdo->prepare("UPDATE ride_requests SET status = 'accepted' WHERE id = :id")
			->execute([':id' => $requestId]);
		flash('ok', 'Richiesta accettata.');
	}

	if ($action === 'reject') {
		if ($currentStatus !== 'pending') {
			throw new RuntimeException('Stato non aggiornabile.');
		}
		$pdo->prepare("UPDATE ride_requests SET status = 'rejected' WHERE id = :id")
			->execute([':id' => $requestId]);
		flash('ok', 'Richiesta rifiutata.');
	}

	if ($action === 'cancel') {
		if (!in_array($currentStatus, ['pending', 'accepted'], true)) {
			throw new RuntimeException('Stato non aggiornabile.');
		}

		if ($currentStatus === 'accepted') {
			$pdo->prepare('UPDATE rides SET seats_available = seats_available + 1 WHERE id = :ride')
				->execute([':ride' => $rideId]);
		}
		$pdo->prepare("UPDATE ride_requests SET status = 'canceled' WHERE id = :id")
			->execute([':id' => $requestId]);
		flash('ok', 'Richiesta annullata.');
	}

	$pdo->commit();
} catch (Throwable $e) {
	$pdo->rollBack();
	flash('error', 'Impossibile aggiornare: ' . $e->getMessage());
}

if ($isDriver) {
	redirect(url('?p=my_rides'));
}
redirect(url('?p=ride&id=' . $rideId));

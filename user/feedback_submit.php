<?php

declare(strict_types=1);

require_login($pdo);

$user = current_user($pdo);
if ($user === null) {
	redirect(url('?p=login'));
}

if (!is_post()) {
	redirect(url('?p=home'));
}

verify_csrf();

$rideId = (int)($_POST['ride_id'] ?? 0);
$toUserId = (int)($_POST['to_user_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim((string)($_POST['comment'] ?? ''));

if ($rideId <= 0 || $toUserId <= 0 || $rating < 1 || $rating > 5) {
	flash('error', 'Feedback non valido.');
	redirect(url('?p=home'));
}

if ($toUserId === (int)$user['id']) {
	flash('error', 'Non puoi dare un feedback a te stesso.');
	redirect(url('?p=ride&id=' . $rideId));
}

$stmt = $pdo->prepare('SELECT id, driver_id, depart_at FROM rides WHERE id = :id');
$stmt->execute([':id' => $rideId]);
$ride = $stmt->fetch();
if (!$ride) {
	flash('error', 'Passaggio non trovato.');
	redirect(url('?p=home'));
}

$now = (string)$pdo->query('SELECT NOW()')->fetchColumn();
$departAt = (string)($ride['depart_at'] ?? '');
if ($now !== '' && $departAt !== '' && $departAt >= $now) {
	flash('error', 'Puoi lasciare feedback solo dopo aver effettuato il passaggio.');
	redirect(url('?p=ride&id=' . $rideId));
}

$fromUserId = (int)$user['id'];
$driverId = (int)$ride['driver_id'];

$allowed = false;

// Caso 1: driver -> passeggero (serve richiesta accepted)
if ($fromUserId === $driverId) {
	$stmt = $pdo->prepare(
		"SELECT COUNT(*)
		 FROM ride_requests
		 WHERE ride_id = :ride
		 AND passenger_id = :pid
		 AND status = 'accepted'"
	);
	$stmt->execute([':ride' => $rideId, ':pid' => $toUserId]);
	$allowed = ((int)$stmt->fetchColumn() > 0);
}

// Caso 2: passeggero -> driver (serve richiesta accepted)
if (!$allowed && $toUserId === $driverId) {
	$stmt = $pdo->prepare(
		"SELECT COUNT(*)
		 FROM ride_requests
		 WHERE ride_id = :ride
		 AND passenger_id = :pid
		 AND status = 'accepted'"
	);
	$stmt->execute([':ride' => $rideId, ':pid' => $fromUserId]);
	$allowed = ((int)$stmt->fetchColumn() > 0);
}

if (!$allowed) {
	flash('error', 'Non sei autorizzato a lasciare feedback per questo passaggio.');
	redirect(url('?p=ride&id=' . $rideId));
}

try {
	$stmt = $pdo->prepare(
		"INSERT INTO feedback (context, context_ref, ride_id, from_user_id, to_user_id, rating, comment)
		 VALUES ('ride', :ctxref, :ride, :from, :to, :rating, :comment)
		 ON DUPLICATE KEY UPDATE
			rating = VALUES(rating),
			comment = VALUES(comment)"
	);
	$stmt->execute([
		':ctxref' => (string)$rideId,
		':ride' => $rideId,
		':from' => $fromUserId,
		':to' => $toUserId,
		':rating' => $rating,
		':comment' => ($comment === '') ? null : $comment,
	]);
	flash('ok', 'Feedback salvato.');
} catch (Throwable $e) {
	flash('error', 'Impossibile salvare il feedback.');
}

redirect(url('?p=ride&id=' . $rideId));

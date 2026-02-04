<?php

declare(strict_types=1);

require_admin($pdo);

function admin_requests_adjust_seats(PDO $pdo, int $rideId, int $delta): void
{
	if ($delta === 0) {
		return;
	}
	if ($delta > 0) {
		$stmt = $pdo->prepare('UPDATE rides SET seats_available = LEAST(seats_total, seats_available + :d) WHERE id = :id');
		$stmt->execute([':d' => $delta, ':id' => $rideId]);
		return;
	}

	$need = abs($delta);
	$stmt = $pdo->prepare('UPDATE rides SET seats_available = seats_available - :d1 WHERE id = :id AND seats_available >= :d2');
	$stmt->execute([':d1' => $need, ':d2' => $need, ':id' => $rideId]);
	if ($stmt->rowCount() !== 1) {
		throw new RuntimeException('Posti esauriti.');
	}
}

if (is_post()) {
	verify_csrf();

	$op = (string)($_POST['op'] ?? '');
	$allowedOp = ['add', 'delete', 'set_status'];
	if (!in_array($op, $allowedOp, true)) {
		flash('error', 'Operazione non valida.');
		redirect(url('?p=admin_requests'));
	}

	$pdo->beginTransaction();
	try {
		if ($op === 'add') {
			$rideId = (int)($_POST['ride_id'] ?? 0);
			$passengerId = (int)($_POST['passenger_id'] ?? 0);
			$status = (string)($_POST['status'] ?? 'pending');
			$message = trim((string)($_POST['message'] ?? ''));

			if ($rideId <= 0 || $passengerId <= 0) {
				throw new RuntimeException('Ride o passeggero non validi.');
			}
			if (!in_array($status, ['pending', 'accepted', 'rejected', 'canceled'], true)) {
				throw new RuntimeException('Stato non valido.');
			}

			// Verifica ride e (se accepted) disponibilità posti.
			$stmt = $pdo->prepare('SELECT id, seats_available FROM rides WHERE id = :id');
			$stmt->execute([':id' => $rideId]);
			$ride = $stmt->fetch();
			if (!$ride) {
				throw new RuntimeException('Passaggio non trovato.');
			}

			if ($status === 'accepted') {
				admin_requests_adjust_seats($pdo, $rideId, -1);
			}

			try {
				$ins = $pdo->prepare(
					"INSERT INTO ride_requests (ride_id, passenger_id, status, message)
					 VALUES (:ride, :pid, :st, :msg)"
				);
				$ins->execute([
					':ride' => $rideId,
					':pid' => $passengerId,
					':st' => $status,
					':msg' => ($message === '') ? null : $message,
				]);
			} catch (PDOException $e) {
				throw new RuntimeException('Prenotazione già presente per questo utente/passaggio.');
			}

			flash('ok', 'Prenotazione aggiunta.');
		}

		if ($op === 'delete') {
			$requestId = (int)($_POST['request_id'] ?? 0);
			if ($requestId <= 0) {
				throw new RuntimeException('Richiesta non valida.');
			}

			$stmt = $pdo->prepare('SELECT id, ride_id, status FROM ride_requests WHERE id = :id');
			$stmt->execute([':id' => $requestId]);
			$rr = $stmt->fetch();
			if (!$rr) {
				throw new RuntimeException('Richiesta non trovata.');
			}

			if ((string)$rr['status'] === 'accepted') {
				admin_requests_adjust_seats($pdo, (int)$rr['ride_id'], +1);
			}

			$pdo->prepare('DELETE FROM ride_requests WHERE id = :id')->execute([':id' => $requestId]);
			flash('ok', 'Prenotazione eliminata.');
		}

		if ($op === 'set_status') {
			$requestId = (int)($_POST['request_id'] ?? 0);
			$next = (string)($_POST['next_status'] ?? '');
			if ($requestId <= 0 || !in_array($next, ['pending', 'accepted', 'rejected', 'canceled'], true)) {
				throw new RuntimeException('Parametri non validi.');
			}

			$stmt = $pdo->prepare('SELECT id, ride_id, status FROM ride_requests WHERE id = :id');
			$stmt->execute([':id' => $requestId]);
			$rr = $stmt->fetch();
			if (!$rr) {
				throw new RuntimeException('Richiesta non trovata.');
			}
			$rideId = (int)$rr['ride_id'];
			$cur = (string)$rr['status'];
			if ($cur === $next) {
				throw new RuntimeException('Stato già impostato.');
			}

			// Riallinea posti in base alla transizione.
			if ($cur !== 'accepted' && $next === 'accepted') {
				admin_requests_adjust_seats($pdo, $rideId, -1);
			}
			if ($cur === 'accepted' && $next !== 'accepted') {
				admin_requests_adjust_seats($pdo, $rideId, +1);
			}

			$pdo->prepare('UPDATE ride_requests SET status = :st WHERE id = :id')->execute([':st' => $next, ':id' => $requestId]);
			flash('ok', 'Stato aggiornato.');
		}

		$pdo->commit();
	} catch (Throwable $e) {
		$pdo->rollBack();
		flash('error', 'Errore: ' . $e->getMessage());
	}

	redirect(url('?p=admin_requests'));
}

$requests = $pdo->query(
	"SELECT rr.*, 
			r.from_city, r.to_city, r.depart_at,
			u.email AS passenger_email, u.full_name AS passenger_name,
			d.email AS driver_email
	 FROM ride_requests rr
	 JOIN rides r ON r.id = rr.ride_id
	 JOIN users u ON u.id = rr.passenger_id
	 JOIN users d ON d.id = r.driver_id
	 ORDER BY rr.created_at DESC, rr.id DESC"
)->fetchAll();

$rides = $pdo->query(
	"SELECT r.id, r.from_city, r.to_city, r.depart_at, r.seats_available, r.seats_total, u.email AS driver_email
	 FROM rides r
	 JOIN users u ON u.id = r.driver_id
	 ORDER BY r.depart_at DESC, r.id DESC"
)->fetchAll();

$users = $pdo->query(
	"SELECT id, email, full_name, role
	 FROM users
	 ORDER BY full_name ASC, email ASC"
)->fetchAll();

render('admin/admin_requests.php', [
	'requests' => $requests,
	'rides' => $rides,
	'users' => $users,
]);

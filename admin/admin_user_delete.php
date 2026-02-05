<?php

declare(strict_types=1);

require_admin($pdo);

$targetId = (int)($_GET['id'] ?? 0);
if ($targetId <= 0) {
	flash('error', 'ID utente non valido.');
	redirect(url('?p=admin_users'));
}

$current = current_user($pdo);
if ($current && (int)$current['id'] === $targetId) {
	flash('error', 'Non puoi eliminare il tuo stesso account mentre sei loggato.');
	redirect(url('?p=admin_user_edit&id=' . $targetId));
}

$stmt = $pdo->prepare(
	"SELECT u.*,
			 (SELECT COUNT(*) FROM rides r WHERE r.driver_id = u.id) AS rides_count,
			 (SELECT COUNT(*) FROM ride_requests rr WHERE rr.passenger_id = u.id) AS requests_count,
			 (SELECT COUNT(*) FROM ride_requests rr WHERE rr.passenger_id = u.id AND rr.status = 'accepted') AS accepted_requests_count
	 FROM users u
	 WHERE u.id = ?"
);
$stmt->execute([$targetId]);
$u = $stmt->fetch();
if (!$u) {
	flash('error', 'Utente non trovato.');
	redirect(url('?p=admin_users'));
}

if (is_post()) {
	verify_csrf();

	$pdo->beginTransaction();
	try {
		$acc = $pdo->prepare(
			"SELECT ride_id, COUNT(*) AS c
			 FROM ride_requests
			 WHERE passenger_id = :pid AND status = 'accepted'
			 GROUP BY ride_id"
		);
		$acc->execute([':pid' => $targetId]);
		$rows = $acc->fetchAll();
		if ($rows) {
			$upd = $pdo->prepare(
				"UPDATE rides
				 SET seats_available = LEAST(seats_total, seats_available + :delta)
				 WHERE id = :ride"
			);
			foreach ($rows as $r) {
				$rideId = (int)($r['ride_id'] ?? 0);
				$delta = (int)($r['c'] ?? 0);
				if ($rideId > 0 && $delta > 0) {
					$upd->execute([':delta' => $delta, ':ride' => $rideId]);
				}
			}
		}

		$pdo->prepare('DELETE FROM ride_requests WHERE passenger_id = ?')->execute([$targetId]);

		$pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$targetId]);

		$pdo->commit();
		flash('ok', 'Account eliminato.');
		redirect(url('?p=admin_users'));
	} catch (Throwable $e) {
		$pdo->rollBack();
		flash('error', 'Impossibile eliminare account.');
		redirect(url('?p=admin_user_delete&id=' . $targetId));
	}
}

render('admin/admin_user_delete.php', [
	'u' => $u,
]);

<?php

declare(strict_types=1);

require_admin($pdo);

if (is_post()) {
	verify_csrf();

	$op = (string)($_POST['op'] ?? '');
	if (!in_array($op, ['add', 'update', 'delete'], true)) {
		flash('error', 'Operazione non valida.');
		redirect(url('?p=admin_feedback'));
	}

	try {
		if ($op === 'add') {
			$context = (string)($_POST['context'] ?? 'manual');
			$rideIdSelect = (int)($_POST['ride_id_select'] ?? 0);
			$rideIdManual = (int)($_POST['ride_id_manual'] ?? 0);
			$rideId = $rideIdManual > 0 ? $rideIdManual : $rideIdSelect;
			$fromUserId = (int)($_POST['from_user_id'] ?? 0);
			$toUserId = (int)($_POST['to_user_id'] ?? 0);
			$rating = (int)($_POST['rating'] ?? 0);
			$comment = trim((string)($_POST['comment'] ?? ''));

			if (!in_array($context, ['manual', 'ride'], true)) {
				throw new RuntimeException('Contesto non valido.');
			}
			if ($fromUserId <= 0 || $toUserId <= 0 || $fromUserId === $toUserId) {
				throw new RuntimeException('Utenti non validi.');
			}
			if ($rating < 1 || $rating > 5) {
				throw new RuntimeException('Rating non valido.');
			}

			$contextRef = '';
			$rideFk = null;
			if ($context === 'ride') {
				if ($rideId <= 0) {
					throw new RuntimeException('Ride ID obbligatorio per feedback di tipo ride.');
				}
				$stmt = $pdo->prepare('SELECT id FROM rides WHERE id = :id');
				$stmt->execute([':id' => $rideId]);
				if (!(int)$stmt->fetchColumn()) {
					throw new RuntimeException('Passaggio non trovato.');
				}
				$contextRef = (string)$rideId . '-' . bin2hex(random_bytes(4));
				$rideFk = $rideId;
			} else {
				$contextRef = 'm-' . bin2hex(random_bytes(6));
			}

			$stmt = $pdo->prepare(
				"INSERT INTO feedback (context, context_ref, ride_id, from_user_id, to_user_id, rating, comment)
				 VALUES (:ctx, :ref, :ride, :from, :to, :rating, :comment)"
			);
			$stmt->execute([
				':ctx' => $context,
				':ref' => $contextRef,
				':ride' => $rideFk,
				':from' => $fromUserId,
				':to' => $toUserId,
				':rating' => $rating,
				':comment' => ($comment === '') ? null : $comment,
			]);

			flash('ok', 'Recensione salvata.');
		}

		if ($op === 'update') {
			$feedbackId = (int)($_POST['feedback_id'] ?? 0);
			$rating = (int)($_POST['rating'] ?? 0);
			$comment = trim((string)($_POST['comment'] ?? ''));

			if ($feedbackId <= 0 || $rating < 1 || $rating > 5) {
				throw new RuntimeException('Parametri non validi.');
			}

			$stmt = $pdo->prepare('UPDATE feedback SET rating = :r, comment = :c WHERE id = :id');
			$stmt->execute([
				':r' => $rating,
				':c' => ($comment === '') ? null : $comment,
				':id' => $feedbackId,
			]);
			flash('ok', 'Recensione aggiornata.');
		}

		if ($op === 'delete') {
			$feedbackId = (int)($_POST['feedback_id'] ?? 0);
			if ($feedbackId <= 0) {
				throw new RuntimeException('Recensione non valida.');
			}
			$pdo->prepare('DELETE FROM feedback WHERE id = :id')->execute([':id' => $feedbackId]);
			flash('ok', 'Recensione rimossa.');
		}
	} catch (Throwable $e) {
		flash('error', 'Errore: ' . $e->getMessage());
	}

	redirect(url('?p=admin_feedback'));
}

$users = $pdo->query(
	"SELECT id, email, full_name, role
	 FROM users
	 ORDER BY full_name ASC, email ASC"
)->fetchAll();

$rides = $pdo->query(
	"SELECT r.id, r.from_city, r.to_city, r.depart_at,
		u.full_name AS driver_name
	 FROM rides r
	 JOIN users u ON u.id = r.driver_id
	 ORDER BY r.depart_at DESC, r.id DESC
	 LIMIT 200"
)->fetchAll();

$feedback = $pdo->query(
	"SELECT f.*, 
		fu.full_name AS from_name, fu.email AS from_email,
		tu.full_name AS to_name, tu.email AS to_email,
		r.from_city, r.to_city, r.depart_at
	 FROM feedback f
	 JOIN users fu ON fu.id = f.from_user_id
	 JOIN users tu ON tu.id = f.to_user_id
	 LEFT JOIN rides r ON r.id = f.ride_id
	 ORDER BY f.created_at DESC, f.id DESC"
)->fetchAll();

render('admin/admin_feedback.php', [
	'users' => $users,
	'rides' => $rides,
	'feedback' => $feedback,
]);

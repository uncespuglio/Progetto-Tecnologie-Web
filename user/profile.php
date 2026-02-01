<?php

require_login($pdo);

$user = current_user($pdo);

if (is_post()) {
	verify_csrf();

	$fullName = trim((string)($_POST['full_name'] ?? ''));
	$phone = trim((string)($_POST['phone'] ?? ''));
	$newPassword = (string)($_POST['new_password'] ?? '');
	$university = unibo_university();

	if ($fullName === '') {
		flash('error', 'Nome e cognome sono obbligatori.');
		redirect(url('?p=profile'));
	}
	if ($phone !== '' && strlen($phone) > 30) {
		flash('error', 'Telefono troppo lungo.');
		redirect(url('?p=profile'));
	}
	if ($newPassword !== '' && strlen($newPassword) < 8) {
		flash('error', 'La nuova password deve avere almeno 8 caratteri.');
		redirect(url('?p=profile'));
	}

	$pdo->beginTransaction();
	try {
		$stmt = $pdo->prepare('UPDATE users SET full_name = :n, university = :u, phone = :p WHERE id = :id');
		$stmt->execute([
			':n' => $fullName,
			':u' => $university,
			':p' => ($phone === '' ? null : $phone),
			':id' => (int)$user['id'],
		]);

		if ($newPassword !== '') {
			$hash = password_hash($newPassword, PASSWORD_DEFAULT);
			$pdo->prepare('UPDATE users SET password_hash = :h WHERE id = :id')
				->execute([':h' => $hash, ':id' => (int)$user['id']]);
		}
		$pdo->commit();
		flash('ok', 'Profilo aggiornato.');
	} catch (Throwable $e) {
		$pdo->rollBack();
		flash('error', 'Errore aggiornamento profilo.');
	}
	redirect(url('?p=profile'));
}

$stmt = $pdo->prepare(
	"SELECT rr.id AS request_id, rr.status, rr.created_at,
		r.id AS ride_id, r.from_city, r.to_city, r.depart_at,
		u.full_name AS driver_name, u.university AS driver_uni
	 FROM ride_requests rr
	 JOIN rides r ON r.id = rr.ride_id
	 JOIN users u ON u.id = r.driver_id
	 WHERE rr.passenger_id = :uid
	 ORDER BY r.depart_at DESC, rr.created_at DESC"
);
$stmt->execute([':uid' => (int)$user['id']]);
$myRequests = $stmt->fetchAll();

render('user/profile.php', [
	'user' => current_user($pdo),
	'myRequests' => $myRequests,
]);

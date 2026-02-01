<?php

require_admin($pdo);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
	flash('error', 'ID passaggio non valido.');
	redirect(url('?p=admin_rides'));
}

$ride = $pdo->prepare(
	"SELECT r.*, u.full_name AS driver_name
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verify_csrf();

	$pdo->prepare('DELETE FROM ride_requests WHERE ride_id = ?')->execute([$id]);
	$pdo->prepare('DELETE FROM rides WHERE id = ?')->execute([$id]);

	flash('ok', 'Passaggio eliminato.');
	redirect(url('?p=admin_rides'));
}

render('admin/admin_ride_delete.php', [
	'ride' => $ride,
]);

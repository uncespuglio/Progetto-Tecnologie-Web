<?php

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
	http_response_code(400);
	flash('error', 'ID passaggio non valido.');
	redirect(url('?p=search'));
}

$stmt = $pdo->prepare(
	"SELECT r.*, u.full_name AS driver_name, u.university, u.id AS driver_user_id
	 FROM rides r
	 JOIN users u ON u.id = r.driver_id
	 WHERE r.id = :id"
);
$stmt->execute([':id' => $id]);
$ride = $stmt->fetch();
if (!$ride) {
	http_response_code(404);
	render('pages/404.php', ['page' => 'ride']);
	return;
}

$user = current_user($pdo);
$isDriver = $user && ((int)$user['id'] === (int)$ride['driver_id']);

$myRequest = null;
if ($user && !$isDriver) {
	$stmt = $pdo->prepare('SELECT * FROM ride_requests WHERE ride_id = :ride AND passenger_id = :pid');
	$stmt->execute([':ride' => $id, ':pid' => (int)$user['id']]);
	$myRequest = $stmt->fetch() ?: null;
}

$stmt = $pdo->prepare('SELECT stop_city FROM ride_stops WHERE ride_id = :id ORDER BY stop_order ASC');
$stmt->execute([':id' => $id]);
$stops = array_map(static fn($row) => (string)$row['stop_city'], $stmt->fetchAll());

render('pages/ride.php', [
	'ride' => $ride,
	'user' => $user,
	'isDriver' => $isDriver,
	'myRequest' => $myRequest,
	'stops' => $stops,
]);

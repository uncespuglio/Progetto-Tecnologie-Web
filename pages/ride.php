<?php

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
	http_response_code(400);
	flash('error', 'ID passaggio non valido.');
	redirect(url('?p=search'));
}

$stmt = $pdo->prepare(
	"SELECT r.*, u.full_name AS driver_name, u.university, u.email AS driver_email, u.id AS driver_user_id,
		fb.avg_rating AS driver_avg_rating, fb.cnt AS driver_feedback_count
	 FROM rides r
	 JOIN users u ON u.id = r.driver_id
	 LEFT JOIN (
		SELECT to_user_id, AVG(rating) AS avg_rating, COUNT(*) AS cnt
		FROM feedback
		GROUP BY to_user_id
	 ) fb ON fb.to_user_id = r.driver_id
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

$now = (string)$pdo->query('SELECT NOW()')->fetchColumn();
$isPast = ($now !== '' && (string)$ride['depart_at'] < $now);

$myRequest = null;
if ($user && !$isDriver) {
	$stmt = $pdo->prepare('SELECT * FROM ride_requests WHERE ride_id = :ride AND passenger_id = :pid');
	$stmt->execute([':ride' => $id, ':pid' => (int)$user['id']]);
	$myRequest = $stmt->fetch() ?: null;
}

$rideRequests = [];
$travelers = [];
$feedbackGivenByMe = [];
$myFeedbackToDriver = null;
$feedbackFromDriver = null;
if ($isDriver) {
	$stmt = $pdo->prepare(
		"SELECT rr.id AS request_id, rr.status, rr.message, rr.created_at,
			u.id AS passenger_id, u.full_name AS passenger_name, u.email AS passenger_email, u.phone AS passenger_phone
		 FROM ride_requests rr
		 JOIN users u ON u.id = rr.passenger_id
		 WHERE rr.ride_id = :ride
		 ORDER BY rr.created_at DESC, rr.id DESC"
	);
	$stmt->execute([':ride' => $id]);
	$rideRequests = $stmt->fetchAll();

	$stmt = $pdo->prepare(
		"SELECT u.id AS passenger_id, u.full_name AS passenger_name, u.email AS passenger_email, u.phone AS passenger_phone
		 FROM ride_requests rr
		 JOIN users u ON u.id = rr.passenger_id
		 WHERE rr.ride_id = :ride
		 AND rr.status = 'accepted'
		 ORDER BY rr.created_at ASC, rr.id ASC"
	);
	$stmt->execute([':ride' => $id]);
	$travelers = $stmt->fetchAll();

	$stmt = $pdo->prepare(
		'SELECT to_user_id, rating FROM feedback WHERE ride_id = :ride AND from_user_id = :from'
	);
	$stmt->execute([':ride' => $id, ':from' => (int)$user['id']]);
	foreach ($stmt->fetchAll() as $row) {
		$feedbackGivenByMe[(int)$row['to_user_id']] = (int)$row['rating'];
	}
}

if ($user && !$isDriver && $myRequest && (string)$myRequest['status'] === 'accepted' && $isPast) {
	$stmt = $pdo->prepare(
		'SELECT rating FROM feedback WHERE ride_id = :ride AND from_user_id = :from AND to_user_id = :to'
	);
	$stmt->execute([
		':ride' => $id,
		':from' => (int)$user['id'],
		':to' => (int)$ride['driver_id'],
	]);
	$myFeedbackToDriver = $stmt->fetch() ?: null;

	$stmt = $pdo->prepare(
		'SELECT rating FROM feedback WHERE ride_id = :ride AND from_user_id = :from AND to_user_id = :to'
	);
	$stmt->execute([
		':ride' => $id,
		':from' => (int)$ride['driver_id'],
		':to' => (int)$user['id'],
	]);
	$feedbackFromDriver = $stmt->fetch() ?: null;
}

$stmt = $pdo->prepare('SELECT stop_city FROM ride_stops WHERE ride_id = :id ORDER BY stop_order ASC');
$stmt->execute([':id' => $id]);
$stops = array_map(static fn($row) => (string)$row['stop_city'], $stmt->fetchAll());

render('pages/ride.php', [
	'ride' => $ride,
	'user' => $user,
	'isDriver' => $isDriver,
	'isPast' => $isPast,
	'myRequest' => $myRequest,
	'stops' => $stops,
	'rideRequests' => $rideRequests,
	'travelers' => $travelers,
	'feedbackGivenByMe' => $feedbackGivenByMe,
	'myFeedbackToDriver' => $myFeedbackToDriver,
	'feedbackFromDriver' => $feedbackFromDriver,
]);

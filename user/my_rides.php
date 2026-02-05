<?php

require_login($pdo);

$user = current_user($pdo);

$stmt = $pdo->prepare(
	"SELECT r.*
	 FROM rides r
	 WHERE r.driver_id = :uid
	 AND r.depart_at >= NOW()
	 ORDER BY r.depart_at ASC"
);
$stmt->execute([':uid' => (int)$user['id']]);
$ridesUpcoming = $stmt->fetchAll();

$stmt = $pdo->prepare(
	"SELECT r.*
	 FROM rides r
	 WHERE r.driver_id = :uid
	 AND r.depart_at < NOW()
	 ORDER BY r.depart_at DESC"
);
$stmt->execute([':uid' => (int)$user['id']]);
$ridesPast = $stmt->fetchAll();

$stmt = $pdo->prepare(
	"SELECT rr.id AS request_id, rr.status, rr.message, rr.created_at,
		r.id AS ride_id, r.from_city, r.to_city, r.depart_at,
		u.full_name AS passenger_name, u.university AS passenger_uni
	 FROM ride_requests rr
	 JOIN rides r ON r.id = rr.ride_id
	 JOIN users u ON u.id = rr.passenger_id
	 WHERE r.driver_id = :uid
	 AND r.depart_at >= NOW()
	 ORDER BY r.depart_at ASC, rr.created_at DESC"
);
$stmt->execute([':uid' => (int)$user['id']]);
$requestsUpcoming = $stmt->fetchAll();

$stmt = $pdo->prepare(
	"SELECT rr.id AS request_id, rr.status, rr.message, rr.created_at,
		r.id AS ride_id, r.from_city, r.to_city, r.depart_at,
		u.full_name AS passenger_name, u.university AS passenger_uni
	 FROM ride_requests rr
	 JOIN rides r ON r.id = rr.ride_id
	 JOIN users u ON u.id = rr.passenger_id
	 WHERE r.driver_id = :uid
	 AND r.depart_at < NOW()
	 ORDER BY r.depart_at DESC, rr.created_at DESC"
);
$stmt->execute([':uid' => (int)$user['id']]);
$requestsPast = $stmt->fetchAll();

render('user/my_rides.php', [
	'user' => $user,
	'ridesUpcoming' => $ridesUpcoming,
	'ridesPast' => $ridesPast,
	'requestsUpcoming' => $requestsUpcoming,
	'requestsPast' => $requestsPast,
]);

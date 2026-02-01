<?php

$user = current_user($pdo);

$allowed = allowed_cities();
$fromIn = [];
$toIn = [];
$params = [];
foreach ($allowed as $i => $c) {
	$ph1 = ':from_allowed_' . $i;
	$ph2 = ':to_allowed_' . $i;
	$fromIn[] = $ph1;
	$toIn[] = $ph2;
	$params[$ph1] = $c;
	$params[$ph2] = $c;
}

$stmt = $pdo->prepare(
	"SELECT r.id, r.from_city, r.to_city, r.depart_at, r.seats_available, r.price_cents,
		u.full_name AS driver_name, u.university
	 FROM rides r
	 JOIN users u ON u.id = r.driver_id
	 WHERE r.from_city IN (" . implode(',', $fromIn) . ")
	 AND r.to_city IN (" . implode(',', $toIn) . ")
	 ORDER BY r.depart_at ASC
	 LIMIT 10"
);
$stmt->execute($params);
$rides = $stmt->fetchAll();

render('pages/home.php', [
	'user' => $user,
	'rides' => $rides,
	'allowedCities' => $allowed,
]);

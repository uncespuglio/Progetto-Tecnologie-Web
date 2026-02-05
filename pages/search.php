<?php

$fromRaw = trim((string)($_GET['from'] ?? ''));
$toRaw = trim((string)($_GET['to'] ?? ''));
$date = trim((string)($_GET['date'] ?? ''));

$from = $fromRaw === '' ? '' : (normalize_city($fromRaw) ?? '');
$to = $toRaw === '' ? '' : (normalize_city($toRaw) ?? '');

if ($fromRaw !== '' && $from === '') {
	flash('error', 'Partenza non valida: seleziona una sede UNIBO tra Bologna/Forlì/Cesena/Ravenna/Rimini.');
	redirect(url('?p=search'));
}
if ($toRaw !== '' && $to === '') {
	flash('error', 'Arrivo non valido: seleziona una sede UNIBO tra Bologna/Forlì/Cesena/Ravenna/Rimini.');
	redirect(url('?p=search'));
}
if ($from !== '' && $to !== '' && $from === $to) {
	flash('error', 'Partenza e arrivo devono essere diversi.');
	redirect(url('?p=search'));
}

$where = [];
$params = [];

$allowed = allowed_cities();
$in = implode(',', array_fill(0, count($allowed), '?'));
$where[] = 'r.from_city IN (' . $in . ')';
$where[] = 'r.to_city IN (' . $in . ')';
foreach ($allowed as $c) {
	$params[] = $c;
}

$where[] = 'r.depart_at >= NOW()';
foreach ($allowed as $c) {
	$params[] = $c;
}

if ($from !== '') {
	$where[] = '(
		r.from_city = ?
		OR EXISTS (SELECT 1 FROM ride_stops s WHERE s.ride_id = r.id AND s.stop_city = ?)
	)';
	$params[] = $from;
	$params[] = $from;
}
if ($to !== '') {
	$where[] = '(
		r.to_city = ?
		OR EXISTS (SELECT 1 FROM ride_stops s WHERE s.ride_id = r.id AND s.stop_city = ?)
	)';
	$params[] = $to;
	$params[] = $to;
}

if ($from !== '' && $to !== '') {
	$where[] = '(
		(r.from_city = ? AND r.to_city = ?)
		OR (r.from_city = ? AND EXISTS (SELECT 1 FROM ride_stops s_to WHERE s_to.ride_id = r.id AND s_to.stop_city = ?))
		OR (EXISTS (SELECT 1 FROM ride_stops s_from WHERE s_from.ride_id = r.id AND s_from.stop_city = ?) AND r.to_city = ?)
		OR EXISTS (
			SELECT 1
			FROM ride_stops s_from
			JOIN ride_stops s_to ON s_from.ride_id = s_to.ride_id
			WHERE s_from.ride_id = r.id
			AND s_from.stop_city = ?
			AND s_to.stop_city = ?
			AND s_from.stop_order < s_to.stop_order
		)
	)';
	
	$params[] = $from;
	$params[] = $to;
	$params[] = $from;
	$params[] = $to;
	$params[] = $from;
	$params[] = $to;
	$params[] = $from;
	$params[] = $to;
}
if ($date !== '') {
	$where[] = 'DATE(r.depart_at) = :date';
	$where[count($where) - 1] = 'DATE(r.depart_at) = ?';
	$params[] = $date;
}

$sql = "SELECT r.id, r.from_city, r.to_city, r.depart_at, r.seats_available,
		u.full_name AS driver_name, u.university,
		fb.avg_rating AS driver_avg_rating, fb.cnt AS driver_feedback_count
		FROM rides r
		JOIN users u ON u.id = r.driver_id
		LEFT JOIN (
			SELECT to_user_id, AVG(rating) AS avg_rating, COUNT(*) AS cnt
			FROM feedback
			GROUP BY to_user_id
		) fb ON fb.to_user_id = r.driver_id";

if ($where) {
	$sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY r.depart_at ASC LIMIT 100';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rides = $stmt->fetchAll();

render('pages/search.php', [
	'from' => $from,
	'to' => $to,
	'date' => $date,
	'rides' => $rides,
	'allowedCities' => $allowed,
]);

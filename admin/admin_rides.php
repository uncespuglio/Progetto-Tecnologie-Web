<?php

require_admin($pdo);

$rides = $pdo->query(
	"SELECT r.*, u.full_name AS driver_name, u.email AS driver_email
	 FROM rides r
	 JOIN users u ON u.id = r.driver_id
	 ORDER BY r.depart_at DESC, r.id DESC"
)->fetchAll();

render('admin/admin_rides.php', [
	'rides' => $rides,
]);


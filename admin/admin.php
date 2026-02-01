<?php

require_admin($pdo);

$user = current_user($pdo);

$counts = [
	'users' => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
	'rides' => (int)$pdo->query('SELECT COUNT(*) FROM rides')->fetchColumn(),
	'requests' => (int)$pdo->query('SELECT COUNT(*) FROM ride_requests')->fetchColumn(),
];

render('admin/admin.php', [
	'user' => $user,
	'counts' => $counts,
]);

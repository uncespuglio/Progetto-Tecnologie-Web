<?php

require_admin($pdo);

$users = $pdo->query(
	"SELECT u.*,
		 (SELECT COUNT(*) FROM rides r WHERE r.driver_id = u.id) AS rides_count,
		 (SELECT COUNT(*) FROM ride_requests rr WHERE rr.passenger_id = u.id) AS requests_count,
		 (SELECT AVG(f.rating) FROM feedback f WHERE f.to_user_id = u.id) AS avg_rating,
		 (SELECT COUNT(*) FROM feedback f WHERE f.to_user_id = u.id) AS feedback_count
	 FROM users u
	 ORDER BY u.created_at DESC, u.id DESC"
)->fetchAll();

render('admin/admin_users.php', [
	'users' => $users,
]);

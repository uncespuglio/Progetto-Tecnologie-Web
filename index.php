<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

verify_csrf();

$page = (string)($_GET['p'] ?? 'home');

switch ($page) {
	case 'home':
		require __DIR__ . '/pages/home.php';
		break;
	case 'search':
		require __DIR__ . '/pages/search.php';
		break;
	case 'ride':
		require __DIR__ . '/pages/ride.php';
		break;
		case 'ride_create':
			require __DIR__ . '/user/ride_create.php';
		break;
		case 'my_rides':
			require __DIR__ . '/user/my_rides.php';
		break;
		case 'profile':
			require __DIR__ . '/user/profile.php';
		break;
		case 'admin':
			require __DIR__ . '/admin/admin.php';
		break;
		case 'admin_rides':
			require __DIR__ . '/admin/admin_rides.php';
		break;
		case 'admin_ride_create':
			require __DIR__ . '/admin/admin_ride_create.php';
		break;
		case 'admin_ride_edit':
			require __DIR__ . '/admin/admin_ride_edit.php';
		break;
		case 'admin_ride_delete':
			require __DIR__ . '/admin/admin_ride_delete.php';
		break;
		case 'admin_users':
			require __DIR__ . '/admin/admin_users.php';
		break;
		case 'admin_user_edit':
			require __DIR__ . '/admin/admin_user_edit.php';
		break;
		case 'admin_user_delete':
			require __DIR__ . '/admin/admin_user_delete.php';
		break;
		case 'admin_requests':
			require __DIR__ . '/admin/admin_requests.php';
		break;
		case 'admin_feedback':
			require __DIR__ . '/admin/admin_feedback.php';
		break;
		case 'request_seat':
			require __DIR__ . '/user/request_seat.php';
		break;
		case 'request_update':
			require __DIR__ . '/user/request_update.php';
		break;
		case 'feedback_submit':
			require __DIR__ . '/user/feedback_submit.php';
		break;
	case 'login':
		require __DIR__ . '/pages/login.php';
		break;
	case 'register':
		require __DIR__ . '/pages/register.php';
		break;
		case 'logout':
			require __DIR__ . '/user/logout.php';
		break;
	default:
		http_response_code(404);
		render('pages/404.php', ['page' => $page]);
}


<?php

require_admin($pdo);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
	flash('error', 'ID utente non valido.');
	redirect(url('?p=admin_users'));
}

$user = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$user->execute([$id]);
$user = $user->fetch();
if (!$user) {
	flash('error', 'Utente non trovato.');
	redirect(url('?p=admin_users'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verify_csrf();

	$full_name = trim((string)($_POST['full_name'] ?? ''));
	$university = unibo_university();
	$phone = trim((string)($_POST['phone'] ?? ''));
	$role = trim((string)($_POST['role'] ?? 'user'));
	$reset_password = (string)($_POST['reset_password'] ?? '');

	$errors = [];
	if ($full_name === '') $errors[] = 'Nome e cognome obbligatori.';
	if (!in_array($role, ['user', 'admin'], true)) $errors[] = 'Ruolo non valido.';
	if ($reset_password !== '' && strlen($reset_password) < 8) $errors[] = 'La nuova password deve essere lunga almeno 8 caratteri.';

	if (!$errors) {
		if ($reset_password !== '') {
			$stmt = $pdo->prepare('UPDATE users SET full_name = ?, university = ?, phone = ?, role = ?, password_hash = ? WHERE id = ?');
			$stmt->execute([$full_name, $university, $phone, $role, password_hash($reset_password, PASSWORD_DEFAULT), $id]);
		} else {
			$stmt = $pdo->prepare('UPDATE users SET full_name = ?, university = ?, phone = ?, role = ? WHERE id = ?');
			$stmt->execute([$full_name, $university, $phone, $role, $id]);
		}

		flash('ok', 'Utente aggiornato.');
		redirect(url('?p=admin_users'));
	}

	flash('error', implode(' ', $errors));

	$user['full_name'] = $full_name;
	$user['university'] = $university;
	$user['phone'] = $phone;
	$user['role'] = $role;
}

render('admin/admin_user_edit.php', [
	'u' => $user,
]);

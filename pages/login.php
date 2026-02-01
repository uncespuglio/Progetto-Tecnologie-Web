<?php

if (is_post()) {
	$email = trim((string)($_POST['email'] ?? ''));
	$password = (string)($_POST['password'] ?? '');

	if ($email === '' || $password === '') {
		flash('error', 'Inserisci email e password.');
		redirect(url('?p=login'));
	}

	$stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = :email');
	$stmt->execute([':email' => $email]);
	$user = $stmt->fetch();
	if (!$user || !password_verify($password, (string)$user['password_hash'])) {
		flash('error', 'Credenziali non valide.');
		redirect(url('?p=login'));
	}

	session_regenerate_id(true);
	$_SESSION['user_id'] = (int)$user['id'];
	flash('ok', 'Login effettuato.');
	redirect(url('?p=home'));
}

render('pages/login.php');

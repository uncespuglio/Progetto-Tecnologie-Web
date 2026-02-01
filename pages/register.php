<?php

if (is_post()) {
	$email = trim((string)($_POST['email'] ?? ''));
	$fullName = trim((string)($_POST['full_name'] ?? ''));
	$phone = trim((string)($_POST['phone'] ?? ''));
	$password = (string)($_POST['password'] ?? '');
	$university = unibo_university();

	if ($email === '' || $fullName === '' || $password === '') {
		flash('error', 'Compila tutti i campi.');
		redirect(url('?p=register'));
	}
	if (!is_unibo_email($email)) {
		flash('error', 'Registrazione consentita solo con email UNIBO (es. @studio.unibo.it).');
		redirect(url('?p=register'));
	}
	if (strlen($password) < 8) {
		flash('error', 'La password deve avere almeno 8 caratteri.');
		redirect(url('?p=register'));
	}

	$hash = password_hash($password, PASSWORD_DEFAULT);

	try {
		$stmt = $pdo->prepare('INSERT INTO users (email, password_hash, full_name, university, phone) VALUES (:email, :hash, :name, :uni, :phone)');
		$stmt->execute([
			':email' => $email,
			':hash' => $hash,
			':name' => $fullName,
			':uni' => $university,
			':phone' => ($phone === '' ? null : $phone),
		]);
		$userId = (int)$pdo->lastInsertId();
		session_regenerate_id(true);
		$_SESSION['user_id'] = $userId;
		flash('ok', 'Registrazione completata.');
		redirect(url('?p=home'));
	} catch (PDOException $e) {
		flash('error', 'Impossibile registrare: email già in uso?');
		redirect(url('?p=register'));
	}
}

render('pages/register.php');

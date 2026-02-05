<?php
/** @var string $content */

$pdo = db();
$user = current_user($pdo);
?>
<!doctype html>
<html lang="it">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>UniRide</title>
	<link rel="stylesheet" href="style.css?v=<?= (int) @filemtime(__DIR__ . '/../style.css') ?>">
</head>
<body>
	<a class="skip-link" href="#main">Vai al contenuto</a>
	<div class="container">
		<nav class="nav" aria-label="Navigazione principale">
			<a class="brand" href="<?= e(url('?p=home')) ?>">
				<span class="brand-badge" aria-hidden="true"></span>
				<span>UniRide</span>
			</a>
			<div class="nav-links">
				<a class="btn" href="<?= e(url('?p=search')) ?>">Cerca</a>
				<?php if ($user): ?>
					<a class="btn primary" href="<?= e(url('?p=ride_create')) ?>">Pubblica passaggio</a>
					<a class="btn" href="<?= e(url('?p=my_rides')) ?>">I miei passaggi</a>
					<a class="btn" href="<?= e(url('?p=profile')) ?>">Profilo</a>
					<?php if (is_admin($user)): ?>
						<a class="btn" href="<?= e(url('?p=admin')) ?>">Admin</a>
					<?php endif; ?>
					<a class="btn danger" href="<?= e(url('?p=logout')) ?>">Logout</a>
				<?php else: ?>
					<a class="btn" href="<?= e(url('?p=login')) ?>">Login</a>
					<a class="btn primary" href="<?= e(url('?p=register')) ?>">Registrati</a>
				<?php endif; ?>
			</div>
		</nav>

		<?php if ($msg = flash('error')): ?>
			<div class="alert error" style="margin-top:14px;"><?= e($msg) ?></div>
		<?php endif; ?>
		<?php if ($msg = flash('ok')): ?>
			<div class="alert ok" style="margin-top:14px;"><?= e($msg) ?></div>
		<?php endif; ?>

		<main id="main" tabindex="-1">
			<?= $content ?>
		</main>

		<div class="footer">
			<a class="footer-link" href="https://github.com/uncespuglio/Progetto-Tecnologie-Web/tree/main" target="_blank" rel="noopener noreferrer" aria-label="Apri la repository GitHub del progetto UniRide">
				UniRide • Progetto universitario • <?= e(date('Y')) ?>
			</a>
		</div>
	</div>
	<script defer src="js/uniride-ui.js?v=<?= (int) @filemtime(__DIR__ . '/../js/uniride-ui.js') ?>"></script>
	<script defer src="js/validate-email-studio.js?v=<?= (int) @filemtime(__DIR__ . '/../js/validate-email-studio.js') ?>"></script>
	<script defer src="js/validate-password-length.js?v=<?= (int) @filemtime(__DIR__ . '/../js/validate-password-length.js') ?>"></script>
</body>
</html>

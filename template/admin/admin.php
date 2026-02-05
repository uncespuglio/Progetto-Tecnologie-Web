<?php /** @var array $user */ ?>
<div class="card" style="margin-top:18px;">
	<p class="pill">Admin</p>
	<h1 class="title">Pannello di amministrazione</h1>
	<p class="subtitle">Gestione contenuti del servizio e utenti.</p>

	<div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
		<a class="btn" href="<?= e(url('?p=admin_rides')) ?>">Gestisci passaggi</a>
		<a class="btn" href="<?= e(url('?p=admin_requests')) ?>">Gestisci prenotazioni</a>
		<a class="btn" href="<?= e(url('?p=admin_feedback')) ?>">Gestisci recensioni</a>
		<a class="btn" href="<?= e(url('?p=admin_users')) ?>">Gestisci utenti</a>
	</div>

	<div class="muted" style="margin-top:14px; font-size:13px;">
		Sei loggato come: <?= e($user['email']) ?> • ruolo: <span class="pill"><?= e((string)$user['role']) ?></span>
	</div>
</div>

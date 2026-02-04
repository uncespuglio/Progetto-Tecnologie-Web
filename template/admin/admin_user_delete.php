<?php /** @var array $u */ ?>
<div class="card" style="margin-top:18px;">
	<p class="pill">Admin</p>
	<h1 class="title">Elimina account</h1>
	<p class="subtitle">Conferma eliminazione (operazione <strong>Delete</strong>).</p>

	<div style="margin-top:12px;">
		<div><strong><?= e($u['email']) ?></strong></div>
		<div class="muted">Nome: <?= e($u['full_name']) ?></div>
		<div class="muted">Ruolo: <span class="pill"><?= e((string)($u['role'] ?? 'user')) ?></span></div>
		<div class="muted" style="margin-top:8px;">
			Passaggi pubblicati: <?= e((string)($u['rides_count'] ?? 0)) ?> •
			Prenotazioni fatte: <?= e((string)($u['requests_count'] ?? 0)) ?> •
			Accettate: <?= e((string)($u['accepted_requests_count'] ?? 0)) ?>
		</div>
	</div>

	<div class="alert error" style="margin-top:14px;">
		Operazione irreversibile. Le prenotazioni accettate verranno rimosse e i posti disponibili verranno riallineati.
	</div>

	<form method="post" action="<?= e(url('?p=admin_user_delete&id=' . (int)$u['id'])) ?>" style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
		<?= csrf_field() ?>
		<button class="btn danger" type="submit">Sì, elimina account</button>
		<a class="btn" href="<?= e(url('?p=admin_users')) ?>">No, torna indietro</a>
	</form>
</div>

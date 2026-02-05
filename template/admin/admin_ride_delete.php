<?php /** @var array $ride */ ?>
<div class="card" style="margin-top:18px;">
	<p class="pill">Admin</p>
	<h1 class="title">Elimina passaggio</h1>
	<p class="subtitle">Conferma eliminazione (operazione <strong>Delete</strong>).</p>

	<div style="margin-top:12px;">
		<div><strong><?= e($ride['from_city']) ?> → <?= e($ride['to_city']) ?></strong></div>
		<div class="muted">Partenza: <?= e($ride['depart_at']) ?></div>
	</div>

	<form method="post" action="<?= e(url('?p=admin_ride_delete&id=' . (int)$ride['id'])) ?>" style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
		<?= csrf_field() ?>
		<button class="btn danger" type="submit">Sì, elimina</button>
		<a class="btn" href="<?= e(url('?p=admin_rides')) ?>">No, torna indietro</a>
	</form>
</div>

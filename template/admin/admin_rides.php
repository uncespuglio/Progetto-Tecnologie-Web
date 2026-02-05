<?php /** @var array $rides */ ?>
<div class="card" style="margin-top:18px;">
	<div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:flex-end;">
		<div>
			<p class="pill">Admin</p>
			<h1 class="title" style="margin-top:6px;">Passaggi</h1>
			<p class="subtitle">Modifica o elimina annunci pubblicati dagli utenti.</p>
		</div>
		<div>
			<a class="btn primary" href="<?= e(url('?p=admin_ride_create')) ?>">+ Crea passaggio</a>
			<a class="btn" href="<?= e(url('?p=admin')) ?>">← Dashboard</a>
		</div>
	</div>

	<?php if (!$rides): ?>
		<p class="muted" style="margin-top:14px;">Nessun passaggio nel sistema.</p>
	<?php else: ?>
		<div class="table-wrap">
		<table class="table" style="min-width:920px;">
			<thead>
				<tr>
					<th>Tratta</th>
					<th>Partenza</th>
					<th>Posti</th>
					<th>Driver</th>
					<th>Azioni</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($rides as $r): ?>
				<tr>
					<td><a href="<?= e(url('?p=ride&id=' . (int)$r['id'])) ?>"><?= e($r['from_city']) ?> → <?= e($r['to_city']) ?></a></td>
					<td><?= e($r['depart_at']) ?></td>
					<td><?= e((string)$r['seats_available']) ?>/<?= e((string)$r['seats_total']) ?></td>
					<td class="muted"><?= e($r['driver_email']) ?></td>
					<td>
						<a class="btn" href="<?= e(url('?p=admin_ride_edit&id=' . (int)$r['id'])) ?>">Modifica</a>
						<a class="btn danger" href="<?= e(url('?p=admin_ride_delete&id=' . (int)$r['id'])) ?>">Elimina</a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		</div>
	<?php endif; ?>
</div>

<?php /** @var array $users */ ?>
<div class="card" style="margin-top:18px;">
	<div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:flex-end;">
		<div>
			<p class="pill">Admin</p>
			<h1 class="title" style="margin-top:6px;">Utenti</h1>
			<p class="subtitle">Gestisci profili, ruoli e statistiche.</p>
		</div>
		<div>
			<a class="btn" href="<?= e(url('?p=admin_feedback')) ?>">Recensioni</a>
			<a class="btn" href="<?= e(url('?p=admin')) ?>">← Dashboard</a>
		</div>
	</div>

	<?php if (!$users): ?>
		<p class="muted" style="margin-top:14px;">Nessun utente.</p>
	<?php else: ?>
		<div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
			<span class="pill">Totale: <?= e((string)count($users)) ?></span>
			<span class="muted" style="font-size:13px;">Suggerimento: gestisci le recensioni da “Recensioni”.</span>
		</div>

		<div style="margin-top:10px; overflow:auto; border-radius:12px;">
		<table class="table admin-users" style="min-width:980px;">
			<thead>
				<tr>
					<th>Utente</th>
					<th>Telefono</th>
					<th>Ruolo</th>
					<th>Stats</th>
					<th>Rating</th>
					<th>Azioni</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($users as $u): ?>
				<tr>
					<td>
						<?= e((string)$u['full_name']) ?>
						<div class="muted" style="font-size:12px; margin-top:4px;">
							<?= e((string)$u['email']) ?>
						</div>
					</td>
					<td class="muted"><?= e((string)($u['phone'] ?? '—')) ?></td>
					<td><span class="pill"><?= e((string)($u['role'] ?? 'user')) ?></span></td>
					<td class="muted">
						<?= e((string)($u['rides_count'] ?? 0)) ?> passaggi • <?= e((string)($u['requests_count'] ?? 0)) ?> richieste
					</td>
					<td>
						<?php
							$cnt = (int)($u['feedback_count'] ?? 0);
							$avg = ($u['avg_rating'] === null) ? null : (float)$u['avg_rating'];
							$label = driver_quality_label($avg, $cnt);
						?>
						<span class="pill"><?= e($label) ?></span>
						<div class="muted" style="font-size:12px; margin-top:4px;">
							<?= e((string)$cnt) ?> feedback
							<?php if ($avg !== null): ?> • media <?= e(number_format($avg, 2, ',', '')) ?>/5<?php endif; ?>
						</div>
					</td>
					<td>
						<div style="display:flex; gap:10px; flex-wrap:nowrap; align-items:center; white-space:nowrap;">
							<a class="btn" href="<?= e(url('?p=admin_user_edit&id=' . (int)$u['id'])) ?>">Modifica</a>
							<a class="btn danger" href="<?= e(url('?p=admin_user_delete&id=' . (int)$u['id'])) ?>">Elimina</a>
						</div>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		</div>
	<?php endif; ?>
</div>

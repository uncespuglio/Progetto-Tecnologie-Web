<?php /** @var array $user */ /** @var array $myRequests */ ?>
<div class="grid two">
	<div class="card">
		<h1 class="title">Profilo</h1>
		<p class="subtitle">Aggiorna i tuoi dati e (opzionale) la password.</p>

		<form class="form" method="post" action="<?= e(url('?p=profile')) ?>">
			<?= csrf_field() ?>
			<label>
				Email
				<input value="<?= e($user['email']) ?>" disabled>
			</label>
			<label>
				Nome e cognome
				<input name="full_name" required value="<?= e($user['full_name']) ?>">
			</label>
			<label>
				Università
				<input value="<?= e(unibo_university()) ?>" disabled>
			</label>
			<label>
				Telefono (opzionale)
				<input name="phone" inputmode="tel" value="<?= e((string)($user['phone'] ?? '')) ?>" placeholder="Es. +39 3...">
			</label>
			<label>
				Nuova password (opzionale)
				<input type="password" name="new_password" autocomplete="new-password" placeholder="Minimo 8 caratteri">
			</label>
			<button class="btn primary" type="submit">Salva modifiche</button>
		</form>
	</div>

	<div class="card">
		<h2 style="margin:0">Le mie richieste</h2>
		<p class="muted" style="margin-top:6px">Passaggi richiesti come passeggero.</p>
		<?php if (!$myRequests): ?>
			<p class="muted">Non hai ancora richiesto posti.</p>
		<?php else: ?>
			<table class="table">
				<thead>
					<tr>
						<th>Passaggio</th>
						<th>Stato</th>
						<th>Azioni</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($myRequests as $r): ?>
					<tr>
						<td>
							<a href="<?= e(url('?p=ride&id=' . (int)$r['ride_id'])) ?>"><?= e($r['from_city']) ?> → <?= e($r['to_city']) ?></a>
							<div class="muted" style="font-size:12px;"><?= e($r['depart_at']) ?> • Driver: <?= e($r['driver_name']) ?> (<?= e($r['driver_uni']) ?>)</div>
						</td>
						<td><span class="pill"><?= e($r['status']) ?></span></td>
						<td>
							<?php if ($r['status'] === 'pending' || $r['status'] === 'accepted'): ?>
								<form method="post" action="<?= e(url('?p=request_update')) ?>" style="display:inline">
									<?= csrf_field() ?>
									<input type="hidden" name="request_id" value="<?= e((string)$r['request_id']) ?>">
									<input type="hidden" name="action" value="cancel">
									<button class="btn danger" type="submit">Annulla</button>
								</form>
							<?php else: ?>
								<span class="muted">—</span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>

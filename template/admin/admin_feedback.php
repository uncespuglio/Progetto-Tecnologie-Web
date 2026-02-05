<?php /** @var array $users */ /** @var array $rides */ /** @var array $feedback */ ?>
<div class="card" style="margin-top:18px;">
	<div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:flex-end;">
		<div>
			<p class="pill">Admin</p>
			<h1 class="title" style="margin-top:6px;">Recensioni</h1>
			<p class="subtitle">Aggiungi/modifica/rimuovi feedback tra utenti (anche manuale, senza viaggio).</p>
		</div>
		<div>
			<a class="btn" href="<?= e(url('?p=admin')) ?>">← Dashboard</a>
		</div>
	</div>

	<div class="card" style="margin-top:14px; padding:14px; background: var(--panel2)">
		<h2 style="margin:0">Aggiungi recensione</h2>
		<form method="post" class="form" action="<?= e(url('?p=admin_feedback')) ?>" style="margin-top:10px;">
			<?= csrf_field() ?>
			<input type="hidden" name="op" value="add">
			<div class="row two">
				<label>
					Contesto
					<select name="context">
						<option value="manual">Manuale (senza viaggio)</option>
						<option value="ride">Legato a passaggio (ride_id)</option>
					</select>
				</label>
				<div>
					<label>
						Passaggio (ride_id)
						<select name="ride_id_select">
							<option value="0">(se manuale, lascia vuoto)</option>
							<?php foreach ($rides as $r): ?>
								<option value="<?= e((string)$r['id']) ?>">
									#<?= e((string)$r['id']) ?> • <?= e((string)$r['from_city']) ?> → <?= e((string)$r['to_city']) ?> • <?= e((string)$r['depart_at']) ?> • Driver: <?= e((string)$r['driver_name']) ?>
								</option>
							<?php endforeach; ?>
						</select>
					</label>
					<label class="muted" style="font-size:12px; margin-top:6px;">
						ID manuale (se non in lista)
						<input type="number" name="ride_id_manual" min="0" placeholder="Es. 12" style="max-width:140px;">
					</label>
				</div>
			</div>
			<div class="row two">
				<label>
					Da (autore)
					<select name="from_user_id" required>
						<option value="">Seleziona…</option>
						<?php foreach ($users as $u): ?>
							<option value="<?= e((string)$u['id']) ?>"><?= e((string)$u['full_name']) ?> • <?= e((string)$u['email']) ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>
					A (destinatario)
					<select name="to_user_id" required>
						<option value="">Seleziona…</option>
						<?php foreach ($users as $u): ?>
							<option value="<?= e((string)$u['id']) ?>"><?= e((string)$u['full_name']) ?> • <?= e((string)$u['email']) ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>
			<div class="row two">
				<label>
					Rating
					<select name="rating" required>
						<option value="" selected disabled>Seleziona…</option>
						<?php for ($i = 1; $i <= 5; $i++): ?>
							<option value="<?= $i ?>"><?= $i ?></option>
						<?php endfor; ?>
					</select>
				</label>
				<label>
					Commento (opzionale)
					<input name="comment" placeholder="Es. puntuale e gentile">
				</label>
			</div>
			<button class="btn primary" type="submit">Salva</button>
		</form>
	</div>

	<h2 style="margin-top:18px;">Elenco recensioni</h2>
	<p class="muted" style="margin-top:6px;">Totale: <?= e((string)count($feedback)) ?></p>

	<?php if (empty($feedback)): ?>
		<p class="muted">Nessuna recensione.</p>
	<?php else: ?>
		<table class="table" style="margin-top:10px;">
			<thead>
				<tr>
					<th>Destinatario</th>
					<th>Autore</th>
					<th>Contesto</th>
					<th>Rating</th>
					<th>Commento</th>
					<th>Azioni</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($feedback as $f): ?>
				<tr>
					<td>
						<?= e((string)($f['to_name'] ?? '')) ?>
						<div class="muted" style="font-size:12px; margin-top:4px;"><?= e((string)($f['to_email'] ?? '')) ?></div>
					</td>
					<td>
						<?= e((string)($f['from_name'] ?? '')) ?>
						<div class="muted" style="font-size:12px; margin-top:4px;"><?= e((string)($f['from_email'] ?? '')) ?></div>
					</td>
					<td class="muted">
						<?php if ((string)($f['context'] ?? '') === 'ride' && !empty($f['ride_id'])): ?>
							Ride #<?= e((string)$f['ride_id']) ?>
							<?php if (!empty($f['from_city']) && !empty($f['to_city'])): ?>
								<div style="font-size:12px; margin-top:4px;">
									<?= e((string)$f['from_city']) ?> → <?= e((string)$f['to_city']) ?> • <?= e((string)($f['depart_at'] ?? '')) ?>
								</div>
							<?php endif; ?>
						<?php else: ?>
							Manuale
						<?php endif; ?>
					</td>
					<td>
						<form method="post" action="<?= e(url('?p=admin_feedback')) ?>" style="display:flex; gap:8px; align-items:center;">
							<?= csrf_field() ?>
							<input type="hidden" name="op" value="update">
							<input type="hidden" name="feedback_id" value="<?= e((string)$f['id']) ?>">
							<select name="rating" required>
								<option value="">Seleziona…</option>
								<?php for ($i = 1; $i <= 5; $i++): ?>
									<option value="<?= $i ?>" <?= ((int)$f['rating'] === $i) ? 'selected' : '' ?>><?= $i ?></option>
								<?php endfor; ?>
							</select>
							<button class="btn" type="submit">Aggiorna</button>
						</form>
					</td>
					<td>
						<form method="post" action="<?= e(url('?p=admin_feedback')) ?>" style="display:flex; gap:8px; align-items:center;">
							<?= csrf_field() ?>
							<input type="hidden" name="op" value="update">
							<input type="hidden" name="feedback_id" value="<?= e((string)$f['id']) ?>">
							<input type="hidden" name="rating" value="<?= e((string)$f['rating']) ?>">
							<input name="comment" value="<?= e((string)($f['comment'] ?? '')) ?>" placeholder="commento" style="min-width:180px;">
							<button class="btn" type="submit">Aggiorna</button>
						</form>
					</td>
					<td style="display:flex; gap:10px; flex-wrap:wrap;">
						<form method="post" action="<?= e(url('?p=admin_feedback')) ?>" style="display:inline">
							<?= csrf_field() ?>
							<input type="hidden" name="op" value="delete">
							<input type="hidden" name="feedback_id" value="<?= e((string)$f['id']) ?>">
							<button class="btn danger" type="submit" onclick="return confirm('Rimuovere questa recensione?')">Rimuovi</button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>

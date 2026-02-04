<?php /** @var array $requests */ /** @var array $rides */ /** @var array $users */ ?>
<div class="card" style="margin-top:18px;">
	<div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:flex-end;">
		<div>
			<p class="pill">Admin</p>
			<h1 class="title" style="margin-top:6px;">Prenotazioni</h1>
			<p class="subtitle">Elimina o aggiungi prenotazioni ai passaggi (ride_requests).</p>
		</div>
		<div>
			<a class="btn" href="<?= e(url('?p=admin')) ?>">← Dashboard</a>
		</div>
	</div>

	<div class="grid two" style="margin-top:14px; gap:14px;">
		<div class="card" style="margin:0;">
			<h2 style="margin:0;">Aggiungi prenotazione</h2>
			<p class="muted" style="margin-top:6px;">Se scegli <strong>accepted</strong>, viene occupato 1 posto.</p>

			<form class="form" method="post" action="<?= e(url('?p=admin_requests')) ?>" style="margin-top:10px;">
				<?= csrf_field() ?>
				<input type="hidden" name="op" value="add">

				<label>
					Passaggio
					<select name="ride_id" required>
						<option value="" disabled selected>Seleziona un passaggio</option>
						<?php foreach ($rides as $r): ?>
							<option value="<?= e((string)$r['id']) ?>">
								#<?= e((string)$r['id']) ?> • <?= e($r['from_city']) ?> → <?= e($r['to_city']) ?> • <?= e((string)$r['depart_at']) ?> • posti <?= e((string)$r['seats_available']) ?>/<?= e((string)$r['seats_total']) ?>
							</option>
						<?php endforeach; ?>
					</select>
				</label>

				<label>
					Passeggero
					<select name="passenger_id" required>
						<option value="" disabled selected>Seleziona un utente</option>
						<?php foreach ($users as $u): ?>
							<?php if ((string)($u['role'] ?? 'user') === 'admin') continue; ?>
							<option value="<?= e((string)$u['id']) ?>"><?= e($u['email']) ?><?php if (!empty($u['full_name'])): ?> • <?= e($u['full_name']) ?><?php endif; ?></option>
						<?php endforeach; ?>
					</select>
				</label>

				<div class="row two">
					<label>
						Stato
						<select name="status" required>
							<option value="pending">pending</option>
							<option value="accepted">accepted</option>
							<option value="rejected">rejected</option>
							<option value="canceled">canceled</option>
						</select>
					</label>
					<label>
						Messaggio (opzionale)
						<input name="message" placeholder="Nota interna o richiesta">
					</label>
				</div>

				<button class="btn primary" type="submit">Aggiungi</button>
			</form>
		</div>

		<div class="card" style="margin:0;">
			<h2 style="margin:0;">Legenda stati</h2>
			<p class="muted" style="margin-top:6px;"><strong>pending</strong>: in attesa • <strong>accepted</strong>: occupa posto • <strong>rejected</strong>: rifiutata • <strong>canceled</strong>: annullata</p>
			<p class="muted" style="margin-top:8px;">Puoi anche aggiornare lo stato; i posti vengono riallineati automaticamente quando entri/esci da <strong>accepted</strong>.</p>
		</div>
	</div>

	<h2 style="margin-top:18px;">Elenco prenotazioni</h2>

	<?php if (!$requests): ?>
		<p class="muted" style="margin-top:10px;">Nessuna prenotazione nel sistema.</p>
	<?php else: ?>
		<table class="table" style="margin-top:10px;">
			<thead>
				<tr>
					<th>Passaggio</th>
					<th>Passeggero</th>
					<th>Stato</th>
					<th>Creato</th>
					<th>Azioni</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($requests as $rr): ?>
				<tr>
					<td>
						<a href="<?= e(url('?p=ride&id=' . (int)$rr['ride_id'])) ?>">
							#<?= e((string)$rr['ride_id']) ?> • <?= e($rr['from_city']) ?> → <?= e($rr['to_city']) ?>
						</a>
						<div class="muted" style="font-size:12px; margin-top:4px;">Partenza: <?= e((string)$rr['depart_at']) ?> • Driver: <?= e((string)$rr['driver_email']) ?></div>
					</td>
					<td>
						<div><?= e((string)$rr['passenger_email']) ?></div>
						<div class="muted" style="font-size:12px; margin-top:4px;"><?= e((string)($rr['passenger_name'] ?? '')) ?></div>
					</td>
					<td><span class="pill"><?= e((string)$rr['status']) ?></span></td>
					<td class="muted"><?= e((string)$rr['created_at']) ?></td>
					<td style="display:flex; gap:10px; flex-wrap:wrap;">
						<?php if ((string)$rr['status'] === 'pending'): ?>
							<form method="post" action="<?= e(url('?p=admin_requests')) ?>">
								<?= csrf_field() ?>
								<input type="hidden" name="op" value="set_status">
								<input type="hidden" name="request_id" value="<?= e((string)$rr['id']) ?>">
								<input type="hidden" name="next_status" value="accepted">
								<button class="btn primary" type="submit">Accetta</button>
							</form>
							<form method="post" action="<?= e(url('?p=admin_requests')) ?>">
								<?= csrf_field() ?>
								<input type="hidden" name="op" value="set_status">
								<input type="hidden" name="request_id" value="<?= e((string)$rr['id']) ?>">
								<input type="hidden" name="next_status" value="rejected">
								<button class="btn" type="submit">Rifiuta</button>
							</form>
						<?php endif; ?>

						<?php if (in_array((string)$rr['status'], ['pending', 'accepted'], true)): ?>
							<form method="post" action="<?= e(url('?p=admin_requests')) ?>">
								<?= csrf_field() ?>
								<input type="hidden" name="op" value="set_status">
								<input type="hidden" name="request_id" value="<?= e((string)$rr['id']) ?>">
								<input type="hidden" name="next_status" value="canceled">
								<button class="btn" type="submit">Annulla</button>
							</form>
						<?php endif; ?>

						<form method="post" action="<?= e(url('?p=admin_requests')) ?>" onsubmit="return confirm('Eliminare questa prenotazione?');">
							<?= csrf_field() ?>
							<input type="hidden" name="op" value="delete">
							<input type="hidden" name="request_id" value="<?= e((string)$rr['id']) ?>">
							<button class="btn danger" type="submit">Elimina</button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>

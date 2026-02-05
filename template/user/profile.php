<?php /** @var array $user */ /** @var bool $editMode */ /** @var array $myRequestsUpcoming */ /** @var array $myTripsDone */ /** @var array $feedbackReceived */ /** @var array $feedbackSent */ /** @var float|null $avgRating */ /** @var int $feedbackCount */ ?>
<div class="grid two">
	<div class="card">
		<div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
			<div>
				<h1 class="title" style="margin:0;">Profilo</h1>
				<p class="subtitle" style="margin-top:6px;">Gestisci i tuoi dati e visualizza i feedback ricevuti.</p>
			</div>
			<div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap; justify-content:flex-end;">
				<?php if (!empty($feedbackCount) && $avgRating !== null): ?>
					<span class="pill">Media: <?= e(number_format($avgRating, 1, ',', '')) ?>/5 • <?= e((string)$feedbackCount) ?> feedback</span>
				<?php else: ?>
					<span class="pill">Nessun feedback</span>
				<?php endif; ?>
				<?php if (!empty($editMode)): ?>
					<a class="btn" href="<?= e(url('?p=profile')) ?>">Chiudi modifica</a>
				<?php else: ?>
					<a class="btn" href="<?= e(url('?p=profile&edit=1')) ?>">Modifica profilo</a>
				<?php endif; ?>
			</div>
		</div>

		<?php if (!empty($editMode)): ?>
			<form class="form" method="post" action="<?= e(url('?p=profile')) ?>" style="margin-top:12px;">
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
		<?php else: ?>
			<div class="muted" style="margin-top:12px; font-size:14px;">
				<div><strong style="color:var(--text)">Email:</strong> <?= e((string)$user['email']) ?></div>
				<div style="margin-top:6px;"><strong style="color:var(--text)">Nome:</strong> <?= e((string)$user['full_name']) ?></div>
				<?php if (!empty($user['phone'])): ?>
					<div style="margin-top:6px;"><strong style="color:var(--text)">Telefono:</strong> <?= e((string)$user['phone']) ?></div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<h2 style="margin-top:18px;">Feedback ricevuti</h2>
		<p class="muted" style="margin-top:6px;">Valutazioni ricevute dopo i passaggi effettuati.</p>
		<?php if (empty($feedbackReceived)): ?>
			<p class="muted">Nessun feedback ricevuto ancora.</p>
		<?php else: ?>
			<div class="table-wrap">
			<table class="table" style="min-width:900px; margin-top:10px;">
				<thead>
					<tr>
						<th>Da</th>
						<th>Voto</th>
						<th>Passaggio</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($feedbackReceived as $f): ?>
					<tr>
						<td>
							<?= e((string)($f['from_name'] ?? '')) ?>
							<div class="muted" style="font-size:12px; margin-top:4px;">
								<?= e((string)($f['from_email'] ?? '')) ?>
								<?php if (!empty($f['comment'])): ?> • <?= e((string)$f['comment']) ?><?php endif; ?>
							</div>
						</td>
						<td><span class="pill"><?= e((string)$f['rating']) ?>/5</span></td>
						<td>
							<?php if (!empty($f['ride_id']) && !empty($f['from_city']) && !empty($f['to_city'])): ?>
								<a href="<?= e(url('?p=ride&id=' . (int)$f['ride_id'])) ?>"><?= e((string)$f['from_city']) ?> → <?= e((string)$f['to_city']) ?></a>
								<div class="muted" style="font-size:12px; margin-top:4px;"><?= e((string)$f['depart_at']) ?></div>
							<?php else: ?>
								<span class="pill">Manuale</span>
								<div class="muted" style="font-size:12px; margin-top:4px;">Creato il <?= e((string)$f['created_at']) ?></div>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			</div>
		<?php endif; ?>

		<h2 style="margin-top:18px;">Feedback inviati</h2>
		<p class="muted" style="margin-top:6px;">I feedback che hai lasciato ad altri utenti.</p>
		<?php if (empty($feedbackSent)): ?>
			<p class="muted">Non hai ancora inviato feedback.</p>
		<?php else: ?>
				<div class="table-wrap">
				<table class="table" style="min-width:900px; margin-top:10px;">
				<thead>
					<tr>
						<th>A</th>
						<th>Voto</th>
						<th>Passaggio</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($feedbackSent as $f): ?>
					<tr>
						<td>
							<?= e((string)($f['to_name'] ?? '')) ?>
							<div class="muted" style="font-size:12px; margin-top:4px;">
								<?= e((string)($f['to_email'] ?? '')) ?>
								<?php if (!empty($f['comment'])): ?> • <?= e((string)$f['comment']) ?><?php endif; ?>
							</div>
						</td>
						<td><span class="pill"><?= e((string)$f['rating']) ?>/5</span></td>
						<td>
							<?php if (!empty($f['ride_id']) && !empty($f['from_city']) && !empty($f['to_city'])): ?>
								<a href="<?= e(url('?p=ride&id=' . (int)$f['ride_id'])) ?>"><?= e((string)$f['from_city']) ?> → <?= e((string)$f['to_city']) ?></a>
								<div class="muted" style="font-size:12px; margin-top:4px;"><?= e((string)$f['depart_at']) ?></div>
							<?php else: ?>
								<span class="pill">Manuale</span>
								<div class="muted" style="font-size:12px; margin-top:4px;">Creato il <?= e((string)$f['created_at']) ?></div>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
				</div>
		<?php endif; ?>
	</div>

	<div class="card">
		<h2 style="margin:0">Le mie richieste (in programma)</h2>
		<p class="muted" style="margin-top:6px">Passaggi richiesti come passeggero (futuri).</p>
		<?php if (!$myRequestsUpcoming): ?>
			<p class="muted">Non hai ancora richiesto posti.</p>
		<?php else: ?>
			<div class="table-wrap">
			<table class="table" style="min-width:920px;">
				<thead>
					<tr>
						<th>Passaggio</th>
						<th>Stato</th>
						<th>Azioni</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($myRequestsUpcoming as $r): ?>
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
			</div>
		<?php endif; ?>

		<h2 style="margin-top:18px;">Storico (viaggi effettuati)</h2>
		<p class="muted" style="margin-top:6px">Solo richieste <strong>accepted</strong> con partenza nel passato.</p>
		<?php if (!$myTripsDone): ?>
			<p class="muted">Nessun viaggio effettuato ancora.</p>
		<?php else: ?>
			<div class="table-wrap">
			<table class="table" style="min-width:920px;">
				<thead>
					<tr>
						<th>Passaggio</th>
						<th>Stato</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($myTripsDone as $r): ?>
					<tr>
						<td>
							<a href="<?= e(url('?p=ride&id=' . (int)$r['ride_id'])) ?>"><?= e($r['from_city']) ?> → <?= e($r['to_city']) ?></a>
							<div class="muted" style="font-size:12px;"><?= e($r['depart_at']) ?> • Driver: <?= e($r['driver_name']) ?> (<?= e($r['driver_uni']) ?>)</div>
						</td>
						<td><span class="pill"><?= e($r['status']) ?></span></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			</div>
		<?php endif; ?>
	</div>
</div>

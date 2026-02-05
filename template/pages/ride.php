<?php /** @var array $ride */ /** @var array|null $user */ /** @var bool $isDriver */ /** @var bool $isPast */ /** @var array|null $myRequest */ /** @var array $stops */ /** @var array $rideRequests */ /** @var array $travelers */ /** @var array $feedbackGivenByMe */ /** @var array|null $myFeedbackToDriver */ /** @var array|null $feedbackFromDriver */ ?>
<div class="grid two">
	<div class="card">
		<p class="pill">Passaggio</p>
		<?php $mapsUrl = google_maps_directions_url((string)$ride['from_city'], (string)$ride['to_city'], $stops ?? []); ?>
		<h1 class="title">
			<a href="<?= e($mapsUrl) ?>" target="_blank" rel="noopener" style="color:inherit; text-decoration:none;">
				<?= e($ride['from_city']) ?> → <?= e($ride['to_city']) ?>
			</a>
		</h1>
		<p class="subtitle">Partenza: <?= e($ride['depart_at']) ?></p>
		<?php if (!empty($isPast)): ?>
			<div class="alert" style="margin-top:12px;">
				Viaggio effettuato (passaggio nel passato).
			</div>
		<?php endif; ?>
		<?php if (!empty($stops)): ?>
			<div style="margin-top:10px; display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
				<span class="muted" style="font-size:12px;">Percorso:</span>
				<a class="pill" href="<?= e($mapsUrl) ?>" target="_blank" rel="noopener" style="text-decoration:none; color:inherit;">
					<?= e($ride['from_city']) ?>
				</a>
				<?php foreach ($stops as $c): ?>
					<span class="muted" aria-hidden="true">→</span>
					<a class="pill" href="<?= e($mapsUrl) ?>" target="_blank" rel="noopener" style="text-decoration:none; color:inherit;">
						<?= e($c) ?>
					</a>
				<?php endforeach; ?>
				<span class="muted" aria-hidden="true">→</span>
				<a class="pill" href="<?= e($mapsUrl) ?>" target="_blank" rel="noopener" style="text-decoration:none; color:inherit;">
					<?= e($ride['to_city']) ?>
				</a>
			</div>
		<?php endif; ?>

		<div class="muted" style="margin-top:10px; font-size:12px;">
			<a href="<?= e($mapsUrl) ?>" target="_blank" rel="noopener">Apri percorso su Google Maps (campus UNIBO)</a>
		</div>

		<div style="margin-top:12px; display:grid; gap:10px;">
			<?php
				$avg = isset($ride['driver_avg_rating']) ? (float)$ride['driver_avg_rating'] : null;
				$cnt = (int)($ride['driver_feedback_count'] ?? 0);
				$label = driver_quality_label($ride['driver_avg_rating'] === null ? null : $avg, $cnt);
			?>
			<div class="muted">Driver: <strong style="color:var(--text)"><?= e($ride['driver_name']) ?></strong> • <?= e($label) ?></div>
			<div>Posti disponibili: <span class="pill"><?= e((string)$ride['seats_available']) ?></span></div>
			<?php if (!empty($ride['notes'])): ?>
				<div class="card" style="padding:12px; background: var(--panel2)">
					<div class="muted" style="font-size:12px">Note</div>
					<div><?= nl2br(e((string)$ride['notes'])) ?></div>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="card">
		<h2 style="margin:0">Azioni</h2>
		<?php if (!$user): ?>
			<p class="muted" style="margin-top:10px">Per richiedere un posto devi fare login.</p>
			<a class="btn primary" href="<?= e(url('?p=login')) ?>">Login</a>
		<?php elseif ($isDriver): ?>
			<?php if (!empty($isPast)): ?>
				<p class="muted" style="margin-top:10px">Sei il driver di questo passaggio (storico).</p>
				<h3 style="margin-top:12px;">Chi ha viaggiato con te</h3>
				<?php if (empty($travelers)): ?>
					<p class="muted" style="margin-top:8px;">Nessun passeggero (nessuna richiesta accettata).</p>
				<?php else: ?>
					<table class="table" style="margin-top:8px;">
						<thead>
							<tr>
								<th>Studente</th>
								<th>Contatto</th>
								<th>Feedback (1–5)</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($travelers as $t): ?>
							<?php $pid = (int)($t['passenger_id'] ?? 0); $existing = (int)($feedbackGivenByMe[$pid] ?? 0); ?>
							<tr>
								<td><?= e((string)($t['passenger_name'] ?? '')) ?></td>
								<td class="muted">
									<?= e((string)($t['passenger_email'] ?? '')) ?>
									<?php if (!empty($t['passenger_phone'])): ?> • <?= e((string)$t['passenger_phone']) ?><?php endif; ?>
								</td>
								<td>
									<form method="post" action="<?= e(url('?p=feedback_submit')) ?>" style="display:flex; gap:8px; align-items:center;">
										<?= csrf_field() ?>
										<input type="hidden" name="ride_id" value="<?= e((string)$ride['id']) ?>">
										<input type="hidden" name="to_user_id" value="<?= e((string)$pid) ?>">
										<select name="rating" required>
											<option value="" <?= $existing > 0 ? '' : 'selected' ?> disabled>Seleziona…</option>
											<?php for ($i = 1; $i <= 5; $i++): ?>
												<option value="<?= $i ?>" <?= ($existing === $i) ? 'selected' : '' ?>><?= $i ?></option>
											<?php endfor; ?>
										</select>
										<button class="btn" type="submit"><?= $existing > 0 ? 'Aggiorna' : 'Salva' ?></button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			<?php else: ?>
				<p class="muted" style="margin-top:10px">Sei il driver di questo passaggio.</p>
				<p class="muted" style="margin-top:6px;">Richieste per questo passaggio:</p>
				<?php if (empty($rideRequests)): ?>
					<p class="muted" style="margin-top:8px;">Nessuna richiesta al momento.</p>
				<?php else: ?>
					<table class="table" style="margin-top:10px;">
						<thead>
							<tr>
								<th>Studente</th>
								<th>Stato</th>
								<th>Azioni</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($rideRequests as $req): ?>
							<tr>
								<td>
									<?= e((string)($req['passenger_name'] ?? '')) ?>
									<div class="muted" style="font-size:12px; margin-top:4px;">
										<?= e((string)($req['passenger_email'] ?? '')) ?>
										<?php if (!empty($req['message'])): ?> • Msg: <?= e((string)$req['message']) ?><?php endif; ?>
									</div>
								</td>
								<td><span class="pill"><?= e((string)$req['status']) ?></span></td>
								<td>
									<?php if ((string)$req['status'] === 'pending'): ?>
										<form method="post" action="<?= e(url('?p=request_update')) ?>" style="display:inline">
											<?= csrf_field() ?>
											<input type="hidden" name="request_id" value="<?= e((string)$req['request_id']) ?>">
											<input type="hidden" name="action" value="accept">
											<button class="btn primary" type="submit">Accetta</button>
										</form>
										<form method="post" action="<?= e(url('?p=request_update')) ?>" style="display:inline">
											<?= csrf_field() ?>
											<input type="hidden" name="request_id" value="<?= e((string)$req['request_id']) ?>">
											<input type="hidden" name="action" value="reject">
											<button class="btn danger" type="submit">Rifiuta</button>
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
				<div style="margin-top:12px;">
					<a class="btn" href="<?= e(url('?p=my_rides')) ?>">Vai a “I miei passaggi”</a>
				</div>
			<?php endif; ?>
		<?php else: ?>
			<?php
				$driverEmail = (string)($ride['driver_email'] ?? '');
				$subject = 'UniRide: info passaggio ' . (string)$ride['from_city'] . ' → ' . (string)$ride['to_city'] . ' (' . (string)$ride['depart_at'] . ')';
				$bodyLines = [
					'Ciao,',
					'',
					'Sono ' . (string)($user['full_name'] ?? $user['email'] ?? 'uno studente') . ' e vorrei informazioni su questo passaggio:',
					'- Tratta: ' . (string)$ride['from_city'] . ' → ' . (string)$ride['to_city'],
					'- Partenza: ' . (string)$ride['depart_at'],
					'- Link: ' . absolute_url('?p=ride&id=' . (int)$ride['id']),
					'',
					'Grazie!',
				];
				$mailto = 'mailto:' . $driverEmail;
				if ($driverEmail !== '') {
					$mailto .= '?' . http_build_query([
						'subject' => $subject,
						'body' => implode("\n", $bodyLines),
					], '', '&', PHP_QUERY_RFC3986);
				}
			?>
			<?php if ($driverEmail !== ''): ?>
				<a class="btn" href="<?= e($mailto) ?>">Contatta via email</a>
			<?php endif; ?>

			<?php if (!empty($isPast) && $myRequest && (string)$myRequest['status'] === 'accepted'): ?>
				<?php $existing = (int)(($myFeedbackToDriver['rating'] ?? 0)); ?>
				<h3 style="margin-top:14px;">Feedback al driver</h3>
				<form method="post" action="<?= e(url('?p=feedback_submit')) ?>" class="form" style="margin-top:10px;">
					<?= csrf_field() ?>
					<input type="hidden" name="ride_id" value="<?= e((string)$ride['id']) ?>">
					<input type="hidden" name="to_user_id" value="<?= e((string)$ride['driver_id']) ?>">
					<label>
						Valutazione (1–5)
						<select name="rating" required>
								<option value="" <?= $existing > 0 ? '' : 'selected' ?> disabled>Seleziona…</option>
							<?php for ($i = 1; $i <= 5; $i++): ?>
								<option value="<?= $i ?>" <?= ($existing === $i) ? 'selected' : '' ?>><?= $i ?></option>
							<?php endfor; ?>
						</select>
					</label>
					<button class="btn primary" type="submit"><?= $existing > 0 ? 'Aggiorna feedback' : 'Invia feedback' ?></button>
				</form>
				<?php if (!empty($feedbackFromDriver) && isset($feedbackFromDriver['rating'])): ?>
					<p class="muted" style="margin-top:10px;">Feedback ricevuto dal driver: <span class="pill"><?= e((string)$feedbackFromDriver['rating']) ?>/5</span></p>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ($myRequest): ?>
				<p class="muted" style="margin-top:10px">Hai già una richiesta: <span class="pill"><?= e($myRequest['status']) ?></span></p>
				<?php if (empty($isPast) && ($myRequest['status'] === 'pending' || $myRequest['status'] === 'accepted')): ?>
					<form method="post" action="<?= e(url('?p=request_update')) ?>" class="form" style="margin-top:10px;">
						<?= csrf_field() ?>
						<input type="hidden" name="request_id" value="<?= e((string)$myRequest['id']) ?>">
						<input type="hidden" name="action" value="cancel">
						<button class="btn danger" type="submit">Annulla richiesta</button>
					</form>
				<?php endif; ?>
			<?php else: ?>
				<?php if (!empty($isPast)): ?>
					<p class="muted" style="margin-top:10px">Questo passaggio è già passato.</p>
				<?php elseif ((int)$ride['seats_available'] <= 0): ?>
					<p class="muted" style="margin-top:10px">Posti esauriti.</p>
				<?php else: ?>
					<form method="post" action="<?= e(url('?p=request_seat')) ?>" class="form" style="margin-top:10px;">
						<?= csrf_field() ?>
						<input type="hidden" name="ride_id" value="<?= e((string)$ride['id']) ?>">
						<label>
							Messaggio (opzionale)
							<textarea name="message" placeholder="Es. ciao! posso salire a...?"></textarea>
						</label>
						<button class="btn primary" type="submit">Richiedi un posto</button>
					</form>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>

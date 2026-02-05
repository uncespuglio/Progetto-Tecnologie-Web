<?php /** @var array $ride */ /** @var array|null $user */ /** @var bool $isDriver */ /** @var array|null $myRequest */ /** @var array $stops */ ?>
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
			<a href="<?= e($mapsUrl) ?>" target="_blank" rel="noopener">Apri percorso su Google Maps (stazioni)</a>
		</div>

		<div style="margin-top:12px; display:grid; gap:10px;">
			<div class="muted">Driver: <strong style="color:var(--text)"><?= e($ride['driver_name']) ?></strong> • <?= e($ride['university']) ?></div>
			<div>Posti disponibili: <span class="pill"><?= e((string)$ride['seats_available']) ?></span></div>
			<div>Prezzo: <span class="pill"><?= e(number_format(((int)$ride['price_cents'])/100, 2, ',', '.')) ?>€</span></div>
			<?php if (!empty($ride['notes'])): ?>
				<div class="card" style="padding:12px; background: rgba(15,23,42,.55)">
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
			<p class="muted" style="margin-top:10px">Sei il driver di questo passaggio.</p>
			<a class="btn" href="<?= e(url('?p=my_rides')) ?>">Gestisci richieste</a>
		<?php else: ?>
			<?php if ($myRequest): ?>
				<p class="muted" style="margin-top:10px">Hai già una richiesta: <span class="pill"><?= e($myRequest['status']) ?></span></p>
				<?php if ($myRequest['status'] === 'pending' || $myRequest['status'] === 'accepted'): ?>
					<form method="post" action="<?= e(url('?p=request_update')) ?>" class="form" style="margin-top:10px;">
						<?= csrf_field() ?>
						<input type="hidden" name="request_id" value="<?= e((string)$myRequest['id']) ?>">
						<input type="hidden" name="action" value="cancel">
						<button class="btn danger" type="submit">Annulla richiesta</button>
					</form>
				<?php endif; ?>
			<?php else: ?>
				<?php if ((int)$ride['seats_available'] <= 0): ?>
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

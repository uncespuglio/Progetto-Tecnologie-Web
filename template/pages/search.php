<?php /** @var string $from */ /** @var string $to */ /** @var string $date */ /** @var array $rides */ /** @var array $allowedCities */ ?>
<div class="grid two">
	<div class="card">
		<h1 class="title">Cerca passaggi</h1>
		<p class="subtitle">Solo UNIBO: Bologna, Forlì, Cesena, Ravenna, Rimini. La ricerca considera anche le <strong>tappe</strong>.</p>
		<form class="form" action="<?= e(url('?p=search')) ?>" method="get">
			<input type="hidden" name="p" value="search">
			<div class="row two">
				<label>
					Partenza
					<select name="from">
						<option value="">Qualsiasi</option>
						<?php foreach ($allowedCities as $c): ?>
							<option value="<?= e($c) ?>" <?= ($from === $c) ? 'selected' : '' ?>><?= e($c) ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>
					Arrivo
					<select name="to">
						<option value="">Qualsiasi</option>
						<?php foreach ($allowedCities as $c): ?>
							<option value="<?= e($c) ?>" <?= ($to === $c) ? 'selected' : '' ?>><?= e($c) ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>
			<div class="row two">
				<label>
					Data
					<input type="date" name="date" value="<?= e($date) ?>">
				</label>
				<label>
					&nbsp;
					<button class="btn primary" type="submit">Cerca</button>
				</label>
			</div>
		</form>
	</div>

	<div class="card">
		<h2 style="margin:0">Risultati</h2>
		<p class="muted" style="margin-top:6px">Trovati: <?= e((string)count($rides)) ?></p>
		<?php if (!$rides): ?>
			<p class="muted">Nessun passaggio corrisponde ai filtri.</p>
		<?php else: ?>
			<div class="table-wrap">
			<table class="table" style="min-width:920px;">
				<thead>
					<tr>
						<th>Tratta</th>
						<th>Partenza</th>
						<th>Posti</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($rides as $r): ?>
					<tr>
						<td>
							<a href="<?= e(url('?p=ride&id=' . (int)$r['id'])) ?>">
								<?= e($r['from_city']) ?> → <?= e($r['to_city']) ?>
							</a>
							<?php
								$avg = isset($r['driver_avg_rating']) ? (float)$r['driver_avg_rating'] : null;
								$cnt = (int)($r['driver_feedback_count'] ?? 0);
								$label = driver_quality_label($r['driver_avg_rating'] === null ? null : $avg, $cnt);
							?>
							<div class="muted" style="font-size:12px;">Driver: <?= e($r['driver_name'] ?? '') ?> • <?= e($label) ?></div>
						</td>
						<td><?= e($r['depart_at']) ?></td>
						<td><?= e((string)$r['seats_available']) ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			</div>
		<?php endif; ?>
	</div>
</div>

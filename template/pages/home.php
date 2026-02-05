<?php /** @var array|null $user */ /** @var array $rides */ /** @var array $allowedCities */ ?>
<div class="grid two">
	<div class="card">
		<p class="pill">Solo UNIBO</p>
		<h1 class="title">Passaggi tra sedi UNIBO</h1>
		<p class="subtitle">Bologna, Forlì, Cesena, Ravenna, Rimini. Cerca un passaggio o pubblica il tuo.</p>

		<div class="grid" style="margin-top:14px;">
			<form class="form" action="<?= e(url('?p=search')) ?>" method="get">
				<input type="hidden" name="p" value="search">
				<div class="row two">
					<label>
						Partenza
						<select name="from">
							<option value="">Qualsiasi</option>
							<?php foreach ($allowedCities as $c): ?>
								<option value="<?= e($c) ?>"><?= e($c) ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<label>
						Arrivo
						<select name="to">
							<option value="">Qualsiasi</option>
							<?php foreach ($allowedCities as $c): ?>
								<option value="<?= e($c) ?>"><?= e($c) ?></option>
							<?php endforeach; ?>
						</select>
					</label>
				</div>
				<div class="row two">
					<label>
						Data (opzionale)
						<input type="date" name="date">
					</label>
					<label>
						&nbsp;
						<button class="btn primary" type="submit">Cerca passaggi</button>
					</label>
				</div>
			</form>
			<div>
				<?php if ($user): ?>
					<a class="btn primary" href="<?= e(url('?p=ride_create')) ?>">Pubblica un passaggio</a>
				<?php else: ?>
					<a class="btn" href="<?= e(url('?p=register')) ?>">Registrati per pubblicare</a>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="card">
		<h2 style="margin:0">Prossimi passaggi</h2>
		<p class="muted" style="margin-top:6px">Ultimi annunci (demo).</p>

		<?php if (!$rides): ?>
			<p class="muted">Ancora nessun passaggio pubblicato.</p>
		<?php else: ?>
			<table class="table">
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
							<div class="muted" style="font-size:12px;">Driver: <?= e($r['driver_name'] ?? '') ?> • <?= e($r['university'] ?? '') ?></div>
						</td>
						<td><?= e($r['depart_at']) ?></td>
						<td><?= e((string)$r['seats_available']) ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>

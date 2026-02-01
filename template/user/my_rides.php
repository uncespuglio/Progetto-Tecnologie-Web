<?php /** @var array $user */ /** @var array $rides */ /** @var array $requests */ ?>
<div class="grid two">
	<div class="card">
		<h1 class="title">I miei passaggi</h1>
		<p class="subtitle">Gestisci i tuoi annunci e le richieste.</p>

		<div style="margin-top:12px;">
			<a class="btn primary" href="<?= e(url('?p=ride_create')) ?>">Nuovo passaggio</a>
		</div>

		<?php if (!$rides): ?>
			<p class="muted" style="margin-top:14px;">Non hai ancora pubblicato passaggi.</p>
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
						<td><a href="<?= e(url('?p=ride&id=' . (int)$r['id'])) ?>"><?= e($r['from_city']) ?> → <?= e($r['to_city']) ?></a></td>
						<td><?= e($r['depart_at']) ?></td>
						<td><?= e((string)$r['seats_available']) ?>/<?= e((string)$r['seats_total']) ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

	<div class="card">
		<h2 style="margin:0">Richieste ricevute</h2>
		<?php if (!$requests): ?>
			<p class="muted" style="margin-top:10px;">Nessuna richiesta al momento.</p>
		<?php else: ?>
			<table class="table">
				<thead>
					<tr>
						<th>Passaggio</th>
						<th>Studente</th>
						<th>Stato</th>
						<th>Azioni</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($requests as $req): ?>
					<tr>
						<td>
							<a href="<?= e(url('?p=ride&id=' . (int)$req['ride_id'])) ?>">
								<?= e($req['from_city']) ?> → <?= e($req['to_city']) ?>
							</a>
							<div class="muted" style="font-size:12px;"><?= e($req['depart_at']) ?></div>
							<?php if (!empty($req['message'])): ?>
								<div class="muted" style="font-size:12px;">Msg: <?= e($req['message']) ?></div>
							<?php endif; ?>
						</td>
						<td>
							<?= e($req['passenger_name']) ?>
							<div class="muted" style="font-size:12px;"><?= e($req['passenger_uni']) ?></div>
						</td>
						<td><span class="pill"><?= e($req['status']) ?></span></td>
						<td>
							<?php if ($req['status'] === 'pending'): ?>
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
	</div>
</div>

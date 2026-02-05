<?php /** @var array $ride */ /** @var array $users */ /** @var string $departLocal */ /** @var array $allowedCities */ /** @var array $stops */ ?>
<div class="grid">
	<div class="card">
		<p class="pill">Admin</p>
		<h1 class="title">Crea passaggio</h1>
		<p class="subtitle">Crea un nuovo annuncio scegliendo un driver.</p>

		<form class="form" method="post" action="<?= e(url('?p=admin_ride_create')) ?>">
			<?= csrf_field() ?>

			<label>
				Driver
				<select name="driver_id" required>
					<option value="" disabled <?= empty($ride['driver_id']) ? 'selected' : '' ?>>Seleziona un driver</option>
					<?php foreach ($users as $u): ?>
						<option value="<?= e((string)$u['id']) ?>" <?= ((int)$u['id'] === (int)($ride['driver_id'] ?? 0)) ? 'selected' : '' ?>>
							<?= e($u['email']) ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>

			<div class="row two">
				<label>
					Partenza
					<select name="from_city" required>
						<option value="" disabled <?= empty($ride['from_city']) ? 'selected' : '' ?>>Seleziona una città</option>
						<?php foreach ($allowedCities as $c): ?>
							<option value="<?= e($c) ?>" <?= ((string)$ride['from_city'] === $c) ? 'selected' : '' ?>><?= e($c) ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>
					Arrivo
					<select name="to_city" required>
						<option value="" disabled <?= empty($ride['to_city']) ? 'selected' : '' ?>>Seleziona una città</option>
						<?php foreach ($allowedCities as $c): ?>
							<option value="<?= e($c) ?>" <?= ((string)$ride['to_city'] === $c) ? 'selected' : '' ?>><?= e($c) ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>

			<div class="card" style="padding:12px; background: var(--panel2)">
				<div class="muted" style="font-size:12px;">Tappe (opzionale)</div>
				<div class="muted" style="font-size:12px; margin-top:6px;">La ricerca considera anche le tappe (match per segmenti).</div>

				<div data-stops-root data-max-stops="5" style="margin-top:10px; display:grid; gap:10px;">
					<div data-stops-list style="display:grid; gap:10px;">
						<?php foreach (($stops ?? []) as $existing): ?>
							<div class="row two" data-stop-row>
								<label>
									Città tappa
									<select name="stops[]">
										<option value="">—</option>
										<?php foreach ($allowedCities as $c): ?>
											<option value="<?= e($c) ?>" <?= ((string)$existing === $c) ? 'selected' : '' ?>><?= e($c) ?></option>
										<?php endforeach; ?>
									</select>
								</label>
								<label>
									&nbsp;
									<button class="btn danger" type="button" data-remove-stop>Rimuovi</button>
								</label>
							</div>
						<?php endforeach; ?>
					</div>
					<div style="display:flex; gap:10px; flex-wrap:wrap;">
						<button class="btn" type="button" data-add-stop>Aggiungi tappa</button>
					</div>

					<template data-stop-template>
						<div class="row two" data-stop-row>
							<label>
								Città tappa
								<select name="stops[]">
									<option value="">—</option>
									<?php foreach ($allowedCities as $c): ?>
										<option value="<?= e($c) ?>"><?= e($c) ?></option>
									<?php endforeach; ?>
								</select>
							</label>
							<label>
								&nbsp;
								<button class="btn danger" type="button" data-remove-stop>Rimuovi</button>
							</label>
						</div>
					</template>
				</div>
			</div>

			<label>Data e ora<input type="datetime-local" name="depart_at" required value="<?= e((string)$departLocal) ?>"></label>

			<div class="row two">
				<label>Posti totali<input type="number" min="1" max="8" name="seats_total" required value="<?= e((string)($ride['seats_total'] ?? 3)) ?>"></label>
				<label>Posti disponibili<input type="number" min="0" max="8" name="seats_available" required value="<?= e((string)($ride['seats_available'] ?? 3)) ?>"></label>
			</div>

			<label>
				Note
				<textarea name="notes"><?= e((string)($ride['notes'] ?? '')) ?></textarea>
			</label>

			<div style="display:flex; gap:10px; flex-wrap:wrap;">
				<button class="btn primary" type="submit">Crea</button>
				<a class="btn" href="<?= e(url('?p=admin_rides')) ?>">Annulla</a>
			</div>
		</form>
	</div>
</div>

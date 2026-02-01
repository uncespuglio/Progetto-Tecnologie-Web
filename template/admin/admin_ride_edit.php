<?php /** @var array $ride */ /** @var array $users */ /** @var string $departLocal */ /** @var array $allowedCities */ /** @var array $stops */ ?>
<div class="grid two">
	<div class="card">
		<p class="pill">Admin • CRUD</p>
		<h1 class="title">Modifica passaggio</h1>
		<p class="subtitle">Aggiorna i dati dell’annuncio.</p>

		<form class="form" method="post" action="<?= e(url('?p=admin_ride_edit&id=' . (int)$ride['id'])) ?>">
			<?= csrf_field() ?>
			<label>
				Driver
				<select name="driver_id" required>
					<option value="" disabled <?= empty($ride['driver_id']) ? 'selected' : '' ?>>Seleziona un driver</option>
					<?php foreach ($users as $u): ?>
						<option value="<?= e((string)$u['id']) ?>" <?= ((int)$u['id'] === (int)$ride['driver_id']) ? 'selected' : '' ?>>
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


			<div class="card" style="padding:12px; background: rgba(15,23,42,.55)">
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

			<div class="row two">
				<label>Data e ora<input type="datetime-local" name="depart_at" required value="<?= e($departLocal) ?>"></label>
				<label>Prezzo (EUR)<input name="price_eur" inputmode="decimal" value="<?= e(number_format(((int)$ride['price_cents'])/100, 2, ',', '.')) ?>"></label>
			</div>

			<div class="row two">
				<label>Posti totali<input type="number" min="1" max="8" name="seats_total" required value="<?= e((string)$ride['seats_total']) ?>"></label>
				<label>Posti disponibili<input type="number" min="0" max="8" name="seats_available" required value="<?= e((string)$ride['seats_available']) ?>"></label>
			</div>

			<label>
				Note
				<textarea name="notes"><?= e((string)($ride['notes'] ?? '')) ?></textarea>
			</label>

			<div style="display:flex; gap:10px; flex-wrap:wrap;">
				<button class="btn primary" type="submit">Salva</button>
				<a class="btn" href="<?= e(url('?p=admin_rides')) ?>">Annulla</a>
			</div>
		</form>
	</div>

	<div class="card">
		<h2 style="margin:0">Suggerimento rubric</h2>
		<p class="muted" style="margin-top:6px;">Questa schermata documenta chiaramente l’operazione <strong>Update</strong> del CRUD lato Admin.</p>
	</div>
</div>

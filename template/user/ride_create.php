<div class="grid">
	<div class="card">
		<h1 class="title">Pubblica passaggio</h1>
		<p class="subtitle">Crea un annuncio visibile agli altri studenti.</p>

		<form class="form" method="post" action="<?= e(url('?p=ride_create')) ?>">
			<?= csrf_field() ?>
			<div class="row two">
				<label>
					Città di partenza
					<select name="from_city" required>
						<option value="" selected disabled>Seleziona una città</option>
						<?php foreach (($allowedCities ?? allowed_cities()) as $c): ?>
							<option value="<?= e($c) ?>"><?= e($c) ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>
					Città di arrivo
					<select name="to_city" required>
						<option value="" selected disabled>Seleziona una città</option>
						<?php foreach (($allowedCities ?? allowed_cities()) as $c): ?>
							<option value="<?= e($c) ?>"><?= e($c) ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>

			<div class="card" style="padding:12px; background: var(--panel2)">
				<div class="muted" style="font-size:12px;">Tappe (opzionale)</div>
				<div class="muted" style="font-size:12px; margin-top:6px;">La ricerca considera anche le tappe (match per segmenti).</div>

				<div data-stops-root data-max-stops="5" style="margin-top:10px; display:grid; gap:10px;">
					<div data-stops-list style="display:grid; gap:10px;"></div>
					<div style="display:flex; gap:10px; flex-wrap:wrap;">
						<button class="btn" type="button" data-add-stop>Aggiungi tappa</button>
					</div>

					<template data-stop-template>
						<div class="row two" data-stop-row>
							<label>
								Città tappa
								<select name="stops[]">
									<option value="">—</option>
									<?php foreach (($allowedCities ?? allowed_cities()) as $c): ?>
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
				<label>
					Data e ora
					<input type="datetime-local" name="depart_at" required>
				</label>
				<label>
					Posti disponibili
					<input type="number" min="1" max="8" name="seats_total" value="3" required>
				</label>
			</div>
			<div style="margin-top:10px;">
				<button class="btn primary" type="submit">Pubblica</button>
			</div>
			<label>
				Note (opzionale)
				<textarea name="notes" placeholder="Es. solo zaini, no valigie grandi; punto di ritrovo..."> </textarea>
			</label>
		</form>
	</div>
</div>

<?php /** @var array $u */ ?>
<div class="grid two">
	<div class="card">
		<p class="pill">Admin</p>
		<h1 class="title">Modifica utente</h1>
		<p class="subtitle"><?= e($u['email']) ?></p>

		<form class="form" method="post" action="<?= e(url('?p=admin_user_edit&id=' . (int)$u['id'])) ?>">
			<?= csrf_field() ?>
			<label>
				Nome e cognome
				<input name="full_name" required value="<?= e($u['full_name']) ?>">
			</label>
			<label>
				Università
				<input value="<?= e(unibo_university()) ?>" disabled>
			</label>
			<label>
				Telefono
				<input name="phone" inputmode="tel" value="<?= e((string)($u['phone'] ?? '')) ?>">
			</label>
			<label>
				Ruolo
				<select name="role" required>
					<option value="" disabled>Seleziona un ruolo</option>
					<option value="user" <?= ((string)($u['role'] ?? 'user') === 'user') ? 'selected' : '' ?>>user</option>
					<option value="admin" <?= ((string)($u['role'] ?? 'user') === 'admin') ? 'selected' : '' ?>>admin</option>
				</select>
			</label>
			<label>
				Reset password (opzionale)
				<input type="password" name="reset_password" autocomplete="new-password" placeholder="Minimo 8 caratteri">
			</label>

			<div style="display:flex; gap:10px; flex-wrap:wrap;">
				<button class="btn primary" type="submit">Salva</button>
				<a class="btn" href="<?= e(url('?p=admin_users')) ?>">Annulla</a>
			</div>
		</form>
	</div>

	<div class="card">
		<h2 style="margin:0">Nota rubric</h2>
		<p class="muted" style="margin-top:6px;">Questa schermata copre l’aggiornamento profilo lato Admin e la gestione ruoli (contenuti/utenti).</p>
	</div>
</div>

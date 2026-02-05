<div class="grid two">
	<div class="card">
		<h1 class="title">Registrazione</h1>
		<p class="subtitle">Crea un account per pubblicare e richiedere passaggi.</p>

		<form class="form" method="post" action="<?= e(url('?p=register')) ?>">
			<?= csrf_field() ?>
			<label>
				Nome e cognome
				<input name="full_name" required autocomplete="name" placeholder="Es. Sofia Rossi">
			</label>
			<label>
				Università
				<input value="<?= e(unibo_university()) ?>" disabled>
			</label>
			<label>
				Telefono (opzionale)
				<input name="phone" inputmode="tel" placeholder="Es. +39 3...">
			</label>
			<label>
				Email
				<input type="email" name="email" required autocomplete="email" placeholder="nome.cognome@studenti...">
			</label>
			<label>
				Password
				<input type="password" name="password" required autocomplete="new-password" placeholder="Minimo 8 caratteri">
			</label>
			<button class="btn primary" type="submit">Crea account</button>
		</form>

		<p class="muted" style="margin-top:12px;">Hai già un account? <a href="<?= e(url('?p=login')) ?>">Login</a></p>
	</div>

	<div class="card">
		<h2 style="margin:0">Solo UNIBO</h2>
		<p class="muted" style="margin-top:6px;">Il progetto è limitato a studenti UNIBO. Usa un'email <code>@studio.unibo.it</code>.</p>
	</div>
</div>

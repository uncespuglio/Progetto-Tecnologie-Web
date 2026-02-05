<div class="grid">
	<div class="card">
		<h1 class="title">Login</h1>
		<p class="subtitle">Accedi con l’email universitaria (o quella che hai registrato).</p>
		<form class="form" method="post" action="<?= e(url('?p=login')) ?>">
			<?= csrf_field() ?>
			<label>
				Email
				<input type="email" name="email" required autocomplete="email">
			</label>
			<label>
				Password
				<input type="password" name="password" required autocomplete="current-password">
			</label>
			<button class="btn primary" type="submit">Entra</button>
		</form>
		<p class="muted" style="margin-top:12px;">Non hai un account? <a href="<?= e(url('?p=register')) ?>">Registrati</a></p>
	</div>
</div>

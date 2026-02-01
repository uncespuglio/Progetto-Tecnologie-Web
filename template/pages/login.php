<div class="grid two">
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

	<div class="card">
		<h2 style="margin:0">Credenziali demo</h2>
		<p class="muted" style="margin-top:6px;">Password per tutti: <code>Password123!</code></p>
		<ul class="muted" style="margin-top:10px; line-height:1.6">
			<li><strong>Admin</strong>: admin@unibo.test</li>
			<li>User: sofia.rossi@unibo.test</li>
			<li>User: marco.bianchi@unibo.test</li>
			<li>User: giulia.conti@unibo.test</li>
			<li>User: luca.ferretti@unibo.test</li>
		</ul>
		<p class="muted" style="margin-top:10px;">Registrazione consentita solo con email UNIBO (<code>@studio.unibo.it</code>) o demo (<code>@unibo.test</code>).</p>
	</div>
</div>

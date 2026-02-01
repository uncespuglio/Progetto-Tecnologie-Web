<?php /** @var string $page */ ?>
<div class="card" style="margin-top:18px;">
	<h1 class="title">404</h1>
	<p class="subtitle">Pagina non trovata: <span class="pill"><?= e($page) ?></span></p>
	<p style="margin-top:14px;"><a class="btn" href="<?= e(url('?p=home')) ?>">Torna alla home</a></p>
</div>

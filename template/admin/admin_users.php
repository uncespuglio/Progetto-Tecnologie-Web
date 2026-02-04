<?php /** @var array $users */ ?>
<div class="card" style="margin-top:18px;">
	<div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:flex-end;">
		<div>
			<p class="pill">Admin</p>
			<h1 class="title" style="margin-top:6px;">Utenti</h1>
			<p class="subtitle">Gestisci profili e ruoli.</p>
		</div>
		<div>
			<a class="btn" href="<?= e(url('?p=admin')) ?>">← Dashboard</a>
		</div>
	</div>

	<?php if (!$users): ?>
		<p class="muted" style="margin-top:14px;">Nessun utente.</p>
	<?php else: ?>
		<table class="table">
			<thead>
				<tr>
					<th>Email</th>
					<th>Nome</th>
					<th>Università</th>
					<th>Ruolo</th>
					<th>Azioni</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($users as $u): ?>
				<tr>
					<td><?= e($u['email']) ?></td>
					<td><?= e($u['full_name']) ?></td>
					<td class="muted"><?= e($u['university']) ?></td>
					<td><span class="pill"><?= e((string)($u['role'] ?? 'user')) ?></span></td>
					<td style="display:flex; gap:10px; flex-wrap:wrap;">
						<a class="btn" href="<?= e(url('?p=admin_user_edit&id=' . (int)$u['id'])) ?>">Modifica</a>
						<a class="btn danger" href="<?= e(url('?p=admin_user_delete&id=' . (int)$u['id'])) ?>">Elimina</a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>

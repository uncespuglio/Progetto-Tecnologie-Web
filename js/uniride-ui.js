function initStopsWidget(root) {
	const list = root.querySelector('[data-stops-list]');
	const template = root.querySelector('template[data-stop-template]');
	const addBtn = root.querySelector('[data-add-stop]');
	const maxStops = Number(root.getAttribute('data-max-stops') || '5');

	if (!list || !template || !addBtn) return;

	function stopsCount() {
		return list.querySelectorAll('[data-stop-row]').length;
	}

	function updateAddState() {
		addBtn.disabled = stopsCount() >= maxStops;
	}

	function addStopRow() {
		if (stopsCount() >= maxStops) return;
		const fragment = template.content.cloneNode(true);
		list.appendChild(fragment);
		updateAddState();
	}

	addBtn.addEventListener('click', () => addStopRow());

	list.addEventListener('click', (e) => {
		const btn = e.target.closest('[data-remove-stop]');
		if (!btn) return;
		const row = btn.closest('[data-stop-row]');
		if (!row) return;
		row.remove();
		updateAddState();
	});

	if (stopsCount() === 0) {
		addStopRow();
	}
	updateAddState();
}

document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll('[data-stops-root]').forEach((root) => initStopsWidget(root));
});

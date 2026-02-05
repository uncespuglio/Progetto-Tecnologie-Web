(function () {
	'use strict';

	function ensureErrorEl(input, idSuffix) {
		const existingId = input.getAttribute('aria-describedby');
		if (existingId) {
			const existing = document.getElementById(existingId);
			if (existing) return existing;
		}

		const id = `field-error-${idSuffix}`;
		let errorEl = document.getElementById(id);
		if (!errorEl) {
			errorEl = document.createElement('div');
			errorEl.id = id;
			errorEl.className = 'field-error';
			errorEl.setAttribute('aria-live', 'polite');
			errorEl.setAttribute('aria-atomic', 'true');
			errorEl.hidden = true;

			const label = input.closest('label');
			if (label) {
				label.appendChild(errorEl);
			} else {
				input.insertAdjacentElement('afterend', errorEl);
			}
		}

		input.setAttribute('aria-describedby', id);
		return errorEl;
	}

	function setFieldError(input, errorEl, message) {
		const hasError = Boolean(message);
		input.setAttribute('aria-invalid', hasError ? 'true' : 'false');
		errorEl.textContent = message || '';
		errorEl.hidden = !hasError;
	}

	function isRegisterPage() {
		return Boolean(document.querySelector('form[action*="?p=register"]'));
	}

	function validateEmailValue(value) {
		const trimmed = String(value || '').trim();
		if (trimmed === '') return '';
		const lower = trimmed.toLowerCase();
		if (!lower.includes('@')) return 'Inserisci un\'email valida.';
		if (!lower.endsWith('@studio.unibo.it')) return 'Usa un\'email @studio.unibo.it.';
		return '';
	}

	document.addEventListener('DOMContentLoaded', () => {
		if (!isRegisterPage()) return;

		const form = document.querySelector('form[action*="?p=register"]');
		const emailInput = document.querySelector('input[name="email"]');
		if (!form || !emailInput) return;

		const errorEl = ensureErrorEl(emailInput, 'email');

		const run = () => {
			const msg = validateEmailValue(emailInput.value);
			setFieldError(emailInput, errorEl, msg);
			return !msg;
		};

		emailInput.addEventListener('input', run);
		emailInput.addEventListener('blur', run);

		form.addEventListener('submit', (e) => {
			if (!run()) {
				e.preventDefault();
				emailInput.focus();
			}
		});
	});
})();

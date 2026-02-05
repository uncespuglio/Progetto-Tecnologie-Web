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
			errorEl = document.createElement('span');
			errorEl.id = id;
			errorEl.className = 'field-error';
			errorEl.setAttribute('role', 'status');
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

	function validatePasswordValue(value) {
		const v = String(value || '');
		if (v.length === 0) return '';
		if (v.length < 8) return 'Minimo 8 caratteri.';
		return '';
	}

	document.addEventListener('DOMContentLoaded', () => {
		if (!isRegisterPage()) return;

		const form = document.querySelector('form[action*="?p=register"]');
		const pwdInput = document.querySelector('input[name="password"]');
		if (!form || !pwdInput) return;

		const errorEl = ensureErrorEl(pwdInput, 'password');

		const run = () => {
			const msg = validatePasswordValue(pwdInput.value);
			setFieldError(pwdInput, errorEl, msg);
			return !msg;
		};

		pwdInput.addEventListener('input', run);
		pwdInput.addEventListener('blur', run);

		form.addEventListener('submit', (e) => {
			if (!run()) {
				e.preventDefault();
				pwdInput.focus();
			}
		});
	});
})();

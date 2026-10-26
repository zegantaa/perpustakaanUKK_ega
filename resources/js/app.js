import './bootstrap';

document.querySelector('.menu-toggle')?.addEventListener('click', () => {
	document.querySelector('.sidebar')?.classList.toggle('open');
});

document.querySelector('.member-menu-toggle')?.addEventListener('click', () => {
	document.querySelector('.member-nav-links')?.classList.toggle('open');
});

document.querySelector('.member-profile-button')?.addEventListener('click', () => {
	document.querySelector('.member-profile-wrap')?.classList.toggle('open');
});

const borrowModal = document.querySelector('[data-borrow-modal]');
document.querySelector('[data-borrow-open]')?.addEventListener('click', () => {
	if (borrowModal) borrowModal.hidden = false;
});
document.querySelectorAll('[data-borrow-close]').forEach((button) => {
	button.addEventListener('click', () => {
		if (borrowModal) borrowModal.hidden = true;
	});
});
document.querySelector('[data-borrow-form]')?.addEventListener('submit', () => {
	const submitButton = document.querySelector('[data-borrow-submit]');
	if (submitButton) {
		submitButton.disabled = true;
		submitButton.textContent = 'Memproses...';
	}
});

document.querySelectorAll('[data-cancel-open]').forEach((button) => {
	button.addEventListener('click', () => {
		const modal = document.getElementById(button.dataset.cancelOpen);
		if (modal) modal.hidden = false;
	});
});
document.querySelectorAll('[data-cancel-close]').forEach((button) => {
	button.addEventListener('click', () => {
		button.closest('[data-cancel-modal]')?.setAttribute('hidden', '');
	});
});
document.querySelectorAll('[data-cancel-form]').forEach((form) => {
	form.addEventListener('submit', () => {
		const submitButton = form.querySelector('[data-cancel-submit]');
		if (submitButton) {
			submitButton.disabled = true;
			submitButton.textContent = 'Membatalkan...';
		}
	});
});

document.querySelectorAll('[data-cover-input]').forEach((input) => {
	input.addEventListener('change', () => {
		const file = input.files?.[0];
		const preview = document.querySelector('[data-cover-preview]');
		if (!file || !preview) return;
		const error = input.closest('label')?.querySelector('[data-cover-error]');
		if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
			input.value = '';
			preview.innerHTML = '<span>File tidak valid</span>';
			if (error) error.textContent = 'Format cover harus JPG, JPEG, PNG, atau WEBP.';
			return;
		}
		if (file.size > 2 * 1024 * 1024) {
			input.value = '';
			preview.innerHTML = '<span>Belum Ada Cover</span>';
			if (error) error.textContent = 'Ukuran cover maksimal 2 MB.';
			return;
		}
		if (error) error.textContent = '';
		const reader = new FileReader();
		reader.onload = (event) => { preview.innerHTML = `<img src="${event.target.result}" alt="Preview cover">`; };
		reader.readAsDataURL(file);
	});
});

document.querySelectorAll('[data-book-form]').forEach((form) => {
	form.addEventListener('submit', () => {
		const button = form.querySelector('button[type="submit"]');
		if (button) { button.disabled = true; button.textContent = 'Menyimpan...'; }
	});
});

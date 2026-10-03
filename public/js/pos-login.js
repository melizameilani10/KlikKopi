/**
 * Perkoci POS — Login Kasir
 *  - keypad PIN, indikator titik, show/hide PIN
 *  - validasi ringan di sisi klien (server tetap sumber kebenaran)
 *  - loading state pada tombol submit
 */
(() => {
    'use strict';

    const form = document.getElementById('pos-form');
    if (!form) return;

    const PIN_LENGTH = 6;

    const usernameInput = document.getElementById('username');
    const pinInput      = document.getElementById('pin');
    const toggleBtn     = document.getElementById('toggle-pin');
    const keypad        = document.getElementById('keypad');
    const dots          = Array.from(document.querySelectorAll('#pin-dots span'));
    const submitBtn     = document.getElementById('pos-submit');
    const submitLabel   = document.getElementById('pos-submit-label');

    /* ---------- Helper error ---------- */
    const errorEl = (name) => form.querySelector(`[data-error-for="${name}"]`);

    function showError(name, message, input) {
        const el = errorEl(name);
        if (el) { el.textContent = message; el.hidden = false; }
        if (input) { input.classList.add('is-invalid'); input.setAttribute('aria-invalid', 'true'); }
    }

    function clearError(name, input) {
        const el = errorEl(name);
        if (el) { el.textContent = ''; el.hidden = true; }
        if (input) { input.classList.remove('is-invalid'); input.removeAttribute('aria-invalid'); }
    }

    /* ---------- PIN ---------- */
    function renderDots() {
        const length = pinInput.value.length;
        dots.forEach((dot, index) => dot.classList.toggle('is-filled', index < length));
    }

    function setPin(value) {
        pinInput.value = value.replace(/\D/g, '').slice(0, PIN_LENGTH);
        renderDots();
        clearError('pin', pinInput);
    }

    pinInput.addEventListener('input', () => setPin(pinInput.value));

    keypad?.addEventListener('click', (event) => {
        const button = event.target.closest('button');
        if (!button) return;

        const { key, action } = button.dataset;

        if (key !== undefined) setPin(pinInput.value + key);
        else if (action === 'back') setPin(pinInput.value.slice(0, -1));
        else if (action === 'clear') setPin('');
    });

    toggleBtn?.addEventListener('click', () => {
        const reveal = pinInput.type === 'password';
        const useEl  = toggleBtn.querySelector('use');

        pinInput.type = reveal ? 'text' : 'password';
        toggleBtn.setAttribute('aria-pressed', String(reveal));
        toggleBtn.setAttribute('aria-label', reveal ? 'Sembunyikan PIN' : 'Tampilkan PIN');
        if (useEl) useEl.setAttribute('href', reveal ? '#i-eye-off' : '#i-eye');
    });

    usernameInput.addEventListener('input', () => clearError('username', usernameInput));
    form.querySelectorAll('input[name="shift"]').forEach((radio) => {
        radio.addEventListener('change', () => clearError('shift'));
    });

    /* ---------- Validasi ---------- */
    function validate() {
        let firstInvalid = null;

        if (!form.querySelector('input[name="shift"]:checked')) {
            showError('shift', 'Pilih shift kerja terlebih dahulu.');
            firstInvalid ??= form.querySelector('input[name="shift"]');
        } else {
            clearError('shift');
        }

        if (!usernameInput.value.trim()) {
            showError('username', 'ID Kasir wajib diisi.', usernameInput);
            firstInvalid ??= usernameInput;
        } else {
            clearError('username', usernameInput);
        }

        if (!pinInput.value) {
            showError('pin', 'PIN wajib diisi.', pinInput);
            firstInvalid ??= pinInput;
        } else if (pinInput.value.length !== PIN_LENGTH) {
            showError('pin', 'PIN harus terdiri dari 6 digit angka.', pinInput);
            firstInvalid ??= pinInput;
        } else {
            clearError('pin', pinInput);
        }

        if (firstInvalid) firstInvalid.focus();
        return !firstInvalid;
    }

    /* ---------- Loading state ---------- */
    function setLoading(isLoading) {
        submitBtn.disabled = isLoading;
        submitBtn.classList.toggle('is-loading', isLoading);
        submitBtn.setAttribute('aria-busy', String(isLoading));
        submitLabel.textContent = isLoading ? submitBtn.dataset.loadingText : submitBtn.dataset.defaultText;
    }

    form.addEventListener('submit', (event) => {
        if (form.dataset.submitting === 'true' || !validate()) {
            event.preventDefault();
            return;
        }

        form.dataset.submitting = 'true';
        setLoading(true);
    });

    // Pulihkan tombol saat halaman dibuka lagi lewat tombol Back (bfcache).
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            form.dataset.submitting = 'false';
            setLoading(false);
        }
    });

    renderDots();
})();

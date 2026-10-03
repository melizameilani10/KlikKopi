/**
 * Perkoci — Halaman login (satu halaman untuk semua role)
 *  - show / hide kata sandi
 *  - validasi ringan di sisi klien (server tetap sumber kebenaran)
 *  - loading state pada tombol submit
 */
(() => {
    'use strict';

    const form = document.getElementById('login-form');
    if (!form) return;

    const loginInput    = document.getElementById('login');
    const passwordInput = document.getElementById('password');
    const toggleBtn     = document.getElementById('toggle-password');
    const submitBtn     = document.getElementById('login-submit');
    const submitLabel   = document.getElementById('login-submit-label');

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

    // Hapus tanda merah dari respon server "kredensial salah" saat pengguna mengetik lagi.
    function clearAuthState() {
        [loginInput, passwordInput].forEach((input) => {
            if (input && !errorEl(input.name)?.textContent) input.classList.remove('is-invalid');
        });
    }

    /* ---------- Show / hide kata sandi ---------- */
    if (toggleBtn && passwordInput) {
        const useEl = toggleBtn.querySelector('use');

        toggleBtn.addEventListener('click', () => {
            const reveal = passwordInput.type === 'password';

            passwordInput.type = reveal ? 'text' : 'password';
            toggleBtn.setAttribute('aria-pressed', String(reveal));
            toggleBtn.setAttribute('aria-label', reveal ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            if (useEl) useEl.setAttribute('href', reveal ? '#i-eye-off' : '#i-eye');
        });
    }

    /* ---------- Hapus error saat mengetik ---------- */
    loginInput?.addEventListener('input', () => {
        clearError('login', loginInput);
        clearAuthState();
    });
    passwordInput?.addEventListener('input', () => {
        clearError('password', passwordInput);
        clearAuthState();
    });

    /* ---------- Validasi ---------- */
    function validate() {
        let firstInvalid = null;

        if (!loginInput.value.trim()) {
            showError('login', 'ID pengguna atau email wajib diisi.', loginInput);
            firstInvalid ??= loginInput;
        } else {
            clearError('login', loginInput);
        }

        if (!passwordInput.value) {
            showError('password', 'Kata sandi wajib diisi.', passwordInput);
            firstInvalid ??= passwordInput;
        } else {
            clearError('password', passwordInput);
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
})();

/**
 * Perkoci — Login page behaviour
 *  - show / hide password
 *  - 6-digit token boxes (auto-advance, backspace, paste)
 *  - light client-side validation (the server stays the source of truth)
 *  - loading state on submit
 */
(() => {
    'use strict';

    const form = document.getElementById('login-form');
    if (!form) return;

    const emailInput    = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const toggleBtn     = document.getElementById('toggle-password');
    const submitBtn     = document.getElementById('login-submit');
    const submitLabel   = document.getElementById('login-submit-label');
    const otpInputs     = Array.from(form.querySelectorAll('[data-otp-digit]'));
    const otpHidden     = document.getElementById('otp_code');

    const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    /* ---------- Error helpers ---------- */
    const errorEl = (name) => form.querySelector(`[data-error-for="${name}"]`);

    function showError(name, message, inputs = []) {
        const el = errorEl(name);
        if (el) {
            el.textContent = message;
            el.hidden = false;
        }
        inputs.forEach((input) => {
            input.classList.add('is-invalid');
            input.setAttribute('aria-invalid', 'true');
        });
    }

    function clearError(name, inputs = []) {
        const el = errorEl(name);
        if (el) {
            el.textContent = '';
            el.hidden = true;
        }
        inputs.forEach((input) => {
            input.classList.remove('is-invalid');
            input.removeAttribute('aria-invalid');
        });
    }

    // Remove the red state left by a server-side "wrong credentials" response.
    function clearAuthState() {
        [emailInput, passwordInput].forEach((input) => {
            if (input && !errorEl(input.name)?.textContent) {
                input.classList.remove('is-invalid');
            }
        });
    }

    /* ---------- Show / hide password ---------- */
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

    /* ---------- Token (OTP) boxes ---------- */
    const syncOtp = () => {
        if (otpHidden) otpHidden.value = otpInputs.map((input) => input.value).join('');
    };

    otpInputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '').slice(0, 1);
            if (input.value && otpInputs[index + 1]) otpInputs[index + 1].focus();
            syncOtp();
            clearError('otp_code', otpInputs);
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace' && !input.value && otpInputs[index - 1]) {
                otpInputs[index - 1].focus();
            } else if (event.key === 'ArrowLeft' && otpInputs[index - 1]) {
                otpInputs[index - 1].focus();
            } else if (event.key === 'ArrowRight' && otpInputs[index + 1]) {
                otpInputs[index + 1].focus();
            }
        });

        input.addEventListener('paste', (event) => {
            event.preventDefault();
            const digits = (event.clipboardData?.getData('text') || '')
                .replace(/\D/g, '')
                .slice(0, otpInputs.length);

            digits.split('').forEach((digit, i) => { otpInputs[i].value = digit; });
            syncOtp();
            otpInputs[Math.min(digits.length, otpInputs.length - 1)].focus();
        });
    });

    /* ---------- Clear errors while typing ---------- */
    emailInput?.addEventListener('input', () => {
        clearError('email', [emailInput]);
        clearAuthState();
    });
    passwordInput?.addEventListener('input', () => {
        clearError('password', [passwordInput]);
        clearAuthState();
    });

    /* ---------- Validation ---------- */
    function validate() {
        let firstInvalid = null;

        const email = emailInput.value.trim();
        if (!email) {
            showError('email', 'Email wajib diisi.', [emailInput]);
            firstInvalid ??= emailInput;
        } else if (!EMAIL_PATTERN.test(email)) {
            showError('email', 'Format email tidak valid.', [emailInput]);
            firstInvalid ??= emailInput;
        } else {
            clearError('email', [emailInput]);
        }

        if (!passwordInput.value) {
            showError('password', 'Kata sandi wajib diisi.', [passwordInput]);
            firstInvalid ??= passwordInput;
        } else {
            clearError('password', [passwordInput]);
        }

        if (otpInputs.length) {
            const code = otpHidden ? otpHidden.value : '';
            if (code.length > 0 && code.length < otpInputs.length) {
                showError('otp_code', 'Kode token 2FA harus terdiri dari 6 digit angka.', otpInputs);
                firstInvalid ??= otpInputs[code.length];
            } else {
                clearError('otp_code', otpInputs);
            }
        }

        if (firstInvalid) firstInvalid.focus();
        return !firstInvalid;
    }

    /* ---------- Loading state ---------- */
    function setLoading(isLoading) {
        submitBtn.disabled = isLoading;
        submitBtn.classList.toggle('is-loading', isLoading);
        submitBtn.setAttribute('aria-busy', String(isLoading));
        submitLabel.textContent = isLoading
            ? submitBtn.dataset.loadingText
            : submitBtn.dataset.defaultText;
    }

    form.addEventListener('submit', (event) => {
        if (form.dataset.submitting === 'true' || !validate()) {
            event.preventDefault();
            return;
        }

        syncOtp();
        form.dataset.submitting = 'true';
        setLoading(true);
    });

    // Restore the button when the page is shown again via the back button (bfcache).
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            form.dataset.submitting = 'false';
            setLoading(false);
        }
    });
})();

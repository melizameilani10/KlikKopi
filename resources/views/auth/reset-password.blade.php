@extends('layouts.auth')

@section('title', 'Atur Ulang Kata Sandi')

@section('content')
    <section class="auth-card" aria-labelledby="reset-title">
        <div class="auth-card__accent" aria-hidden="true"></div>

        <div class="auth-card__body">
            <header class="auth-header">
                <div class="brand">
                    {{-- Ganti public/images/perkoci-logo.svg dengan logo asli Perkoci --}}
                    <img class="brand__logo" src="{{ asset('images/perkoci-logo.svg') }}" alt="Perkoci Eatery">
                </div>

                <h1 class="auth-title" id="reset-title">Atur Ulang Kata Sandi</h1>
                <p class="auth-subtitle">Buat kata sandi baru untuk akun Anda.</p>
            </header>

            <form class="auth-form" method="POST" action="{{ route('password.update') }}" novalidate>
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="field">
                    <div class="field__head">
                        <label class="field__label" for="email">Alamat Email</label>
                    </div>
                    <div class="input-wrap">
                        <x-icon name="id-card" class="input-icon" />
                        <input
                            type="email"
                            id="email"
                            name="email"
                            @class(['input', 'is-invalid' => $errors->has('email')])
                            value="{{ old('email', $email) }}"
                            placeholder="nama@perkoci.id"
                            autocomplete="email"
                            autocapitalize="none"
                            spellcheck="false"
                            required
                            aria-describedby="email-error"
                        >
                    </div>
                    <p class="field__error" id="email-error" data-error-for="email" @if (! $errors->has('email')) hidden @endif>{{ $errors->first('email') }}</p>
                </div>

                <div class="field">
                    <div class="field__head">
                        <label class="field__label" for="password">Kata Sandi Baru</label>
                        <span class="field__hint">Minimal 8 karakter</span>
                    </div>
                    <div class="input-wrap">
                        <x-icon name="lock" class="input-icon" />
                        <input
                            type="password"
                            id="password"
                            name="password"
                            @class(['input', 'is-invalid' => $errors->has('password')])
                            placeholder="Masukkan kata sandi baru"
                            autocomplete="new-password"
                            required
                            autofocus
                            aria-describedby="password-error"
                        >
                    </div>
                    <p class="field__error" id="password-error" data-error-for="password" @if (! $errors->has('password')) hidden @endif>{{ $errors->first('password') }}</p>
                </div>

                <div class="field">
                    <div class="field__head">
                        <label class="field__label" for="password_confirmation">Konfirmasi Kata Sandi</label>
                    </div>
                    <div class="input-wrap">
                        <x-icon name="lock" class="input-icon" />
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="input"
                            placeholder="Ulangi kata sandi baru"
                            autocomplete="new-password"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="btn btn--primary">
                    <span class="btn__spinner" aria-hidden="true"></span>
                    <span class="btn__label">Simpan Kata Sandi</span>
                    <x-icon name="arrow-right" class="btn__icon" />
                </button>
            </form>

            <p class="auth-footnote">
                <a class="field__link" href="{{ route('login') }}">
                    <x-icon name="arrow-left" class="icon--sm" />
                    Kembali ke halaman masuk
                </a>
            </p>
        </div>
    </section>
@endsection

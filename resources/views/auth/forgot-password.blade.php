@extends('layouts.auth')

@section('title', 'Lupa Kata Sandi')

@section('content')
    <section class="auth-card" aria-labelledby="forgot-title">
        <div class="auth-card__accent" aria-hidden="true"></div>

        <div class="auth-card__body">
            <header class="auth-header">
                <div class="brand">
                    {{-- Ganti public/images/perkoci-logo.svg dengan logo asli Perkoci --}}
                    <img class="brand__logo" src="{{ asset('images/perkoci-logo.svg') }}" alt="Perkoci Eatery">
                </div>

                <h1 class="auth-title" id="forgot-title">Lupa Kata Sandi</h1>
                <p class="auth-subtitle">Masukkan alamat email akun Anda. Kami akan mengirimkan tautan untuk membuat kata sandi baru.</p>
            </header>

            @if (session('success'))
                <div class="alert alert--success" role="status">
                    <x-icon name="check-circle" />
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert--success" role="status">
                    <x-icon name="check-circle" />
                    <div>{{ session('status') }}</div>
                </div>
            @endif

            <form class="auth-form" method="POST" action="{{ route('password.email') }}" novalidate>
                @csrf

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
                            value="{{ old('email') }}"
                            placeholder="nama@perkoci.id"
                            autocomplete="email"
                            autocapitalize="none"
                            spellcheck="false"
                            required
                            aria-describedby="email-error"
                            autofocus
                        >
                    </div>
                    <p class="field__error" id="email-error" data-error-for="email" @if (! $errors->has('email')) hidden @endif>{{ $errors->first('email') }}</p>
                </div>

                <button type="submit" class="btn btn--primary">
                    <span class="btn__spinner" aria-hidden="true"></span>
                    <span class="btn__label">Kirim Tautan Reset</span>
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

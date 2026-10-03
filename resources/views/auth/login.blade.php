@extends('layouts.auth')

@section('title', config('perkoci.login.title'))

@section('content')
    @php
        $login    = config('perkoci.login');
        $authFail = $errors->has('auth');
    @endphp

    <section class="auth-card" aria-labelledby="login-title">
        <div class="auth-card__accent" aria-hidden="true"></div>

        <div class="auth-card__body">

            <header class="auth-header">
                <div class="brand">
                    {{-- Ganti public/images/perkoci-logo.svg dengan logo asli Perkoci --}}
                    <img class="brand__logo" src="{{ asset('images/perkoci-logo.svg') }}" alt="Perkoci Eatery">
                </div>

                <span class="status-pill">
                    <span class="status-pill__dot" aria-hidden="true"></span>
                    {{ $login['status_label'] }}
                </span>

                <h1 class="auth-title" id="login-title">{{ $login['title'] }}</h1>
                <p class="auth-subtitle">{{ $login['subtitle'] }}</p>

                <div class="role-chips">
                    @foreach (config('perkoci.roles') as $role)
                        <span class="role-chip">
                            <x-icon :name="$role['icon']" class="icon--sm" />
                            {{ $role['label'] }}
                        </span>
                    @endforeach
                </div>
            </header>

            @if (session('success'))
                <div class="alert alert--success" role="status">
                    <x-icon name="check-circle" />
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert--info" role="status">
                    <x-icon name="info" />
                    <div>{{ session('info') }}</div>
                </div>
            @endif

            @if ($authFail)
                <div class="alert alert--error" role="alert">
                    <x-icon name="alert-circle" />
                    <div>{{ $errors->first('auth') }}</div>
                </div>
            @endif

            <form id="login-form" class="auth-form" method="POST" action="{{ route('login.store') }}" novalidate>
                @csrf

                {{-- ID pengguna / email --}}
                <div class="field">
                    <div class="field__head">
                        <label class="field__label" for="login">{{ $login['email_label'] }}</label>
                        <span class="field__hint">{{ $login['email_hint'] }}</span>
                    </div>
                    <div class="input-wrap">
                        <x-icon name="id-card" class="input-icon" />
                        <input
                            type="text"
                            id="login"
                            name="login"
                            @class(['input', 'is-invalid' => $errors->has('login') || $authFail])
                            value="{{ old('login') }}"
                            placeholder="{{ $login['email_placeholder'] }}"
                            autocomplete="username"
                            autocapitalize="none"
                            spellcheck="false"
                            required
                            aria-describedby="login-error"
                            @unless (old('login')) autofocus @endunless
                        >
                    </div>
                    <p class="field__error" id="login-error" data-error-for="login" @if (! $errors->has('login')) hidden @endif>{{ $errors->first('login') }}</p>
                </div>

                {{-- Kata sandi --}}
                <div class="field">
                    <div class="field__head">
                        <label class="field__label" for="password">{{ $login['password_label'] }}</label>
                        <a class="field__link" href="{{ route('password.request') }}">{{ $login['forgot_label'] }}</a>
                    </div>
                    <div class="input-wrap">
                        <x-icon name="lock" class="input-icon" />
                        <input
                            type="password"
                            id="password"
                            name="password"
                            @class(['input', 'input--has-action', 'is-invalid' => $errors->has('password') || $authFail])
                            placeholder="Masukkan kata sandi"
                            autocomplete="current-password"
                            required
                            aria-describedby="password-error"
                            @if (old('login')) autofocus @endif
                        >
                        <button
                            type="button"
                            class="input-action"
                            id="toggle-password"
                            aria-label="Tampilkan kata sandi"
                            aria-controls="password"
                            aria-pressed="false"
                        >
                            <x-icon name="eye" />
                        </button>
                    </div>
                    <p class="field__error" id="password-error" data-error-for="password" @if (! $errors->has('password')) hidden @endif>{{ $errors->first('password') }}</p>
                </div>

                {{-- Ingat saya --}}
                <label class="check" for="remember">
                    <input type="checkbox" class="check__input" id="remember" name="remember" value="1" @checked(old('remember'))>
                    <span>{{ $login['remember_label'] }}</span>
                </label>

                <button
                    type="submit"
                    class="btn btn--primary"
                    id="login-submit"
                    data-default-text="{{ $login['submit_label'] }}"
                    data-loading-text="Memproses..."
                >
                    <span class="btn__spinner" aria-hidden="true"></span>
                    <span class="btn__label" id="login-submit-label">{{ $login['submit_label'] }}</span>
                    <x-icon name="arrow-right" class="btn__icon" />
                </button>
            </form>

            <div class="info-panel">
                <p class="info-panel__title">
                    <x-icon name="lock" class="icon--sm" />
                    {{ $login['info_title'] }}
                </p>
                <p class="info-panel__text">{{ $login['info_text'] }}</p>
            </div>
        </div>
    </section>

    <footer class="auth-footnote">
        @foreach ($login['footnote'] as $item)
            @unless ($loop->first)
                <span class="auth-footnote__sep" aria-hidden="true">•</span>
            @endunless
            <span class="auth-footnote__item">
                <x-icon name="{{ $item['icon'] }}" class="icon--sm" />
                {{ $item['text'] }}
            </span>
        @endforeach
    </footer>
@endsection

@push('scripts')
    <script src="{{ asset('js/login.js') }}?v={{ filemtime(public_path('js/login.js')) }}" defer></script>
@endpush

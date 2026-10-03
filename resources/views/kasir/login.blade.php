@extends('layouts.pos')

@section('title', config('perkoci.kasir.title'))

@section('content')
    @php
        $pos           = config('perkoci.kasir');
        $selectedShift = old('shift', $pos['default_shift']);
        $authFail      = $errors->has('auth');
    @endphp

    <main class="pos-page">
        <section class="pos-card" aria-labelledby="pos-title">

            {{-- Area kiri: branding & info terminal --}}
            <aside class="pos-brand">
                <div class="pos-logo">
                    {{-- Ganti public/images/perkoci-logo.svg dengan logo asli Perkoci --}}
                    <img src="{{ asset('images/perkoci-logo.svg') }}" alt="Perkoci Eatery">
                    <span class="pos-logo__dot" aria-hidden="true"></span>
                </div>

                <p class="pos-eyebrow">{{ $pos['brand'] }}</p>
                <p class="pos-tagline">{{ $pos['tagline'] }}</p>

                <div class="machine">
                    <span class="machine__icon"><x-icon name="register" /></span>
                    <div class="machine__info">
                        <span class="machine__label">Lokasi Mesin</span>
                        <strong class="machine__name">{{ $pos['terminal'] }}</strong>
                    </div>
                    <span class="badge-online">
                        <span class="badge-online__dot" aria-hidden="true"></span>
                        {{ $pos['status'] }}
                    </span>
                </div>
            </aside>

            {{-- Area kanan: form login --}}
            <div class="pos-main">
                <header class="pos-head">
                    <h1 class="pos-title" id="pos-title">{{ $pos['title'] }}</h1>
                    <p class="pos-desc">{{ $pos['description'] }}</p>
                </header>

                @if (session('success'))
                    <div class="alert alert--success" role="status">
                        <x-icon name="check-circle" />
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if ($errors->has('lockout'))
                    <div class="alert alert--warning" role="alert">
                        <x-icon name="alert-circle" />
                        <div>{{ $errors->first('lockout') }}</div>
                    </div>
                @endif

                @if ($authFail)
                    <div class="alert alert--error" role="alert">
                        <x-icon name="alert-circle" />
                        <div>{{ $errors->first('auth') }}</div>
                    </div>
                @endif

                <form id="pos-form" class="pos-form" method="POST" action="{{ route('kasir.login.store') }}" novalidate>
                    @csrf

                    {{-- Shift --}}
                    <fieldset class="field field--plain">
                        <legend class="field__label">Pilih Shift Kerja</legend>
                        <div class="segmented" role="radiogroup" aria-label="Pilih shift kerja">
                            @foreach ($pos['shifts'] as $key => $shift)
                                <label class="segmented__item">
                                    <input type="radio" name="shift" value="{{ $key }}" @checked($selectedShift === $key)>
                                    <span class="segmented__label">
                                        <x-icon :name="$shift['icon']" class="icon--sm" />
                                        <span>{{ $shift['label'] }} ({{ $shift['time'] }})</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <p class="field__error" data-error-for="shift" @if (! $errors->has('shift')) hidden @endif>{{ $errors->first('shift') }}</p>
                    </fieldset>

                    {{-- ID Kasir --}}
                    <div class="field">
                        <label class="field__label" for="username">ID Kasir / Akun</label>
                        <div class="input-wrap">
                            <x-icon name="user" class="input-icon" />
                            <input
                                type="text"
                                id="username"
                                name="username"
                                @class(['input', 'is-invalid' => $errors->has('username') || $authFail])
                                value="{{ old('username') }}"
                                placeholder="contoh: kasir.dimas"
                                autocomplete="username"
                                autocapitalize="none"
                                spellcheck="false"
                                required
                                aria-describedby="username-error"
                                @unless (old('username')) autofocus @endunless
                            >
                        </div>
                        <p class="field__error" id="username-error" data-error-for="username" @if (! $errors->has('username')) hidden @endif>{{ $errors->first('username') }}</p>
                    </div>

                    {{-- PIN --}}
                    <div class="field">
                        <div class="field__head">
                            <label class="field__label" for="pin">PIN Kasir (6 Digit)</label>
                            <div class="pin-dots" id="pin-dots" aria-hidden="true">
                                @for ($i = 0; $i < 6; $i++)<span></span>@endfor
                            </div>
                        </div>
                        <div class="input-wrap">
                            <x-icon name="keypad" class="input-icon" />
                            <input
                                type="password"
                                id="pin"
                                name="pin"
                                @class(['input', 'input--has-action', 'is-invalid' => $errors->has('pin') || $authFail])
                                placeholder="Masukkan 6 angka PIN"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                maxlength="6"
                                autocomplete="off"
                                required
                                aria-describedby="pin-error"
                                @if (old('username')) autofocus @endif
                            >
                            <button type="button" class="input-action" id="toggle-pin" aria-label="Tampilkan PIN" aria-pressed="false">
                                <x-icon name="eye" />
                            </button>
                        </div>
                        <p class="field__error" id="pin-error" data-error-for="pin" @if (! $errors->has('pin')) hidden @endif>{{ $errors->first('pin') }}</p>
                    </div>

                    {{-- Keypad --}}
                    <div class="keypad" id="keypad" role="group" aria-label="Keypad PIN">
                        @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9] as $n)
                            <button type="button" class="keypad__key" data-key="{{ $n }}" aria-label="Angka {{ $n }}">{{ $n }}</button>
                        @endforeach
                        <button type="button" class="keypad__key keypad__key--clear" data-action="clear" aria-label="Hapus semua">C</button>
                        <button type="button" class="keypad__key" data-key="0" aria-label="Angka 0">0</button>
                        <button type="button" class="keypad__key keypad__key--back" data-action="back" aria-label="Hapus satu digit">
                            <x-icon name="backspace" />
                        </button>
                    </div>

                    <button
                        type="submit"
                        class="btn btn--primary"
                        id="pos-submit"
                        data-default-text="{{ $pos['submit_label'] }}"
                        data-loading-text="Membuka shift..."
                    >
                        <span class="btn__spinner" aria-hidden="true"></span>
                        <span class="btn__label" id="pos-submit-label">{{ $pos['submit_label'] }}</span>
                        <x-icon name="arrow-right" class="btn__icon" />
                    </button>
                </form>
            </div>
        </section>

        <footer class="pos-footer">
            <p>Perkoci POS {{ $pos['version'] }}</p>
            <p>Butuh bantuan? Hubungi <strong class="pos-footer__link">{{ $pos['help_contact'] }}</strong></p>
        </footer>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('js/pos-login.js') }}?v={{ filemtime(public_path('js/pos-login.js')) }}" defer></script>
@endpush

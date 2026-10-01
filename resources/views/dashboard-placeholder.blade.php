@extends('layouts.auth')

@section('title', 'Dashboard')

@section('content')
    <section class="auth-card" aria-labelledby="dash-title">
        <div class="auth-card__accent" aria-hidden="true"></div>

        <div class="auth-card__body">
            <header class="auth-header">
                <div class="brand">
                    <img class="brand__logo" src="{{ asset('images/perkoci-logo.svg') }}" alt="Perkoci Eatery">
                </div>
                <h1 class="auth-title" id="dash-title">Login Berhasil</h1>
                <p class="auth-subtitle">
                    Halo, <strong>{{ auth()->user()->name }}</strong>. Ini hanya halaman sementara — Dashboard akan dibuat pada tahap berikutnya.
                </p>
            </header>

            @if (session('success'))
                <div class="alert alert--success" role="status">
                    <x-icon name="check-circle" />
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('logout') }}" class="auth-form">
                @csrf
                <button type="submit" class="btn btn--primary">
                    <span class="btn__label">Keluar</span>
                </button>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
    {{-- Icon sprite is included by the layout; nothing else needed here. --}}
@endpush

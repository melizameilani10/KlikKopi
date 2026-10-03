@extends('layouts.pos')

@section('title', 'Terminal Kasir')

@section('content')
    <main class="pos-page">
        <section class="pos-simple">
            <div class="pos-logo">
                <img src="{{ asset('images/perkoci-logo.svg') }}" alt="Perkoci Eatery">
                <span class="pos-logo__dot" aria-hidden="true"></span>
            </div>
            <h1 class="pos-title">Shift Dibuka</h1>
            <p class="pos-desc">
                Halo, <strong>{{ auth()->user()->name }}</strong>
                @if (session('pos.shift'))
                    — shift {{ config('perkoci.kasir.shifts.'.session('pos.shift').'.label') }}
                @endif
                . Ini halaman sementara, Dashboard Kasir dibuat pada tahap berikutnya.
            </p>

            @if (session('success'))
                <div class="alert alert--success" role="status">
                    <x-icon name="check-circle" />
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('kasir.logout') }}" class="pos-form">
                @csrf
                <button type="submit" class="btn btn--primary"><span class="btn__label">Tutup Shift & Keluar</span></button>
            </form>
        </section>
    </main>
@endsection

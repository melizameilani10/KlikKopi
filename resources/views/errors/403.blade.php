@extends('layouts.auth')

@section('title', 'Akses Ditolak')

@section('content')
<div class="auth-card" style="max-width: 560px; margin: 8vh auto; padding: 32px; text-align: center;">
    <p style="font-size: 13px; letter-spacing: 2px; opacity: .6;">ERROR 403</p>
    <h1 style="margin: 8px 0 12px;">Anda tidak memiliki akses ke halaman ini.</h1>

    @auth
        @php
            $role = strtolower((string) auth()->user()->role);
            $label = config("perkoci.roles.{$role}.label") ?? ucfirst($role);
        @endphp
        <p style="opacity: .8;">
            Anda sedang login sebagai
            <strong>{{ auth()->user()->name }}</strong>
            ({{ auth()->user()->username }} · {{ $label }}).
            Halaman ini membutuhkan peran yang berbeda.
        </p>
        <div style="display: flex; gap: 12px; justify-content: center; margin-top: 20px; flex-wrap: wrap;">
            <a href="{{ route('dashboard') }}" class="btn">Ke Dashboard Saya</a>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn">Logout &amp; Ganti Akun</button>
            </form>
        </div>
        <p style="margin-top: 16px; font-size: 13px; opacity: .7;">
            Untuk membuka halaman Kasir, logout lalu login sebagai akun
            <strong>kasir</strong> (mis. <code>kasir.demo</code>).
        </p>
    @else
        <p style="opacity: .8;">Sesi Anda belum login atau sudah kedaluwarsa. Silakan login kembali.</p>
        <div style="margin-top: 20px;">
            <a href="{{ route('login') }}" class="btn">Ke Halaman Login</a>
        </div>
    @endauth
</div>
@endsection

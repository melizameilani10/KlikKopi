@extends('layouts.pos-app')

@section('title', 'Pengaturan Akun')

@section('content')
    <div>
        <h1 class="font-heading text-[28px] font-semibold leading-tight text-pk-brown">Pengaturan Akun</h1>
        <p class="mt-1 text-sm text-pk-brown-soft">Informasi kasir yang sedang bertugas di terminal ini.</p>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <section class="rounded-2xl border border-pk-brown/10 bg-white p-5 shadow-card" aria-label="Profil kasir">
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-pk-khaki font-heading text-2xl font-semibold text-pk-brown">
                    {{ strtoupper(substr($kasir['name'] ?? 'K', 0, 1)) }}
                </span>
                <div>
                    <p class="font-heading text-xl font-semibold text-pk-brown">{{ $kasir['name'] }}</p>
                    <p class="text-sm text-pk-brown-soft">{{ $kasir['role'] }} • {{ $kasir['code'] }}</p>
                </div>
                <span class="ml-auto inline-flex items-center gap-1.5 rounded-full bg-[#e7eee5] px-2.5 py-1 text-[11px] font-bold text-pk-green">
                    <span class="h-1.5 w-1.5 rounded-full bg-pk-green"></span>Aktif
                </span>
            </div>

            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between rounded-xl bg-pk-sand-2 px-3 py-2.5"><dt class="text-pk-brown-soft">Nama kasir</dt><dd class="font-bold text-pk-brown">{{ $kasir['name'] }}</dd></div>
                <div class="flex justify-between rounded-xl bg-pk-sand-2 px-3 py-2.5"><dt class="text-pk-brown-soft">ID kasir / username</dt><dd class="font-bold text-pk-brown">{{ $kasir['username'] ?: 'kasir.dimas' }}</dd></div>
                <div class="flex justify-between rounded-xl bg-pk-sand-2 px-3 py-2.5"><dt class="text-pk-brown-soft">Shift</dt><dd class="font-bold text-pk-brown">{{ $kasir['shift'] }} ({{ $kasir['session'] }})</dd></div>
                <div class="flex justify-between rounded-xl bg-pk-sand-2 px-3 py-2.5"><dt class="text-pk-brown-soft">Status akun</dt><dd class="font-bold text-pk-green">Aktif</dd></div>
            </dl>
        </section>

        <section class="rounded-2xl border border-pk-brown/10 bg-white p-5 shadow-card" aria-label="Keamanan dan sesi">
            <h2 class="font-heading text-lg font-semibold text-pk-brown">PIN &amp; Sesi</h2>
            <p class="text-xs text-pk-brown-soft">Ubah PIN hanya jika fitur backend tersedia. Hubungi manajer untuk reset PIN.</p>

            <form id="pin-form" class="mt-3 space-y-2" onsubmit="return false;">
                <label class="block text-sm font-semibold text-pk-brown">PIN baru (6 digit)
                    <input id="pin-new" type="password" inputmode="numeric" maxlength="6" placeholder="••••••"
                        class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm tracking-[0.4em] outline-none focus:border-pk-green" />
                </label>
                <x-pos.button id="btn-pin" variant="brown" icon="lock">Ubah PIN</x-pos.button>
                <p id="pin-msg" class="hidden rounded-xl p-3 text-xs font-bold" role="status"></p>
            </form>

            <div class="mt-4 border-t border-pk-brown/10 pt-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-pos.button variant="danger" icon="logout">Tutup Shift &amp; Keluar</x-pos.button>
                </form>
                <p class="mt-2 text-center text-xs text-pk-brown-soft">Pastikan cash drawer sudah direkonsiliasi sebelum keluar.</p>
            </div>
        </section>
    </div>
@endsection

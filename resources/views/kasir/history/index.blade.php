@extends('layouts.pos-app')

@section('title', 'Riwayat & Shift Kasir')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="font-heading text-[28px] font-semibold leading-tight text-pk-brown">Riwayat &amp; Shift Kasir</h1>
            <p class="mt-1 text-sm text-pk-brown-soft">Transaksi hari ini, rekap shift, dan informasi terminal.</p>
        </div>
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl bg-pk-brown px-4 py-2.5 text-sm font-bold text-white hover:bg-[#2c1f16]">
            <x-pos.icon name="printer" class="h-4 w-4" />
            Cetak Rekap Shift
        </button>
    </div>

    <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-4">
        <x-pos.stat-card title="Omzet Shift" :value="'Rp '.number_format($omzet, 0, ',', '.')" hint="Semua metode" icon="banknote" tone="green" />
        <x-pos.stat-card title="Cash" :value="'Rp '.number_format($recap['cash'], 0, ',', '.')" hint="Tunai loket" icon="banknote" tone="brown" />
        <x-pos.stat-card title="QRIS" :value="'Rp '.number_format($recap['qris'], 0, ',', '.')" hint="Scan dinamis" icon="qr" tone="khaki" />
        <x-pos.stat-card title="Debit" :value="'Rp '.number_format($recap['debit'], 0, ',', '.')" hint="EDC" icon="card" tone="amber" />
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <section class="rounded-2xl border border-pk-brown/10 bg-white p-5 shadow-card lg:col-span-2" aria-label="Transaksi hari ini">
            <h2 class="font-heading text-lg font-semibold text-pk-brown">Transaksi Hari Ini</h2>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead>
                        <tr class="text-[11px] uppercase tracking-[0.12em] text-pk-brown-soft">
                            <th class="pb-2 pr-3">Waktu</th>
                            <th class="pb-2 pr-3">Tiket</th>
                            <th class="pb-2 pr-3">Customer</th>
                            <th class="pb-2 pr-3">Meja</th>
                            <th class="pb-2 pr-3">Metode</th>
                            <th class="pb-2 pr-3 text-right">Total</th>
                            <th class="pb-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-pk-brown/10">
                        @foreach ($rows as $r)
                            <tr>
                                <td class="py-2.5 pr-3 font-semibold text-pk-brown-soft">{{ $r['time'] }}</td>
                                <td class="py-2.5 pr-3 font-bold text-pk-brown">{{ $r['code'] }}</td>
                                <td class="py-2.5 pr-3">{{ $r['customer'] }}</td>
                                <td class="py-2.5 pr-3">{{ $r['table'] }}</td>
                                <td class="py-2.5 pr-3">{{ $r['method'] }}</td>
                                <td class="py-2.5 pr-3 text-right font-bold text-pk-brown">Rp {{ number_format($r['total'], 0, ',', '.') }}</td>
                                <td class="py-2.5">
                                    <x-pos.order-status-badge :status="$r['status'] === 'Selesai' ? 'done' : 'cancelled'" :label="$r['status']" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-xs text-pk-brown-soft">{{ $stats['done'] }} selesai • {{ collect($rows)->where('status', 'Dibatalkan')->count() }} dibatalkan • Total {{ $stats['total'] }} pesanan hari ini</p>
        </section>

        <aside class="space-y-4" aria-label="Informasi shift">
            <section class="rounded-2xl bg-pk-brown p-5 text-white shadow-card">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-pk-khaki">Shift Pagi</p>
                <p class="mt-1 font-heading text-xl font-semibold">{{ $kasir['code'] }} • {{ $kasir['name'] }}</p>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between border-b border-white/10 pb-2"><dt class="text-white/70">Mulai</dt><dd class="font-bold">08:00</dd></div>
                    <div class="flex justify-between border-b border-white/10 pb-2"><dt class="text-white/70">Sesi</dt><dd class="font-bold">07:00 - 15:00 WIB</dd></div>
                    <div class="flex justify-between"><dt class="text-white/70">Cash Drawer</dt><dd class="font-bold">Rp {{ $kasir['drawer'] }}</dd></div>
                </dl>
            </section>

            <section class="rounded-2xl border border-pk-brown/10 bg-white p-5 shadow-card">
                <h3 class="font-heading text-base font-semibold text-pk-brown">Rekap Shift</h3>
                <dl class="mt-2 space-y-1.5 text-sm">
                    <div class="flex justify-between"><dt class="text-pk-brown-soft">Cash</dt><dd class="font-bold text-pk-brown">Rp {{ number_format($recap['cash'], 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-pk-brown-soft">QRIS</dt><dd class="font-bold text-pk-brown">Rp {{ number_format($recap['qris'], 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-pk-brown-soft">Debit</dt><dd class="font-bold text-pk-brown">Rp {{ number_format($recap['debit'], 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between border-t border-pk-brown/10 pt-2 font-heading text-base font-semibold text-pk-brown"><dt>Omzet</dt><dd>Rp {{ number_format($omzet, 0, ',', '.') }}</dd></div>
                </dl>
            </section>
        </aside>
    </div>
@endsection

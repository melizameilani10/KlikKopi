@extends('layouts.pos-app')

@section('title', 'Pembayaran & Cetak Struk')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="font-heading text-[28px] font-semibold leading-tight text-pk-brown">Pembayaran &amp; Cetak Struk</h1>
            <p class="mt-1 text-xs font-bold uppercase tracking-[0.2em] text-pk-green">POS Terminal • Loket Kasir 01</p>
        </div>
        <a href="{{ route('kasir.orders.index', ['ticket' => $ticket['code']]) }}" class="inline-flex items-center gap-2 rounded-xl border border-pk-brown/15 bg-white px-4 py-2.5 text-sm font-bold text-pk-brown hover:border-pk-green">
            ← Kembali ke Antrean
        </a>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-5">
        {{-- Kiri: pembayaran --}}
        <section class="rounded-2xl border border-pk-brown/10 bg-white p-5 shadow-card lg:col-span-3" aria-label="Form pembayaran">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="font-heading text-2xl font-semibold text-pk-brown">#{{ $ticket['code'] }}</p>
                    <p class="text-sm font-semibold text-pk-brown">Transaksi Antrean #A-{{ $ticket['code'] }} • {{ $ticket['table'] }} • {{ $ticket['type'] === 'takeaway' ? 'Takeaway' : 'Dine-in' }}</p>
                    <p class="text-sm text-pk-brown-soft">{{ $ticket['customer'] }} • {{ $ticket['phone'] }}</p>
                </div>
                <x-pos.order-status-badge status="waiting_payment" label="Menunggu Pembayaran" />
            </div>

            <ul class="mt-4 divide-y divide-pk-brown/10 rounded-xl bg-pk-paper p-4">
                @foreach ($ticket['items'] as $it)
                    <li class="flex justify-between gap-3 py-1.5 text-sm">
                        <span>{{ $it['qty'] }}× {{ $it['name'] }}</span>
                        <span class="font-bold text-pk-brown">Rp {{ number_format($it['price'] * $it['qty'], 0, ',', '.') }}</span>
                    </li>
                @endforeach
                <li class="flex justify-between pt-2 text-sm text-pk-brown-soft"><span>Subtotal + PB1 + Service</span><span class="font-semibold text-pk-brown">Rp {{ number_format($ticket['total'], 0, ',', '.') }}</span></li>
            </ul>

            <p class="mt-4 rounded-2xl bg-pk-brown p-4 text-center font-heading text-3xl font-semibold text-white" aria-live="polite">
                Rp {{ number_format($ticket['total'], 0, ',', '.') }}
            </p>

            <h2 class="mt-5 text-xs font-bold uppercase tracking-[0.16em] text-pk-brown-soft">Metode Pembayaran</h2>
            <div id="pay-methods" class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4" role="radiogroup" aria-label="Metode pembayaran">
                @foreach ($methods as $i => $m)
                    <button type="button" role="radio" aria-checked="{{ $i === 0 ? 'true' : 'false' }}" data-method="{{ $m['key'] }}"
                        class="pay-method flex flex-col items-center gap-1 rounded-xl border px-3 py-3 text-sm font-bold {{ $i === 0 ? 'border-pk-green bg-[#eef5ea] text-pk-green' : 'border-pk-brown/15 bg-white text-pk-brown hover:border-pk-green' }}">
                        <x-pos.icon :name="$m['icon']" class="h-6 w-6" />
                        {{ $m['label'] }}
                        <span class="text-[11px] font-semibold opacity-70">{{ $m['hint'] }}</span>
                    </button>
                @endforeach
            </div>

            {{-- Panel cash --}}
            <div id="panel-cash" class="pay-panel mt-4">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-xl bg-pk-sand-2 p-3">
                        <p class="text-xs font-semibold text-pk-brown-soft">Total</p>
                        <p class="font-heading text-xl font-semibold text-pk-brown">Rp {{ number_format($ticket['total'], 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl bg-pk-sand-2 p-3">
                        <label for="cash-received" class="text-xs font-semibold text-pk-brown-soft">Uang Diterima</label>
                        <input id="cash-received" type="text" inputmode="numeric" placeholder="Rp 0" autocomplete="off"
                            class="mt-1 w-full bg-transparent font-heading text-xl font-semibold text-pk-brown outline-none placeholder:text-pk-brown-soft/50" />
                    </div>
                    <div class="rounded-xl bg-pk-green p-3 text-white">
                        <p class="text-xs font-semibold text-white/80">Kembalian</p>
                        <p id="cash-change" class="font-heading text-xl font-semibold">Rp 0</p>
                    </div>
                </div>
                <div class="mt-2 flex flex-wrap gap-2">
                    <button type="button" data-cash="exact" class="rounded-lg bg-pk-sand px-3 py-2 text-xs font-bold text-pk-brown hover:bg-[#e6e2d5]">Uang Pas</button>
                    <button type="button" data-cash="50000" class="rounded-lg bg-pk-sand px-3 py-2 text-xs font-bold text-pk-brown hover:bg-[#e6e2d5]">Rp 50.000</button>
                    <button type="button" data-cash="100000" class="rounded-lg bg-pk-sand px-3 py-2 text-xs font-bold text-pk-brown hover:bg-[#e6e2d5]">Rp 100.000</button>
                    <button type="button" data-cash="150000" class="rounded-lg bg-pk-sand px-3 py-2 text-xs font-bold text-pk-brown hover:bg-[#e6e2d5]">Rp 150.000</button>
                </div>
                <p id="cash-error" class="hidden mt-2 rounded-xl bg-[#fbe9e7] p-3 text-xs font-bold text-pk-danger" role="alert"></p>
            </div>

            {{-- Panel non-cash --}}
            <div id="panel-noncash" class="pay-panel mt-4 hidden rounded-xl border border-dashed border-pk-brown/25 bg-pk-paper p-4 text-center">
                <x-pos.icon name="qr" class="mx-auto h-24 w-24 text-pk-brown" />
                <p id="noncash-title" class="mt-2 text-sm font-bold text-pk-brown">Scan QRIS dinamis Rp {{ number_format($ticket['total'], 0, ',', '.') }}</p>
                <p class="text-xs text-pk-brown-soft">Kode berlaku 5 menit • BCA / GoPay / OVO / Dana</p>
            </div>

            <div id="panel-split" class="pay-panel mt-4 hidden">
                <div class="grid grid-cols-2 gap-2">
                    <label class="rounded-xl bg-pk-sand-2 p-3 text-sm font-semibold text-pk-brown">Bill 1 — {{ $ticket['customer'] }}
                        <span class="block font-heading text-lg">Rp {{ number_format(intdiv($ticket['total'], 2), 0, ',', '.') }}</span>
                    </label>
                    <label class="rounded-xl bg-pk-sand-2 p-3 text-sm font-semibold text-pk-brown">Bill 2 — Teman
                        <span class="block font-heading text-lg">Rp {{ number_format($ticket['total'] - intdiv($ticket['total'], 2), 0, ',', '.') }}</span>
                    </label>
                </div>
                <p class="mt-2 text-xs text-pk-brown-soft">Split rata 2 bill. Untuk split custom, gunakan tombol Split Bill di dashboard.</p>
            </div>

            <x-pos.button id="btn-confirm-pay" variant="primary" icon="check" class="mt-4">Konfirmasi Pembayaran Rp {{ number_format($ticket['total'], 0, ',', '.') }}</x-pos.button>
            <p class="mt-2 text-center text-xs text-pk-brown-soft">Dengan konfirmasi, tiket dapur tercetak otomatis dan status menjadi Lunas.</p>
        </section>

        {{-- Kanan: struk --}}
        <aside class="lg:col-span-2" aria-label="Preview struk">
            <div class="rounded-2xl border border-pk-brown/10 bg-white p-5 shadow-card lg:sticky lg:top-[132px]">
                <h2 class="font-heading text-lg font-semibold text-pk-brown">Preview Struk</h2>
                <p class="flex items-center gap-1.5 text-xs font-semibold text-pk-green"><span class="h-2 w-2 rounded-full bg-pk-green"></span>{{ $printer['name'] }} — {{ $printer['status'] }}</p>

                <div id="receipt" class="mx-auto mt-3 w-full max-w-[300px] bg-white p-5 font-mono text-[13px] leading-relaxed text-pk-ink shadow-[0_0_0_1px_rgba(61,43,31,.12)]" style="mask-image:none">
                    <p class="text-center font-bold tracking-wide">PERKOCI EATERY</p>
                    <p class="text-center text-xs">Jl. Kopi No. 24, Bandung</p>
                    <p class="my-2 border-t border-dashed border-pk-brown/30"></p>
                    <p class="font-bold">#{{ $ticket['code'] }} • {{ $ticket['table'] }}</p>
                    <p class="text-xs">{{ $ticket['customer'] }} • Kasir 01</p>
                    <p class="my-2 border-t border-dashed border-pk-brown/30"></p>
                    @foreach ($ticket['items'] as $it)
                        <p class="flex justify-between"><span>{{ $it['qty'] }}× {{ $it['name'] }}</span><span>{{ number_format($it['price'] * $it['qty'], 0, ',', '.') }}</span></p>
                    @endforeach
                    <p class="my-2 border-t border-dashed border-pk-brown/30"></p>
                    <p class="flex justify-between"><span>Subtotal</span><span>{{ number_format($ticket['subtotal'], 0, ',', '.') }}</span></p>
                    <p class="flex justify-between"><span>PB1</span><span>{{ number_format($ticket['pb1'], 0, ',', '.') }}</span></p>
                    <p class="flex justify-between"><span>Service</span><span>{{ number_format($ticket['service'], 0, ',', '.') }}</span></p>
                    <p class="mt-1 flex justify-between font-bold"><span>TOTAL</span><span>Rp {{ number_format($ticket['total'], 0, ',', '.') }}</span></p>
                    <p class="my-2 border-t border-dashed border-pk-brown/30"></p>
                    <p class="text-center text-xs">Terima kasih &amp; sampai jumpa!</p>
                    <p class="text-center text-xs">WiFi: PERKOCI • IG: @perkoci.eatery</p>
                </div>

                <div class="mt-4 space-y-2">
                    <x-pos.button id="btn-print" variant="brown" icon="printer">Cetak Struk</x-pos.button>
                    <x-pos.button id="btn-wa" variant="sand" icon="phone">Kirim E-Receipt WhatsApp</x-pos.button>
                    <x-pos.button id="btn-drawer" variant="ghost" icon="register">Buka Cash Drawer</x-pos.button>
                </div>
            </div>
        </aside>
    </div>

    <script>
        window.POS_PAYMENT_TOTAL = {{ $ticket['total'] }};
    </script>
@endsection

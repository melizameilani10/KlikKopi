@extends('layouts.pos-app')

@section('title', 'Daftar Antrean & Pesanan')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="font-heading text-[28px] font-semibold leading-tight text-pk-brown">Daftar Antrean &amp; Pesanan Meja</h1>
            <p class="mt-1 text-xs font-bold uppercase tracking-[0.2em] text-pk-green">Live Ticket Stream</p>
        </div>
        <button type="button" id="queue-refresh" class="inline-flex items-center gap-2 rounded-xl border border-pk-brown/15 bg-white px-4 py-2.5 text-sm font-bold text-pk-brown hover:border-pk-green">
            <x-pos.icon name="refresh" class="h-4 w-4" />
            Refresh Antrean
        </button>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-5">
        {{-- Kiri: list --}}
        <section class="lg:col-span-3" aria-label="List pesanan">
            <div class="rounded-2xl border border-pk-brown/10 bg-white p-4 shadow-card">
                <div class="flex flex-col gap-2 sm:flex-row">
                    <label class="relative flex-1">
                        <span class="sr-only">Cari tiket, nama, atau meja</span>
                        <x-pos.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-pk-brown-soft" />
                        <input id="ticket-search" type="search" value="{{ $search }}" placeholder="Cari #tiket, nama customer, meja…"
                            class="w-full rounded-xl border border-pk-brown/15 bg-pk-paper py-2.5 pl-10 pr-3 text-sm text-pk-ink outline-none placeholder:text-pk-brown-soft/70 focus:border-pk-green" />
                    </label>
                    <label class="inline-flex items-center gap-2 rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm font-semibold text-pk-brown">
                        Filter area
                        <select id="area-filter" class="bg-transparent text-sm font-semibold outline-none">
                            <option value="">Semua area</option>
                            <option value="Indoor AC">Indoor AC</option>
                            <option value="Outdoor">Outdoor</option>
                            <option value="Counter">Counter</option>
                        </select>
                    </label>
                </div>

                <div id="filter-tabs" class="mt-3 flex flex-wrap gap-2" role="tablist" aria-label="Filter status pesanan">
                    @foreach ($filters as $f)
                        <button type="button" role="tab" data-filter="{{ $f['key'] }}" aria-selected="{{ $activeFilter === $f['key'] ? 'true' : 'false' }}"
                            class="filter-tab inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-bold {{ $activeFilter === $f['key'] ? 'border-pk-green bg-pk-green text-white' : 'border-pk-brown/15 bg-white text-pk-brown hover:border-pk-green' }}">
                            {{ $f['label'] }}
                            @if (! is_null($f['count']))
                                <span class="rounded-full px-1.5 {{ $activeFilter === $f['key'] ? 'bg-white/20' : 'bg-pk-sand' }}">{{ $f['count'] }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>

                <p id="queue-count" class="mt-3 text-xs font-semibold text-pk-brown-soft">Menampilkan 5 dari 18 tiket antrean aktif</p>

                <div id="ticket-list" class="mt-2 grid grid-cols-1 gap-3 md:grid-cols-2">
                    @foreach ($tickets as $t)
                        <x-pos.order-card :ticket="$t" :selected="($activeTicket['code'] ?? '') === $t['code']" :href="route('kasir.orders.index', ['ticket' => $t['code']])" data-status="{{ $t['status'] }}" data-area="{{ $t['area'] }}" data-search="{{ strtolower('#'.$t['code'].' '.$t['customer'].' '.$t['table']) }}" />
                    @endforeach
                </div>
                <p id="ticket-empty" class="hidden rounded-xl bg-pk-sand-2 p-6 text-center text-sm font-semibold text-pk-brown-soft">Tidak ada tiket yang cocok dengan filter/pencarian.</p>
            </div>
        </section>

        {{-- Kanan: detail --}}
        <section class="lg:col-span-2" aria-label="Detail pesanan" aria-live="polite">
            <div id="order-detail" class="rounded-2xl border border-pk-brown/10 bg-white p-5 shadow-card lg:sticky lg:top-[132px]">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p id="d-code" class="font-heading text-[26px] font-semibold leading-none text-pk-brown">#{{ $activeTicket['code'] }}</p>
                        <p id="d-customer" class="mt-1 text-sm font-bold text-pk-ink">{{ $activeTicket['customer'] }}</p>
                        <p id="d-phone" class="flex items-center gap-1 text-xs text-pk-brown-soft">
                            <x-pos.icon name="phone" class="h-3.5 w-3.5" />{{ $activeTicket['phone'] }}
                        </p>
                    </div>
                    <x-pos.order-status-badge id="d-badge" :status="$activeTicket['status']" :label="$activeTicket['status_label']" />
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                    <div class="rounded-xl bg-pk-sand-2 p-3">
                        <p class="font-semibold text-pk-brown-soft">Lokasi</p>
                        <p id="d-table" class="mt-0.5 font-bold text-pk-brown">{{ $activeTicket['table'] }} • {{ $activeTicket['area'] }}</p>
                    </div>
                    <div class="rounded-xl bg-pk-sand-2 p-3">
                        <p class="font-semibold text-pk-brown-soft">Waktu masuk</p>
                        <p class="mt-0.5 font-bold text-pk-brown"><span id="d-time">{{ $activeTicket['time'] }}</span> • <span id="d-ago">{{ $activeTicket['ago'] }}</span></p>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-pk-brown-soft"><span id="d-count">{{ $activeTicket['items_count'] }}</span> Item</p>
                    <ul id="d-items" class="mt-2 divide-y divide-pk-brown/10">
                        @foreach ($activeTicket['items'] as $it)
                            <li class="flex items-start justify-between gap-3 py-2">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-pk-ink">{{ $it['qty'] }}× {{ $it['name'] }}</p>
                                    @if (! empty($it['note']))
                                        <p class="text-xs text-pk-brown-soft">{{ $it['note'] }}</p>
                                    @endif
                                </div>
                                <p class="shrink-0 text-sm font-bold text-pk-brown">Rp {{ number_format($it['price'] * $it['qty'], 0, ',', '.') }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <dl class="mt-3 space-y-1.5 border-t border-pk-brown/10 pt-3 text-sm">
                    <div class="flex justify-between text-pk-brown-soft"><dt>Subtotal</dt><dd id="d-subtotal" class="font-semibold text-pk-brown">Rp {{ number_format($activeTicket['subtotal'], 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between text-pk-brown-soft"><dt>PB1</dt><dd id="d-pb1" class="font-semibold text-pk-brown">Rp {{ number_format($activeTicket['pb1'], 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between text-pk-brown-soft"><dt>Service</dt><dd id="d-service" class="font-semibold text-pk-brown">Rp {{ number_format($activeTicket['service'], 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between border-t border-pk-brown/10 pt-2 font-heading text-lg font-semibold text-pk-brown"><dt>TOTAL</dt><dd id="d-total">Rp {{ number_format($activeTicket['total'], 0, ',', '.') }}</dd></div>
                </dl>

                @if (! empty($activeTicket['note']))
                    <p class="mt-3 rounded-xl bg-[#fdf0e1] p-3 text-xs font-semibold text-[#9a5b14]">Catatan: {{ $activeTicket['note'] }}</p>
                @endif
                <p id="d-note-extra" class="hidden mt-3 rounded-xl bg-[#fdf0e1] p-3 text-xs font-semibold text-[#9a5b14]"></p>

                <div class="mt-4 space-y-2">
                    <x-pos.button id="btn-pay" variant="primary" icon="banknote" :href="route('kasir.payment.show', $activeTicket['code'])">Proses Pembayaran Sekarang</x-pos.button>
                    <div class="grid grid-cols-2 gap-2">
                        <x-pos.button id="btn-kitchen" variant="brown" icon="pot">Kirim Tiket Dapur</x-pos.button>
                        <x-pos.button id="btn-barista" variant="sand" icon="bell">Panggil Barista</x-pos.button>
                    </div>
                    <x-pos.button id="btn-note" variant="ghost" icon="plus">+ Tambah Catatan</x-pos.button>
                    <x-pos.button id="btn-cancel" variant="danger" icon="x">Batalkan Pesanan</x-pos.button>
                </div>
            </div>
        </section>
    </div>

    {{-- Modal batal --}}
    <div id="modal-cancel" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/45 p-4" role="dialog" aria-modal="true" aria-labelledby="modal-cancel-title">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-card">
            <h3 id="modal-cancel-title" class="font-heading text-xl font-semibold text-pk-brown">Batalkan pesanan <span id="modal-cancel-code">#{{ $activeTicket['code'] }}</span>?</h3>
            <p class="mt-2 text-sm text-pk-brown-soft">Tindakan ini tidak dapat dibatalkan. Stok yang terpakai akan dikembalikan dan tiket dihapus dari antrean aktif.</p>
            <label class="mt-3 block text-sm font-semibold text-pk-brown">Alasan pembatalan
                <select id="cancel-reason" class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm outline-none focus:border-pk-danger">
                    <option>Customer membatalkan</option>
                    <option>Stok habis</option>
                    <option>Salah input</option>
                    <option>Lainnya</option>
                </select>
            </label>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <x-pos.button id="modal-cancel-no" variant="sand">Kembali</x-pos.button>
                <x-pos.button id="modal-cancel-yes" variant="danger-solid">Ya, Batalkan</x-pos.button>
            </div>
        </div>
    </div>

    {{-- Modal catatan --}}
    <div id="modal-note" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/45 p-4" role="dialog" aria-modal="true" aria-labelledby="modal-note-title">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-card">
            <h3 id="modal-note-title" class="font-heading text-xl font-semibold text-pk-brown">Tambah catatan</h3>
            <label class="mt-3 block text-sm font-semibold text-pk-brown">Catatan untuk dapur/barista
                <textarea id="note-input" rows="3" placeholder="Contoh: gula aren terpisah…" class="mt-1 w-full rounded-xl border border-pk-brown/15 bg-pk-paper px-3 py-2.5 text-sm outline-none focus:border-pk-green"></textarea>
            </label>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <x-pos.button id="modal-note-no" variant="sand">Batal</x-pos.button>
                <x-pos.button id="modal-note-yes" variant="primary">Simpan Catatan</x-pos.button>
            </div>
        </div>
    </div>

    <script>
        window.POS_TICKETS = @json($tickets);
        window.POS_ACTIVE = @json($activeTicket['code']);
    </script>
@endsection

{{-- Timeline status pesanan. Props: $step (1-6), $paid --}}
@props(['step' => 1, 'paid' => true])

@php
    $steps = [
        ['label' => 'Pesanan Diterima', 'desc' => 'Tiket masuk ke kasir & dapur'],
        ['label' => 'Pembayaran Berhasil', 'desc' => 'Tagihan lunas'],
        ['label' => 'Sedang Diproses', 'desc' => 'Dapur menyiapkan pesanan'],
        ['label' => 'Sedang Diracik', 'desc' => 'Barista meracik minuman'],
        ['label' => 'Siap Diantar', 'desc' => 'Pesanan menuju meja Anda'],
        ['label' => 'Selesai', 'desc' => 'Selamat menikmati!'],
    ];
@endphp

<ol class="space-y-0" aria-label="Status pesanan">
    @foreach ($steps as $i => $s)
        @php
            $n = $i + 1;
            $pendingPay = ($s['label'] === 'Pembayaran Berhasil' && ! $paid);
            $done = $n < $step || ($n === $step && ! $pendingPay && $step > 1) || ($n === 1);
            $isCurrent = $n === $step;
            $dot = $pendingPay
                ? 'bg-white text-pk-amber ring-2 ring-pk-amber'
                : ($done || $isCurrent ? 'bg-pk-green text-white' : 'bg-white text-pk-brown-soft ring-1 ring-pk-brown/20');
            $title = $pendingPay ? 'text-pk-amber' : (($isCurrent || $done) ? 'text-pk-brown' : 'text-pk-brown-soft');
        @endphp
        <li class="relative flex gap-3 pb-5 last:pb-0">
            @if (! $loop->last)
                <span class="absolute left-[15px] top-8 h-[calc(100%-2rem)] w-0.5 {{ $n < $step ? 'bg-pk-green' : 'bg-pk-brown/15' }}"></span>
            @endif
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $dot }}">
                @if ($done && ! $pendingPay)
                    <x-pos.icon name="check" class="h-4 w-4" />
                @elseif ($pendingPay)
                    <x-pos.icon name="clock" class="h-4 w-4" />
                @else
                    <span class="text-xs font-bold">{{ $n }}</span>
                @endif
            </span>
            <div class="pt-0.5">
                <p class="text-sm font-bold {{ $title }}">{{ $s['label'] }}{{ $pendingPay ? ' — Bayar di Kasir' : '' }}</p>
                <p class="text-xs text-pk-brown-soft">{{ $s['desc'] }}</p>
            </div>
        </li>
    @endforeach
</ol>

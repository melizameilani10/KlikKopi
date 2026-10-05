{{-- Kartu tiket antrean. Props: $ticket (array), $selected (bool), $href (string|null) --}}
@props(['ticket' => [], 'selected' => false, 'href' => null])

@php
    $cls = 'block rounded-2xl border bg-white p-4 text-left shadow-card transition-colors '.($selected ? 'border-pk-green ring-1 ring-pk-green' : 'border-pk-brown/10 hover:border-pk-green/50');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $cls]) }} data-ticket-link data-ticket="{{ $ticket['code'] ?? '' }}">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="font-heading text-lg font-semibold leading-none text-pk-brown">#{{ $ticket['code'] ?? '-' }}</p>
                <p class="mt-1 flex items-center gap-1 text-xs font-semibold text-pk-brown">
                    <x-pos.icon name="pin" class="h-3.5 w-3.5 text-pk-amber" />
                    {{ $ticket['table'] ?? '-' }}
                    @if (! empty($ticket['area']))
                        <span class="font-normal text-pk-brown-soft">• {{ $ticket['area'] }}</span>
                    @endif
                </p>
            </div>
            <span class="text-right text-[11px] font-semibold text-pk-brown-soft">{{ $ticket['time'] ?? '' }}<br>{{ $ticket['ago'] ?? '' }}</span>
        </div>

        <p class="mt-2 truncate text-sm font-semibold text-pk-ink">{{ $ticket['customer'] ?? '-' }}</p>

        <div class="mt-2 flex items-center justify-between gap-2">
            <p class="text-xs text-pk-brown-soft">{{ $ticket['items_count'] ?? count($ticket['items'] ?? []) }} Item</p>
            <p class="font-heading text-base font-semibold text-pk-brown">Rp {{ number_format($ticket['total'] ?? 0, 0, ',', '.') }}</p>
        </div>

        <div class="mt-2.5">
            <x-pos.order-status-badge :status="$ticket['status'] ?? 'waiting_payment'" :label="$ticket['status_label'] ?? ''" />
        </div>
    </a>
@else
    <div {{ $attributes->merge(['class' => $cls]) }} data-ticket="{{ $ticket['code'] ?? '' }}">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="font-heading text-lg font-semibold leading-none text-pk-brown">#{{ $ticket['code'] ?? '-' }}</p>
                <p class="mt-1 flex items-center gap-1 text-xs font-semibold text-pk-brown">
                    <x-pos.icon name="pin" class="h-3.5 w-3.5 text-pk-amber" />
                    {{ $ticket['table'] ?? '-' }}
                    @if (! empty($ticket['area']))
                        <span class="font-normal text-pk-brown-soft">• {{ $ticket['area'] }}</span>
                    @endif
                </p>
            </div>
            <span class="text-right text-[11px] font-semibold text-pk-brown-soft">{{ $ticket['time'] ?? '' }}<br>{{ $ticket['ago'] ?? '' }}</span>
        </div>

        <p class="mt-2 truncate text-sm font-semibold text-pk-ink">{{ $ticket['customer'] ?? '-' }}</p>

        <div class="mt-2 flex items-center justify-between gap-2">
            <p class="text-xs text-pk-brown-soft">{{ $ticket['items_count'] ?? count($ticket['items'] ?? []) }} Item</p>
            <p class="font-heading text-base font-semibold text-pk-brown">Rp {{ number_format($ticket['total'] ?? 0, 0, ',', '.') }}</p>
        </div>

        <div class="mt-2.5">
            <x-pos.order-status-badge :status="$ticket['status'] ?? 'waiting_payment'" :label="$ticket['status_label'] ?? ''" />
        </div>
    </div>
@endif

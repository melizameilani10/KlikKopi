{{-- Sidebar manajer. Props: $manager (array), $active (string) --}}
@props(['manager' => [], 'active' => 'dashboard'])

@php
    $menus = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'grid', 'route' => 'manager.dashboard'],
        ['key' => 'sales', 'label' => 'Laporan Penjualan', 'icon' => 'receipt', 'route' => 'manager.sales.index'],
        ['key' => 'finance', 'label' => 'Laporan Keuangan', 'icon' => 'banknote', 'route' => 'manager.finance.index'],
        ['key' => 'pb1', 'label' => 'Audit PB1', 'icon' => 'list', 'route' => 'manager.pb1.index'],
        ['key' => 'promo', 'label' => 'Laporan Promo', 'icon' => 'star', 'route' => 'manager.promo.index'],
        ['key' => 'export', 'label' => 'Export & Cetak', 'icon' => 'printer', 'route' => 'manager.export.index'],
    ];
    $admin = ['name' => $manager['name'] ?? 'Manajer', 'code' => $manager['code'] ?? 'Manajer 01'];
@endphp

<aside class="fixed inset-y-0 left-0 z-40 hidden w-[260px] flex-col bg-pk-side text-white lg:flex" aria-label="Navigasi manajer">
    <div class="px-6 pb-5 pt-6">
        <p class="font-heading text-[20px] font-semibold leading-tight tracking-tight">PERKOCI EATERY</p>
        <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-pk-side-text">Panel Manajer</p>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3" aria-label="Menu manajer">
        @foreach ($menus as $m)
            @php $isActive = $active === $m['key']; @endphp
            <a href="{{ route($m['route']) }}"
               @class([
                   'group flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-semibold transition-colors',
                   'bg-pk-green text-white' => $isActive,
                   'text-white/80 hover:bg-pk-side-2 hover:text-white' => ! $isActive,
               ])
               @if ($isActive) aria-current="page" @endif>
                <span @class([
                    'flex h-8 w-8 items-center justify-center rounded-lg',
                    'bg-white/15' => $isActive,
                    'bg-white/5 group-hover:bg-white/10' => ! $isActive,
                ])>
                    <x-pos.icon :name="$m['icon']" class="h-[18px] w-[18px]" />
                </span>
                <span class="flex-1">{{ $m['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="border-t border-white/10 p-4">
        <div class="rounded-xl bg-pk-side-2 p-4">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-pk-khaki font-heading text-base font-semibold text-pk-brown">
                    {{ strtoupper(substr($admin['name'], 0, 1)) }}
                </span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold">MANAJER</p>
                    <p class="truncate text-xs text-white/60">{{ $admin['code'] }} • {{ $admin['name'] }}</p>
                </div>
                <span class="ml-auto flex h-2.5 w-2.5 rounded-full bg-pk-live" title="Online"></span>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg border border-white/15 px-3 py-2 text-xs font-semibold text-white/80 transition-colors hover:bg-white/10 hover:text-white">
                    <x-pos.icon name="logout" class="h-4 w-4" />
                    Keluar Panel
                </button>
            </form>
        </div>
    </div>
</aside>

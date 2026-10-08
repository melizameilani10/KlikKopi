@extends('layouts.customer')

@section('title', 'Menu Katalog')

@section('content')

    <x-customer.header :table="$table" title="Menu Katalog" />

    <div class="px-4 pb-6 pt-3">

        {{-- STORE INFO --}}
        <x-customer.store-info :table="$table" />


        {{-- SEARCH --}}
        <label class="relative mt-3 block">

            <span class="sr-only">
                Cari menu
            </span>

            {{-- Search icon --}}
            <span
                class="pointer-events-none absolute left-4 top-1/2 z-10 flex h-5 w-5 -translate-y-1/2 items-center justify-center"
                aria-hidden="true"
            >
                <x-pos.icon
                    name="search"
                    class="!h-5 !w-5 shrink-0 text-pk-brown-soft"
                />
            </span>

            <input
                id="menu-search"
                type="search"
                value="{{ $search }}"
                placeholder="Cari menu kopi, artisan tea, atau pastry…"
                autocomplete="off"
                class="h-14 w-full rounded-2xl border-0 bg-white py-3 pl-12 pr-16 text-sm text-pk-brown shadow-sm outline-none transition placeholder:text-pk-brown-soft/70 focus:ring-2 focus:ring-pk-green/20"
            />

            {{-- Filter button --}}
            <button
                type="button"
                aria-label="Filter menu"
                class="absolute right-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-xl bg-pk-brown/10 text-pk-brown-soft"
            >
                <x-customer.icon name="sliders" class="h-4 w-4" />
            </button>

        </label>


        {{-- CATEGORY --}}
        <div
            id="category-chips"
            class="-mx-4 mt-4 flex gap-2.5 overflow-x-auto px-4 pb-1 scrollbar-hide"
            role="tablist"
            aria-label="Kategori menu"
        >

            @foreach ($categories as $c)

                <button
                    type="button"
                    role="tab"
                    data-category="{{ $c }}"
                    aria-selected="{{ $activeCategory === $c ? 'true' : 'false' }}"
                    class="shrink-0 rounded-full px-5 py-2.5 text-sm font-semibold shadow-sm transition
                    {{ $activeCategory === $c
                        ? 'bg-pk-green text-white'
                        : 'bg-white text-pk-brown-soft hover:text-pk-green' }}"
                >
                    {{ $c }}
                </button>

            @endforeach

        </div>


        {{-- PROMO / REKOMENDASI --}}
        <div class="mt-6 flex items-center justify-between">

            <h2 class="flex items-center gap-2 font-heading text-xl font-semibold text-pk-ink">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-pk-olive text-white">
                    <x-customer.icon name="star" class="h-3.5 w-3.5" />
                </span>
                Rekomendasi Hari Ini
            </h2>

            <p class="text-xs font-medium text-pk-brown-soft">
                Spesial Meja {{ $table['no'] ?? '' }}
            </p>

        </div>

        <div class="mt-3">
            <x-customer.promo-card :promo="$promo" />
        </div>


        {{-- MENU HEADER --}}
        <div class="mt-7 flex items-center justify-between">

            <h2 class="font-heading text-xl font-semibold text-pk-ink">
                Pilihan Terbaik
            </h2>

            <p
                id="menu-count"
                class="text-xs font-medium text-pk-brown-soft"
            >
                {{ count($products) }} Menu Tersedia
            </p>

        </div>


        {{-- MENU LIST --}}
        <div
            id="menu-list"
            class="mt-3 grid grid-cols-2 gap-3"
        >

            @forelse ($products as $p)

                <x-customer.menu-card :product="$p" />

            @empty

                <div
                    class="col-span-2 rounded-3xl bg-white p-8 text-center shadow-sm"
                >

                    <p class="font-heading text-lg font-semibold text-pk-ink">
                        Menu tidak ditemukan
                    </p>

                    <p class="mt-1 text-sm text-pk-brown-soft">
                        Coba kata kunci atau kategori lain.
                    </p>

                </div>

            @endforelse

        </div>


        {{-- EMPTY STATE --}}
        <p
            id="menu-empty"
            class="mt-3 hidden rounded-3xl bg-white p-8 text-center text-sm font-semibold text-pk-brown-soft shadow-sm"
        >
            Tidak ada menu yang cocok.
        </p>

    </div>


    {{-- CART --}}
    <x-customer.cart-bar :totals="$totals" />


    {{-- BOTTOM NAVIGATION --}}
    @section('bottomnav')

        <x-customer.bottom-nav
            active="menu"
            :cart-count="$cartCount"
        />

    @endsection

@endsection

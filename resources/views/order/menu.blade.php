@extends('layouts.customer')

@section('title', 'Menu Katalog')

@section('content')

    <x-customer.header :table="$table" title="Menu Katalog" />

    <div class="px-4 pb-6 pt-4">

        {{-- SEARCH --}}
        <label class="relative block">

            <span class="sr-only">
                Cari menu
            </span>

            {{-- Search icon --}}
            <span
                class="pointer-events-none absolute left-3.5 top-1/2 z-10 flex h-4 w-4 -translate-y-1/2 items-center justify-center"
                aria-hidden="true"
            >
                <x-pos.icon
                    name="search"
                    class="!h-4 !w-4 shrink-0 text-pk-brown-soft"
                />
            </span>

            <input
                id="menu-search"
                type="search"
                value="{{ $search }}"
                placeholder="Cari menu kopi, artisan tea, atau pastry…"
                autocomplete="off"
                class="h-11 w-full rounded-xl border border-pk-brown/15 bg-white py-2.5 pl-11 pr-4 text-sm text-pk-brown outline-none transition placeholder:text-pk-brown-soft/70 focus:border-pk-green focus:ring-2 focus:ring-pk-green/10"
            />

        </label>


        {{-- CATEGORY --}}
        <div
            id="category-chips"
            class="mt-3 flex gap-2 overflow-x-auto pb-1 scrollbar-hide"
            role="tablist"
            aria-label="Kategori menu"
        >

            @foreach ($categories as $c)

                <button
                    type="button"
                    role="tab"
                    data-category="{{ $c }}"
                    aria-selected="{{ $activeCategory === $c ? 'true' : 'false' }}"
                    class="shrink-0 rounded-full border px-4 py-2 text-xs font-bold transition
                    {{ $activeCategory === $c
                        ? 'border-pk-green bg-pk-green text-white'
                        : 'border-pk-brown/15 bg-white text-pk-brown hover:border-pk-green/40' }}"
                >
                    {{ $c }}
                </button>

            @endforeach

        </div>


        {{-- PROMO --}}
        <div class="mt-4">
            <x-customer.promo-card :promo="$promo" />
        </div>


        {{-- MENU HEADER --}}
        <div class="mt-5 flex items-center justify-between">

            <h2 class="font-heading text-lg font-semibold text-pk-brown">
                Pilihan Terbaik
            </h2>

            <p
                id="menu-count"
                class="text-xs font-semibold text-pk-brown-soft"
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
                    class="col-span-2 rounded-2xl border border-dashed border-pk-brown/25 bg-white p-8 text-center"
                >

                    <p class="font-heading text-lg font-semibold text-pk-brown">
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
            class="hidden rounded-2xl border border-dashed border-pk-brown/25 bg-white p-8 text-center text-sm font-semibold text-pk-brown-soft"
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

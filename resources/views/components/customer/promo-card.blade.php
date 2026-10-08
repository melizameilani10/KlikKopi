{{-- Kartu promo. Props: $promo --}}
@props(['promo' => []])

<section id="promo" class="overflow-hidden rounded-2xl bg-pk-brown p-4 text-white shadow-card" aria-label="Rekomendasi hari ini">
    <div class="flex items-center justify-between gap-2">
        <p class="text-xs font-bold uppercase tracking-[0.14em] text-white/70">Rekomendasi Hari Ini</p>
        <span class="rounded-full bg-pk-khaki px-2.5 py-1 text-[11px] font-bold text-pk-brown">{{ $promo['badge'] ?? '' }}</span>
    </div>
    <h3 class="mt-1.5 font-heading text-xl font-semibold">{{ $promo['title'] ?? '' }}</h3>
    <p class="mt-1 text-xs leading-relaxed text-white/75">{{ $promo['desc'] ?? '' }}</p>
    <div class="mt-2 flex items-center gap-2">
        <p class="font-heading text-2xl font-semibold text-pk-khaki">Rp {{ number_format($promo['price'] ?? 0, 0, ',', '.') }}</p>
        <p class="text-sm text-white/50 line-through">Rp {{ number_format($promo['old_price'] ?? 0, 0, ',', '.') }}</p>
    </div>
    <button type="button" id="promo-cta" data-promo-items="kopi-susu-aren croissant-butter" class="mt-3 block w-full rounded-xl bg-pk-khaki px-4 py-2.5 text-center text-sm font-bold text-pk-brown">Lihat Menu Promo</button>
</section>

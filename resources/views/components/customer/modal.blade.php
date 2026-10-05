{{-- Modal konfirmasi generik. Props: $id, $title. Slot: konten. Tombol via $actions --}}
@props(['id' => 'modal', 'title' => ''])

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/45 p-4" role="dialog" aria-modal="true" aria-label="{{ $title }}">
    <div class="w-full max-w-[400px] rounded-2xl bg-white p-5 shadow-card">
        <h3 class="font-heading text-lg font-semibold text-pk-brown">{{ $title }}</h3>
        <div class="mt-2 text-sm text-pk-brown-soft">{{ $slot }}</div>
        @isset($actions)
            <div class="mt-4 grid grid-cols-2 gap-2">{{ $actions }}</div>
        @endisset
    </div>
</div>

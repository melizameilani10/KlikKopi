{{-- Modal admin generik. Props: $id, $title --}}
@props(['id' => 'modal', 'title' => ''])

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/45 p-4" role="dialog" aria-modal="true" aria-label="{{ $title }}">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-card">
        <h3 class="font-heading text-xl font-semibold text-pk-brown">{{ $title }}</h3>
        <div class="mt-2 text-sm text-pk-brown-soft">{{ $slot }}</div>
        @isset($actions)
            <div class="mt-4 flex justify-end gap-2">{{ $actions }}</div>
        @endisset
    </div>
</div>

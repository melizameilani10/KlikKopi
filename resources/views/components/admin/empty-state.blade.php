{{-- Empty state admin. Props: $title, $hint?, $actionUrl?, $actionLabel? --}}
@props(['title' => 'Belum ada data', 'hint' => null, 'actionUrl' => null, 'actionLabel' => null])

<div class="rounded-2xl border border-dashed border-pk-brown/25 bg-white p-10 text-center">
    <x-pos.icon name="list" class="mx-auto h-10 w-10 text-pk-brown-soft" />
    <p class="mt-2 font-heading text-lg font-semibold text-pk-brown">{{ $title }}</p>
    @if ($hint)
        <p class="mx-auto mt-1 max-w-md text-sm text-pk-brown-soft">{{ $hint }}</p>
    @endif
    @if ($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="mt-4 inline-block rounded-xl bg-pk-green px-5 py-2.5 text-sm font-bold text-white">{{ $actionLabel }}</a>
    @endif
</div>

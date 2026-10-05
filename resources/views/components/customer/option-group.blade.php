{{-- Grup opsi kustomisasi (radio / checkbox). Props: $group, $index --}}
@props(['group' => [], 'index' => 0])

<fieldset class="rounded-2xl border border-pk-brown/10 bg-white p-4 shadow-card" data-option-group="{{ $group['key'] }}" data-option-type="{{ $group['type'] }}" @if ($group['required'] ?? false) data-option-required="true" @endif>
    <legend class="px-1 text-sm font-bold text-pk-brown">
        {{ $group['title'] }}
        @if ($group['required'] ?? false)
            <span class="ml-1 rounded bg-[#fdf0e1] px-1.5 py-0.5 text-[10px] font-bold text-[#9a5b14]">WAJIB</span>
        @endif
    </legend>
    <div class="mt-1 space-y-1">
        @foreach ($group['options'] as $opt)
            <label class="flex cursor-pointer items-center gap-3 rounded-xl px-2 py-2.5 hover:bg-pk-sand-2 has-[:checked]:bg-[#eef5ea] has-[:checked]:ring-1 has-[:checked]:ring-pk-green">
                <input type="{{ $group['type'] === 'radio' ? 'radio' : 'checkbox' }}"
                    name="opt_{{ $group['key'] }}{{ $group['type'] === 'checkbox' ? '[]' : '' }}"
                    value="{{ $opt['value'] }}" data-delta="{{ $opt['delta'] }}" data-label="{{ $opt['value'] }}"
                    class="h-5 w-5 shrink-0 accent-[#195905]" @if ($group['type'] === 'radio' && $loop->first && ($group['required'] ?? false)) checked @endif>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-bold text-pk-brown">{{ $opt['value'] }}</span>
                    @if (! empty($opt['desc']))
                        <span class="block text-xs text-pk-brown-soft">{{ $opt['desc'] }}</span>
                    @endif
                </span>
                @if (($opt['delta'] ?? 0) > 0)
                    <span class="shrink-0 text-xs font-bold text-pk-green">+Rp {{ number_format($opt['delta'], 0, ',', '.') }}</span>
                @endif
            </label>
        @endforeach
    </div>
</fieldset>

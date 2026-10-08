{{-- Ikon customer (proxy ke sprite POS). Props: $name, sisanya diteruskan sebagai atribut --}}
@props(['name'])

<x-pos.icon :name="$name" {{ $attributes->merge(['class' => 'h-5 w-5']) }} />

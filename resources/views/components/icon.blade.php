@props(['name'])

<svg {{ $attributes->class(['icon']) }} aria-hidden="true" focusable="false"><use href="#i-{{ $name }}"></use></svg>

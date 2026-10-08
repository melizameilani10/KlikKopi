<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#195905">
    <title>@yield('title') · PERKOCI EATERY Self-Order</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tailwind via CDN (tanpa build). Token warna sama dengan POS kasir. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        pk: {
                            'green': '#195905', 'green-dark': '#0c4000', 'green-hover': '#1f6b09',
                            'brown': '#3d2b1f', 'brown-soft': '#705a4c',
                            'khaki': '#bdb76b', 'olive': '#65601e',
                            'amber': '#c97c2c', 'danger': '#b3261e',
                            'bg': '#fcf9f2', 'ink': '#1c1c18',
                            'sand': '#f0eee7', 'sand-2': '#f6f4f0', 'paper': '#faf9f7',
                            'mint': '#e7eee5',
                            'side-text': '#dec1af', 'live': '#93d878',
                        },
                    },
                    fontFamily: {
                        heading: ['Fraunces', 'Georgia', 'Times New Roman', 'serif'],
                        sans: ['Plus Jakarta Sans', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'Arial', 'sans-serif'],
                    },
                    boxShadow: {
                        card: '0 1px 2px rgba(61,43,31,.06), 0 8px 24px -14px rgba(61,43,31,.18)',
                        bar: '0 -8px 24px -12px rgba(61,43,31,.25)',
                    },
                },
            },
        };
    </script>
    <style>
        html { -webkit-text-size-adjust: 100%; }
        body { text-rendering: optimizeLegibility; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        @media (prefers-reduced-motion: reduce) { * { transition-duration: .01ms !important; } }
        {{-- Cetak struk customer: hanya #receipt yang keluar di kertas. --}}
        @media print {
            body.printing-receipt header, body.printing-receipt nav,
            body.printing-receipt #order-toasts, body.printing-receipt .no-print { display: none !important; }
            body.printing-receipt * { visibility: hidden; }
            body.printing-receipt #receipt, body.printing-receipt #receipt * { visibility: visible; }
            body.printing-receipt #receipt {
                position: fixed; left: 0; top: 0; width: 72mm; max-width: 100%;
                margin: 0; box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-pk-bg font-sans text-pk-ink antialiased">
    @include('partials.pos-sprite')

    <div class="mx-auto min-h-screen w-full max-w-[480px] bg-pk-bg pb-28 shadow-card">
        @yield('content')

        @hasSection('bottomnav')
            @yield('bottomnav')
        @else
            <x-customer.bottom-nav :active="$activeNav ?? 'menu'" :cart-count="$cartCount ?? 0" />
        @endif
    </div>

    <div id="order-toasts" class="pointer-events-none fixed bottom-24 left-1/2 z-50 flex w-[min(92vw,420px)] -translate-x-1/2 flex-col gap-2" aria-live="polite"></div>

    <script src="{{ asset('js/customer-order.js') }}" defer></script>
    @stack('scripts')
</body>
</html>

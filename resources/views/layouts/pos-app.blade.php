<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#31312c">
    <title>@yield('title') · {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tailwind via CDN (tanpa build). Untuk produksi, pindahkan ke Vite. --}}
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
                            'mint': '#e7eee5', 'lime': '#bdf2a3',
                            'peach': '#fbdccb', 'peach-chip': '#f8dac8', 'peach-soft': '#fbece3', 'peach-icon': '#f9dccd',
                            'khaki-soft': '#f0eedf', 'khaki-icon': '#e8e6cf',
                            'side': '#31312c', 'side-2': '#43433d', 'side-3': '#55544f', 'side-4': '#5d5450',
                            'side-text': '#dec1af', 'live': '#93d878',
                        },
                    },
                    fontFamily: {
                        heading: ['Fraunces', 'Georgia', 'Times New Roman', 'serif'],
                        sans: ['Plus Jakarta Sans', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'Arial', 'sans-serif'],
                    },
                    boxShadow: {
                        card: '0 1px 2px rgba(61,43,31,.06), 0 8px 24px -14px rgba(61,43,31,.18)',
                    },
                },
            },
        };
    </script>
    <style>
        html { -webkit-text-size-adjust: 100%; }
        .pk-spinner { display: none; width: 16px; height: 16px; border: 2px solid currentColor; border-top-color: transparent; border-radius: 50%; animation: pk-spin .7s linear infinite; }
        [data-loading="true"] > .pk-spinner { display: inline-block; }
        @keyframes pk-spin { to { transform: rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) { * { transition-duration: .01ms !important; } }
        {{-- Cetak struk: hanya #receipt yang keluar di kertas (lebar thermal 72mm). --}}
        @media print {
            aside, header, nav, #pos-overlay, #pos-toasts, .no-print { display: none !important; }
            body.printing-receipt * { visibility: hidden; }
            body.printing-receipt #receipt, body.printing-receipt #receipt * { visibility: visible; }
            body.printing-receipt #receipt {
                position: fixed; left: 0; top: 0; width: 72mm; max-width: 100%;
                margin: 0; box-shadow: none !important; border: none !important;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-pk-bg font-sans text-pk-ink antialiased">
    @include('partials.pos-sprite')

    <x-pos.sidebar :kasir="$kasir" :badge="$sidebarBadge ?? 0" :active="$active ?? 'dashboard'" />
    <div id="pos-overlay" class="fixed inset-0 z-30 hidden bg-black/40 lg:hidden" aria-hidden="true"></div>

    <div class="lg:pl-[280px]">
        <x-pos.topbar :kasir="$kasir" />

        <main class="mx-auto w-full max-w-[1480px] px-4 pb-10 pt-5 sm:px-6 xl:px-8">
            @yield('content')
        </main>
    </div>

    <div id="pos-toasts" class="pointer-events-none fixed bottom-4 right-4 z-50 flex w-[min(92vw,360px)] flex-col gap-2" aria-live="polite"></div>

    <script src="{{ asset('js/pos-dashboard.js') }}?v={{ filemtime(public_path('js/pos-dashboard.js')) }}" defer></script>
</body>
</html>

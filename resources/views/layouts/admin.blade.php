<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#195905">
    <title>@yield('title') · {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tailwind via CDN (tanpa build). Token sama dengan POS kasir. --}}
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
                            'side': '#31312c', 'side-2': '#43433d',
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
        @media print { aside, header.admin-topbar, .no-print { display: none !important; } }
        @media (prefers-reduced-motion: reduce) { * { transition-duration: .01ms !important; } }
    </style>
</head>
<body class="min-h-screen bg-pk-bg font-sans text-pk-ink antialiased">
    @include('partials.pos-sprite')

    @hasSection('sidebar')
        @yield('sidebar')
    @else
        <x-admin.sidebar :admin="$admin" :active="$active ?? 'dashboard'" />
    @endif

    <div class="lg:pl-[260px]">
        <header class="admin-topbar sticky top-0 z-20 border-b border-pk-brown/10 bg-pk-bg/95 backdrop-blur">
            <div class="mx-auto flex w-full max-w-[1280px] flex-wrap items-center gap-2 px-4 py-3 sm:px-6">
                <p class="text-sm text-pk-brown-soft">{{ ($admin ?? $manager ?? [])['today_label'] ?? '' }}</p>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-pk-mint px-2.5 py-1 text-[11px] font-bold text-pk-green">
                    <span class="h-1.5 w-1.5 rounded-full bg-pk-green"></span>ONLINE
                </span>
                <div class="ml-auto flex items-center gap-2">
                    @yield('top-actions')
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-[1280px] px-4 pb-10 pt-5 sm:px-6">
            @if (session('success'))
                <div class="mb-4 flex items-start gap-2 rounded-2xl border border-pk-green/30 bg-[#eef5ea] p-4 text-sm font-semibold text-pk-green" role="status">
                    <x-pos.icon name="check" class="mt-0.5 h-5 w-5 shrink-0" />
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-2xl border border-pk-danger/30 bg-[#fbe9e7] p-4 text-sm font-semibold text-pk-danger" role="alert">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <div id="admin-toasts" class="pointer-events-none fixed bottom-4 right-4 z-50 flex w-[min(92vw,360px)] flex-col gap-2" aria-live="polite"></div>

    <script src="{{ asset('js/admin-panel.js') }}" defer></script>
    @stack('scripts')
</body>
</html>

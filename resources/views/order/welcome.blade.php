@extends('layouts.customer')

@section('title', 'Selamat Datang')

{{-- Cover tidak memakai bottom navigation sesuai desain (komentar agar section terhitung ada). --}}
@section('bottomnav')<!-- cover: tanpa navigasi bawah -->@endsection

@section('content')
    @php
        $tableLabel   = $table['label'] ?? 'Meja 08';
        $tableArea    = $table['area'] ?? 'Indoor AC';
        $redirectUrl  = route('order.menu');
        $redirectMs   = 3500; // lama loading sebelum pindah otomatis
    @endphp

    <div class="flex min-h-[100dvh] w-full flex-col bg-[var(--pk-bg)] font-[family-name:var(--pk-font-body)] text-[var(--pk-brown-soft)]">
        <div class="mx-auto flex w-full max-w-[430px] flex-1 flex-col px-6 pb-10 pt-16 text-center">

            <main class="flex flex-1 flex-col items-center justify-center">
                <p class="text-[12px] font-bold uppercase tracking-[0.28em] text-[var(--pk-khaki)]">Sesi Meja Aktif</p>

                <h1 class="mt-4 font-[family-name:var(--pk-font-heading)] text-[32px] font-semibold leading-[1.2] text-[var(--pk-brown)]">
                    Selamat Datang di<br>Perkoci Eatery
                </h1>

                <p class="mt-3 text-[11px] font-semibold uppercase tracking-[0.32em] text-[var(--pk-brown-soft)]">Artisanal Coffee &amp; Kitchen</p>

                <p class="mt-6 text-[16px] font-bold text-[var(--pk-green)]">{{ $tableLabel }} &bull; {{ $tableArea }}</p>

                <p class="mt-2 flex items-center justify-center gap-2 text-[13px] font-medium">
                    <span class="h-2 w-2 rounded-full bg-[var(--pk-green)]" aria-hidden="true"></span>
                    Terhubung otomatis via QR Meja
                </p>
            </main>

            {{-- Loading & pindah otomatis --}}
            <section id="welcome-loader"
                     class="pt-10 text-center"
                     data-redirect-url="{{ $redirectUrl }}"
                     data-redirect-delay="{{ $redirectMs }}"
                     aria-live="polite">

                <p class="text-[13px] font-medium">Menyiapkan rekomendasi barista hari ini...</p>

                <div id="welcome-progress"
                     class="mx-auto mt-4 h-1.5 w-full overflow-hidden rounded-full bg-[#e5e2db]"
                     role="progressbar" aria-label="Memuat menu" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                    <div id="welcome-progress-bar" class="h-full w-0 rounded-full bg-[var(--pk-green)]"></div>
                </div>

                <p class="mt-8 text-[12px] font-semibold uppercase tracking-[0.18em] text-[var(--pk-khaki)]">Perkoci Preserving Culinary Warmth</p>

                <noscript>
                    <a href="{{ $redirectUrl }}" class="mt-4 inline-block text-[14px] font-semibold text-[var(--pk-green)] underline">Lanjut ke menu</a>
                </noscript>
            </section>
        </div>
    </div>

    <script>
        (() => {
            const loader = document.getElementById('welcome-loader');
            if (!loader) return;

            const bar      = document.getElementById('welcome-progress-bar');
            const track    = document.getElementById('welcome-progress');
            const url      = loader.dataset.redirectUrl;
            const duration = Number(loader.dataset.redirectDelay) || 3500;
            const start    = performance.now();
            let redirected = false;

            function go() {
                if (redirected) return;
                redirected = true;
                window.location.assign(url);
            }

            function frame(now) {
                const t   = Math.min(1, (now - start) / duration);
                const pct = Math.round((1 - Math.pow(1 - t, 2)) * 100); // melambat di ujung

                bar.style.width = pct + '%';
                track.setAttribute('aria-valuenow', String(pct));

                if (t < 1) requestAnimationFrame(frame);
            }

            requestAnimationFrame(frame);
            setTimeout(go, duration + 150); // tetap pindah walau tab sedang di latar belakang

            // Tombol Back dari halaman menu: muat ulang agar animasi mulai dari awal.
            window.addEventListener('pageshow', (event) => {
                if (event.persisted) window.location.reload();
            });
        })();
    </script>
@endsection

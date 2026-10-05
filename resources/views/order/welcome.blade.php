@extends('layouts.customer')

@section('title', 'Selamat Datang')

@section('content')
    {{-- Fraunces versi miring dibutuhkan untuk baris "Perkoci Eatery". Aman walau layout sudah memuat font. --}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500;1,9..144,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">

    @php
        $tableLabel   = $table['label']   ?? 'Meja 08';
        $tableArea    = $table['area']    ?? $table['detail'] ?? 'Area Indoor AC';
        $tableService = $table['service'] ?? 'Layanan Dine-in Aktif';
        $redirectUrl  = route('order.menu');
        $redirectMs   = 3500; // lama loading sebelum pindah otomatis
    @endphp

    <div class="relative isolate flex min-h-[100dvh] w-full flex-col overflow-hidden bg-[#fcf9f2] font-sans text-[#705a4c]"
         style="background-image:
            radial-gradient(120% 52% at 0% 0%, #e5e9da 0%, rgba(229,233,218,0) 62%),
            radial-gradient(95% 48% at 100% 84%, #faefe5 0%, rgba(250,239,229,0) 72%);">

        <div class="mx-auto flex w-full max-w-[430px] flex-1 flex-col px-6 pb-12 pt-10">

            {{-- Bar atas --}}
            <header class="flex items-center justify-between text-[12.5px]">
                <p class="flex items-center gap-2 font-medium uppercase tracking-[0.14em] text-[#877668]">
                    <span class="h-2.5 w-2.5 rounded-full bg-[#41752f]" aria-hidden="true"></span>
                    Digital Order
                </p>
                <p class="flex items-center gap-1.5 font-medium text-[#8c796e]">
                    <svg class="h-[18px] w-[18px] text-[#41752f]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 9a14 14 0 0 1 19 0M5.8 12.6a9.3 9.3 0 0 1 12.4 0M9 16a4.6 4.6 0 0 1 6 0"/><circle cx="12" cy="19.4" r="1" fill="currentColor"/></svg>
                    Perkoci Guest
                </p>
            </header>

            {{-- Logo, judul, deskripsi, info meja --}}
            <main class="mt-14 flex flex-col items-center text-center">

                <div class="relative">
                    <div class="grid h-36 w-36 place-items-center rounded-full bg-white shadow-[0_0_0_1px_rgba(141,210,114,0.18),0_0_34px_6px_rgba(141,210,114,0.22),0_20px_32px_-14px_rgba(61,43,31,0.28)]">
                        {{-- Ganti public/images/perkoci-logo.svg dengan logo asli Perkoci --}}
                        <img src="{{ asset('images/perkoci-logo.svg') }}" alt="Perkoci Eatery" class="h-auto w-[104px]">
                    </div>
                    <span class="absolute -bottom-0.5 right-0 grid h-9 w-9 place-items-center rounded-full bg-[#195905] text-white shadow-md" aria-hidden="true">
                        <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 8h11v5a5 5 0 0 1-5 5h-1a5 5 0 0 1-5-5z"/><path d="M16 9h1.5a2 2 0 0 1 0 4H16"/><path d="M9 4.5v1.5M12 4.5v1.5"/></svg>
                    </span>
                </div>

                <h1 class="mt-7 font-heading text-[29px] leading-[1.25]">
                    <span class="block font-semibold text-[#0b3f00]">Selamat Datang di</span>
                    <span class="block font-medium italic text-[#1c1c18]">Perkoci Eatery</span>
                </h1>

                <p class="mt-3.5 max-w-[340px] text-[16px] leading-[1.65] text-[#705a4c]">
                    Menghadirkan kenikmatan racikan kopi pilihan &amp; hidangan hangat khas nusantara.
                </p>

                <section class="mt-11 w-full rounded-2xl bg-white px-4 py-5 shadow-[0_1px_2px_rgba(61,43,31,0.06),0_10px_26px_-16px_rgba(61,43,31,0.2)]" aria-label="Informasi meja">
                    <p class="mx-auto inline-flex items-center gap-2 rounded-full bg-[#fae8de] px-3.5 py-1.5 text-[12px] font-bold uppercase tracking-[0.08em] text-[#28180d]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 8V5a1 1 0 0 1 1-1h3M16 4h3a1 1 0 0 1 1 1v3M20 16v3a1 1 0 0 1-1 1h-3M8 20H5a1 1 0 0 1-1-1v-3"/><rect x="8" y="8" width="3" height="3"/><rect x="13" y="8" width="3" height="3"/><rect x="8" y="13" width="3" height="3"/><path d="M13 13h3v3"/></svg>
                        Tersambung via Meja
                    </p>

                    <p class="mt-3.5 flex items-center justify-center gap-2.5">
                        <svg class="h-[22px] w-[22px] text-[#0c4000]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9h18M5 9v10M19 9v10M8 9V5h8v4"/></svg>
                        <span class="text-[18px] font-bold text-[#0c4000]">{{ $tableLabel }}</span>
                        <span class="h-1.5 w-1.5 rounded-full bg-[#c9c6bd]" aria-hidden="true"></span>
                        <span class="text-[16px] font-medium text-[#705a4c]">{{ $tableArea }}</span>
                    </p>

                    <p class="mt-2.5 flex items-center justify-center gap-1.5 text-[12.5px] font-medium text-[#65601e]">
                        <svg class="h-[15px] w-[15px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3v7a2 2 0 0 0 2 2v9M10 3v7M8 3v7M17 21V3c-2.5 1.5-4 4.5-4 8h4"/></svg>
                        {{ $tableService }}
                    </p>
                </section>
            </main>

            {{-- Loading & pindah otomatis --}}
            <section id="welcome-loader"
                     class="mt-auto pt-14 text-center"
                     data-redirect-url="{{ $redirectUrl }}"
                     data-redirect-delay="{{ $redirectMs }}"
                     aria-live="polite">

                <p class="flex items-center justify-center gap-2.5 text-[13.5px] font-medium text-[#41493c]">
                    <svg class="h-[18px] w-[18px] animate-spin text-[#195905]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M21 12a9 9 0 1 1-6.2-8.6"/></svg>
                    Menyiapkan aroma &amp; hidangan hari ini...
                </p>

                <div id="welcome-progress"
                     class="mx-auto mt-3.5 h-1.5 w-full overflow-hidden rounded-full bg-[#e5e2db]"
                     role="progressbar" aria-label="Memuat menu" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                    <div id="welcome-progress-bar" class="h-full w-0 rounded-full bg-[#195905]"></div>
                </div>

                <p class="mx-auto mt-4 max-w-[260px] text-[13.5px] leading-relaxed text-[#717a6b]">
                    Harap tunggu sejenak, Anda akan dialihkan otomatis
                </p>

                <noscript>
                    <a href="{{ $redirectUrl }}" class="mt-4 inline-block text-[14px] font-semibold text-[#195905] underline">Lanjut ke menu</a>
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

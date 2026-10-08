<?php

namespace App\Support;

use App\Models\Meja;
use App\Models\Produk;
use Illuminate\Support\Facades\Schema;

/**
 * Data layer Customer Self-Order PERKOCI EATERY.
 *
 * Prioritas: DATA ASLI dari database (tabel `produk` & `meja`).
 * Jika tabel kosong / tidak ada, fallback ke dummy terstruktur
 * yang field-nya 1:1 dengan kolom DB sehingga mudah diganti.
 *
 * Cart & order disimpan di session (tanpa ubah schema/auth).
 * Session keys: order.table, order.cart, order.placed, order.waiter_at
 */
class CustomerData
{
    public static function rupiah(int|float $n): string
    {
        return 'Rp '.number_format((float) $n, 0, ',', '.');
    }

    /**
     * Kategori dari database (produk aktif) agar chip filter selalu
     * sesuai data asli. Fallback ke daftar desain bila DB kosong.
     */
    public static function categories(): array
    {
        try {
            if (Schema::hasTable('produk')) {
                $cats = Produk::query()
                    ->where('status', 'aktif')
                    ->whereNotNull('kategori')
                    ->distinct()
                    ->orderBy('kategori')
                    ->pluck('kategori')
                    ->all();
                if (! empty($cats)) {
                    return array_merge(['Semua'], $cats);
                }
            }
        } catch (\Throwable) {
        }

        return [
            'Semua',
            'Signature Coffee',
            'Manual Brew',
            'Non-Coffee',
            'Makanan Utama',
            'Snack & Dessert',
        ];
    }

    /* ---------- MEJA (DB-first, fallback Meja 08) ---------- */

    /**
     * Resolve konteks meja dari ?meja= / ?qr= / session.
     * Return: ['no' => '08', 'label' => 'Meja 08', 'area' => ..., 'type' => 'Dine-in', 'status' => 'AKTIF', 'from_db' => bool]
     */
    public static function resolveTable(?string $mejaParam = null, ?string $qrParam = null): array
    {
        $fallback = [
            'no' => '08', 'label' => 'Meja 08',
            'area' => 'Indoor AC', 'type' => 'Dine-in',
            'detail' => 'Area Indoor Non-Smoking • Lantai 1',
            'status' => 'AKTIF', 'from_db' => false,
        ];

        try {
            if (! Schema::hasTable('meja')) {
                return $fallback;
            }
            $row = null;
            if ($qrParam) {
                $row = Meja::where('qr_code', $qrParam)->first();
            }
            if (! $row && $mejaParam) {
                $norm = ltrim($mejaParam, '0');
                $row = Meja::where('no_meja', $mejaParam)->first()
                    ?? Meja::where('no_meja', $norm)->first();
            }
            if (! $row) {
                return $fallback;
            }

            return [
                'no' => $row->no_meja,
                'label' => 'Meja '.$row->no_meja,
                'area' => 'Indoor AC',
                'type' => 'Dine-in',
                'detail' => 'Area Indoor Non-Smoking • Lantai 1',
                'status' => $row->status === 'terisi' ? 'TERISI' : 'AKTIF',
                'from_db' => true,
            ];
        } catch (\Throwable) {
            return $fallback;
        }
    }

    /* ---------- KATALOG (DB-first, fallback dummy) ---------- */

    /** Produk DB (status aktif) dipetakan ke shape katalog. [] bila kosong. */
    public static function dbProducts(): array
    {
        try {
            if (! Schema::hasTable('produk')) {
                return [];
            }
            $rows = Produk::where('status', 'aktif')->orderBy('id_produk')->get();

            // Stok per produk (bila tercatat): jumlah <= 0 → otomatis Habis di katalog.
            $stock = [];
            try {
                if (Schema::hasTable('stok')) {
                    $stock = \DB::table('stok')->pluck('jumlah_stok', 'id_produk')->all();
                }
            } catch (\Throwable) {
            }

            return $rows->map(fn ($p) => [
                'id' => 'db-'.$p->id_produk,
                'db_id' => $p->id_produk,
                'name' => $p->nama_produk,
                'desc' => $p->deskripsi ?: $p->nama_produk,
                'price' => (int) $p->harga,
                'category' => $p->kategori ?: 'Signature Coffee',
                'tags' => [],
                'rating' => null,
                'sold_out' => isset($stock[$p->id_produk]) && (int) $stock[$p->id_produk] <= 0,
                'estimate' => '4–6 Menit',
                'customizable' => false,
                'options' => [],
            ])->all();
        } catch (\Throwable) {
            return [];
        }
    }

    public static function products(): array
    {
        $db = self::dbProducts();
        if (! empty($db)) {
            return $db;
        }

        return self::dummyProducts();
    }

    public static function findProduct(string $id): ?array
    {
        foreach (self::products() as $p) {
            if ((string) $p['id'] === (string) $id) {
                return $p;
            }
        }

        return null;
    }

    /** Dummy sementara — struktur siap diganti rows tabel `produk`. */
    private static function dummyProducts(): array
    {
        $drinkOptions = [
            [
                'key' => 'size', 'title' => 'Ukuran Minuman', 'required' => true,
                'type' => 'radio',
                'options' => [
                    ['value' => 'Regular', 'desc' => '360 ml (Standard)', 'delta' => 0],
                    ['value' => 'Large', 'desc' => '500 ml', 'delta' => 5000],
                ],
            ],
            [
                'key' => 'sugar', 'title' => 'Tingkat Manis Aren', 'required' => false,
                'type' => 'radio',
                'options' => [
                    ['value' => 'Normal', 'desc' => '100%', 'delta' => 0],
                    ['value' => 'Less Sugar', 'desc' => '50%', 'delta' => 0],
                    ['value' => 'No Sugar', 'desc' => '0%', 'delta' => 0],
                ],
            ],
            [
                'key' => 'ice', 'title' => 'Banyaknya Es', 'required' => false,
                'type' => 'radio',
                'options' => [
                    ['value' => 'Normal Ice', 'desc' => null, 'delta' => 0],
                    ['value' => 'Less Ice', 'desc' => null, 'delta' => 0],
                    ['value' => 'No Ice', 'desc' => null, 'delta' => 0],
                ],
            ],
            [
                'key' => 'topping', 'title' => 'Topping & Racikan Ekstra', 'required' => false,
                'type' => 'checkbox',
                'options' => [
                    ['value' => 'Espresso Shot Ekstra', 'desc' => null, 'delta' => 6000],
                    ['value' => 'Grass Jelly Organik', 'desc' => null, 'delta' => 4000],
                    ['value' => 'Oat Milk Substitution', 'desc' => null, 'delta' => 7000],
                ],
            ],
        ];

        return [
            [
                'id' => 'kopi-susu-aren', 'name' => 'Kopi Susu Perkoci Aren',
                'desc' => 'Espresso robusta & arabika blend house Perkoci dengan susu creamy dan gula aren murni organik.',
                'short' => 'Espresso blend, aren cair...',
                'price' => 24000, 'category' => 'Signature Coffee',
                'tags' => ['Nutty & Bold', 'Brown Sugar Caramel', 'Velvety Finish'],
                'rating' => '4.9', 'sold_out' => false, 'estimate' => '4–6 Menit',
                'customizable' => true, 'options' => $drinkOptions,
            ],
            [
                'id' => 'v60-gayo', 'name' => 'V60 Single Origin Gayo',
                'desc' => 'Seduhan manual single origin Gayo Arabica, diseduh fresh per pesanan oleh barista.',
                'short' => 'Notes: black tea, citrus...',
                'price' => 28000, 'category' => 'Manual Brew',
                'tags' => ['Floral', 'Citrus Bright', 'Clean Cup'],
                'rating' => '4.8', 'sold_out' => false, 'estimate' => '5–7 Menit',
                'customizable' => true, 'options' => [$drinkOptions[0], $drinkOptions[1]],
            ],
            [
                'id' => 'matcha-latte', 'name' => 'Artisan Matcha Latte',
                'desc' => 'Pure Kyoto ceremonial matcha dipadukan susu segar, creamy dan menenangkan.',
                'short' => 'Pure Kyoto ceremonial matcha',
                'price' => 26000, 'category' => 'Non-Coffee',
                'tags' => ['Earthy', 'Creamy', 'Ceremonial Grade'],
                'rating' => '4.8', 'sold_out' => false, 'estimate' => '4–6 Menit',
                'customizable' => true, 'options' => $drinkOptions,
            ],
            [
                'id' => 'caramel-macchiato', 'name' => 'Caramel Macchiato',
                'desc' => 'Vanilla sweet milk dengan espresso shot dan drizzle karamel house-made.',
                'short' => 'Vanilla sweet milk, espresso...',
                'price' => 29000, 'category' => 'Signature Coffee',
                'tags' => ['Sweet', 'Caramel Drizzle', 'Double Shot'],
                'rating' => '4.7', 'sold_out' => false, 'estimate' => '4–6 Menit',
                'customizable' => true, 'options' => $drinkOptions,
            ],
            [
                'id' => 'cold-brew-nitro', 'name' => 'Cold Brew Nitro',
                'desc' => 'Seduhan dingin 16 jam dengan nitro gas, tekstur silky dan bold.',
                'short' => 'Seduhan 16 jam nitro gas...',
                'price' => 30000, 'category' => 'Signature Coffee',
                'tags' => ['Bold', 'Silky Nitro', 'Slow Steeped'],
                'rating' => '4.9', 'sold_out' => true, 'estimate' => '2–3 Menit',
                'customizable' => false, 'options' => [],
            ],
            [
                'id' => 'nasi-goreng-kampung', 'name' => 'Nasi Goreng Kampung',
                'desc' => 'Nasi goreng autentik dengan telur, kerupuk, dan acar segar. Level pedas bisa diatur.',
                'short' => 'Autentik, telur & kerupuk...',
                'price' => 32000, 'category' => 'Makanan Utama',
                'tags' => ['Wok Hei', 'Pedas Sesuai Selera'],
                'rating' => '4.8', 'sold_out' => false, 'estimate' => '10–15 Menit',
                'customizable' => true,
                'options' => [
                    [
                        'key' => 'spice', 'title' => 'Level Pedas', 'required' => true,
                        'type' => 'radio',
                        'options' => [
                            ['value' => 'Tidak Pedas', 'desc' => null, 'delta' => 0],
                            ['value' => 'Pedas Sedang', 'desc' => null, 'delta' => 0],
                            ['value' => 'Pedas Extra', 'desc' => null, 'delta' => 0],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'croissant-butter', 'name' => 'Croissant Butter',
                'desc' => 'Double baked dengan almond, renyah di luar lembut di dalam. Dipanggang saat dipesan.',
                'short' => 'Double baked dengan almond',
                'price' => 25000, 'category' => 'Snack & Dessert',
                'tags' => ['Buttery', 'Fresh Baked'],
                'rating' => '4.7', 'sold_out' => false, 'estimate' => '5–8 Menit',
                'customizable' => false, 'options' => [],
            ],
        ];
    }

    public static function promo(): array
    {
        // TODO: ganti dengan query tabel promo bila modul promo tersedia.
        return [
            'badge' => 'Spesial Meja 08',
            'title' => '-20% Promo Meja',
            'desc' => 'Es Kopi Perkoci & Almond Croissant — paduan gula aren murni & pastry renyah',
            'price' => 39200,
            'old_price' => 49000,
        ];
    }

    /* ---------- CART (session) ---------- */

    public static function cart(): array
    {
        return session('order.cart', []);
    }

    public static function cartCount(): int
    {
        return array_sum(array_column(self::cart(), 'qty'));
    }

    /** Harga 1 line = (harga produk + delta opsi) * qty */
    public static function lineTotal(array $line): int
    {
        $unit = (int) ($line['base_price'] ?? 0);
        foreach ($line['options'] ?? [] as $opt) {
            $unit += (int) ($opt['delta'] ?? 0);
        }

        return $unit * (int) ($line['qty'] ?? 1);
    }

    public static function totals(?array $cart = null): array
    {
        $cart ??= self::cart();
        $subtotal = 0;
        foreach ($cart as $line) {
            $subtotal += self::lineTotal($line);
        }
        $pb1 = (int) round($subtotal * 0.10);
        $service = $subtotal > 0 ? 2000 : 0;

        return [
            'subtotal' => $subtotal,
            'pb1' => $pb1,
            'service' => $service,
            'total' => $subtotal + $pb1 + $service,
            'count' => array_sum(array_column($cart, 'qty')),
        ];
    }

    public static function paymentMethods(): array
    {
        return [
            ['key' => 'qris', 'label' => 'QRIS', 'hint' => 'Scan sekali bayar', 'icon' => 'qr'],
            ['key' => 'ewallet', 'label' => 'E-Wallet', 'hint' => 'GoPay / OVO / Dana', 'icon' => 'banknote'],
            ['key' => 'debit', 'label' => 'Debit', 'hint' => 'Bayar di kasir / EDC', 'icon' => 'card'],
            ['key' => 'paylater', 'label' => 'Bayar Nanti', 'hint' => 'Bayar di kasir', 'icon' => 'clock'],
        ];
    }
}

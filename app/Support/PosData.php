<?php

namespace App\Support;

/**
 * Data contoh POS Kasir PERKOCI EATERY.
 *
 * Single source of truth sementara untuk UI kasir.
 * TODO: ganti setiap method dengan query ke tabel
 * pesanan / detail_pesanan / pembayaran / meja / produk
 * saat modul backend transaksi sudah siap.
 *
 * Struktur ticket disengaja konsisten dengan brief:
 * #A-24 Budi Santoso (Meja 08, menunggu pembayaran, Rp 68.000)
 * #A-25 Hendra G. (Meja 03, sedang diracik)
 * #A-26 Rina W. (Takeaway 02, siap diantar)
 */
class PosData
{
    public static function rupiah(int|float $n): string
    {
        return 'Rp '.number_format((float) $n, 0, ',', '.');
    }

    /** Daftar tiket aktif (live ticket stream). */
    public static function tickets(): array
    {
        return [
            [
                'code' => 'A-24',
                'display_code' => '#A-24',
                'customer' => 'Budi Santoso',
                'phone' => '081234567890',
                'table' => 'Meja 08',
                'area' => 'Indoor AC',
                'type' => 'dinein',
                'time' => '14:32',
                'ago' => '3 mnt lalu',
                'elapsed' => '00:03:12',
                'status' => 'waiting_payment',
                'status_label' => 'Menunggu Pembayaran',
                'items_count' => 2,
                'items' => [
                    ['name' => 'Kopi Susu Aren', 'qty' => 1, 'price' => 25000, 'note' => 'Less sugar, es sedikit'],
                    ['name' => 'Croissant Butter', 'qty' => 1, 'price' => 35000, 'note' => 'Hangatkan'],
                ],
                'subtotal' => 60000,
                'pb1' => 6000,
                'service' => 2000,
                'total' => 68000,
                'note' => 'Es untuk kopinya sedikit saja.',
                'paid' => false,
            ],
            [
                'code' => 'A-25',
                'display_code' => '#A-25',
                'customer' => 'Hendra G.',
                'phone' => '082112009988',
                'table' => 'Meja 03',
                'area' => 'Indoor AC',
                'type' => 'dinein',
                'time' => '14:28',
                'ago' => '7 mnt lalu',
                'elapsed' => '00:07:45',
                'status' => 'cooking',
                'status_label' => 'Sedang Diracik',
                'items_count' => 3,
                'items' => [
                    ['name' => 'V60 Gayo Arabica', 'qty' => 1, 'price' => 34000, 'note' => 'Hot, light roast'],
                    ['name' => 'Artisan Matcha Latte', 'qty' => 1, 'price' => 36000, 'note' => 'Oatmilk'],
                    ['name' => 'Banana Cake Slice', 'qty' => 1, 'price' => 22000, 'note' => null],
                ],
                'subtotal' => 92000,
                'pb1' => 0,
                'service' => 0,
                'total' => 92000,
                'note' => null,
                'paid' => true,
                'paid_via' => 'QRIS',
            ],
            [
                'code' => 'A-26',
                'display_code' => '#A-26',
                'customer' => 'Rina W.',
                'phone' => '081377001122',
                'table' => 'Takeaway 02',
                'area' => 'Counter',
                'type' => 'takeaway',
                'time' => '14:20',
                'ago' => '15 mnt lalu',
                'elapsed' => '00:15:02',
                'status' => 'ready',
                'status_label' => 'Siap Diantar',
                'items_count' => 2,
                'items' => [
                    ['name' => 'Caramel Macchiato', 'qty' => 2, 'price' => 29000, 'note' => 'Ice, regular cup'],
                ],
                'subtotal' => 58000,
                'pb1' => 0,
                'service' => 0,
                'total' => 58000,
                'note' => 'Ambil sendiri di counter.',
                'paid' => true,
                'paid_via' => 'Tunai',
            ],
            [
                'code' => 'A-27',
                'display_code' => '#A-27',
                'customer' => 'Sinta P.',
                'phone' => '082299110022',
                'table' => 'Meja 12',
                'area' => 'Outdoor',
                'type' => 'dinein',
                'time' => '14:34',
                'ago' => 'Baru saja',
                'elapsed' => '00:00:48',
                'status' => 'new_qr',
                'status_label' => 'Baru Masuk • QR Meja',
                'items_count' => 1,
                'items' => [
                    ['name' => 'Cold Brew Nitro', 'qty' => 1, 'price' => 30000, 'note' => 'Original blend'],
                ],
                'subtotal' => 30000,
                'pb1' => 3000,
                'service' => 1000,
                'total' => 34000,
                'note' => null,
                'paid' => false,
            ],
            [
                'code' => 'A-23',
                'display_code' => '#A-23',
                'customer' => 'Dewi Lestari',
                'phone' => '081255443322',
                'table' => 'Meja 05',
                'area' => 'Indoor AC',
                'type' => 'dinein',
                'time' => '14:05',
                'ago' => '30 mnt lalu',
                'elapsed' => '00:30:10',
                'status' => 'cooking',
                'status_label' => 'Sedang Diracik',
                'items_count' => 2,
                'items' => [
                    ['name' => 'Kopi Tubruk Gayo', 'qty' => 1, 'price' => 22000, 'note' => null],
                    ['name' => 'Pisang Goreng Cokelat', 'qty' => 1, 'price' => 25000, 'note' => null],
                ],
                'subtotal' => 47000,
                'pb1' => 4700,
                'service' => 1500,
                'total' => 53200,
                'note' => null,
                'paid' => true,
                'paid_via' => 'QRIS',
            ],
        ];
    }

    public static function findTicket(string $code): ?array
    {
        $code = ltrim(strtoupper(trim($code)), '#');
        foreach (self::tickets() as $t) {
            if (strtoupper($t['code']) === $code) {
                return $t;
            }
        }

        return null;
    }

    /** Filter tab sesuai brief. */
    public static function filters(): array
    {
        return [
            ['key' => 'all', 'label' => 'Semua', 'count' => 18],
            ['key' => 'waiting_payment', 'label' => 'Menunggu Bayar', 'count' => 5],
            ['key' => 'cooking', 'label' => 'Sedang Diracik', 'count' => 6],
            ['key' => 'ready', 'label' => 'Siap Diantar', 'count' => 4],
            ['key' => 'done', 'label' => 'Selesai Hari Ini', 'count' => null],
            ['key' => 'cancelled', 'label' => 'Dibatalkan', 'count' => null],
        ];
    }

    public static function stats(): array
    {
        return [
            'total' => 84,
            'waiting' => 6,
            'cooking' => 9,
            'done' => 69,
            'revenue' => 4820000,
            'revenue_label' => self::rupiah(4820000),
            'orders_badge' => 5,
        ];
    }

    public static function history(): array
    {
        return [
            ['code' => '#A-21', 'customer' => 'Andi Pratama', 'table' => 'Meja 01', 'time' => '13:58', 'method' => 'QRIS', 'total' => 86000, 'status' => 'Selesai'],
            ['code' => '#A-20', 'customer' => 'Maya Anggraini', 'table' => 'Meja 06', 'time' => '13:45', 'method' => 'Cash', 'total' => 68000, 'status' => 'Selesai'],
            ['code' => '#A-19', 'customer' => 'Rizky Ramadhan', 'table' => 'Takeaway 01', 'time' => '13:30', 'method' => 'Debit', 'total' => 54000, 'status' => 'Selesai'],
            ['code' => '#A-18', 'customer' => 'Putri Ayu', 'table' => 'Meja 10', 'time' => '13:12', 'method' => 'Cash', 'total' => 92000, 'status' => 'Selesai'],
            ['code' => '#A-17', 'customer' => 'Fajar Nugroho', 'table' => 'Meja 04', 'time' => '12:55', 'method' => 'QRIS', 'total' => 47000, 'status' => 'Dibatalkan'],
            ['code' => '#A-16', 'customer' => 'Nadia Salsabila', 'table' => 'Meja 09', 'time' => '12:40', 'method' => 'Cash', 'total' => 75000, 'status' => 'Selesai'],
        ];
    }

    public static function shiftRecap(): array
    {
        return [
            'cash' => 1850000,
            'qris' => 2100000,
            'debit' => 870000,
        ];
    }

    public static function tables(): array
    {
        $occupied = [1, 2, 3, 4, 6, 7, 9, 11, 12, 13, 14, 16, 19];
        $pending = [8];
        $reserved = [15, 20];

        return collect(range(1, 20))->map(fn (int $n) => [
            'no' => str_pad((string) $n, 2, '0', STR_PAD_LEFT),
            'status' => match (true) {
                in_array($n, $occupied, true) => 'occupied',
                in_array($n, $pending, true) => 'pending',
                in_array($n, $reserved, true) => 'reserved',
                default => 'free',
            },
        ])->all();
    }
}

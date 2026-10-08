<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Data POS Kasir PERKOCI EATERY.
 *
 * DB-first: tiket aktif dibaca dari tabel pesanan (hasil checkout
 * self-order customer). Bila belum ada baris aktif, fallback ke
 * data contoh agar UI tetap bisa didemokan.
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

    /** Daftar tiket aktif: database dulu, dummy bila kosong. */
    public static function tickets(): array
    {
        $db = self::dbTickets();

        return ! empty($db) ? $db : self::dummyTickets();
    }

    /** Tiket aktif dari tabel pesanan (status pending/diproses). */
    public static function dbTickets(): array
    {
        try {
            if (! Schema::hasTable('pesanan') || ! Schema::hasTable('detail_pesanan')) {
                return [];
            }

            $rows = DB::table('pesanan')
                ->leftJoin('meja', 'meja.id_meja', '=', 'pesanan.id_meja')
                ->leftJoin('pembayaran', 'pembayaran.id_pesanan', '=', 'pesanan.id_pesanan')
                ->leftJoin('metode_pembayaran', 'metode_pembayaran.id_metode', '=', 'pembayaran.id_metode')
                ->whereIn('pesanan.status_pesanan', ['pending', 'diproses'])
                ->orderBy('pesanan.tanggal_pesan')
                ->select(
                    'pesanan.*', 'meja.no_meja',
                    'pembayaran.status_pembayaran', 'metode_pembayaran.nama_metode'
                )
                ->get();

            if ($rows->isEmpty()) {
                return [];
            }

            $details = DB::table('detail_pesanan')
                ->leftJoin('produk', 'produk.id_produk', '=', 'detail_pesanan.id_produk')
                ->whereIn('detail_pesanan.id_pesanan', $rows->pluck('id_pesanan'))
                ->select('detail_pesanan.*', 'produk.nama_produk')
                ->get()
                ->groupBy('id_pesanan');

            return $rows->map(function ($p) use ($details) {
                $items = [];
                $subtotal = 0;
                foreach ($details[$p->id_pesanan] ?? [] as $d) {
                    $note = implode(', ', array_filter([$d->topping, $d->gula, $d->es, $d->catatan]));
                    $items[] = [
                        'name' => $d->nama_produk ?? "Produk #{$d->id_produk}",
                        'qty' => (int) $d->jumlah,
                        'price' => (float) $d->harga,
                        'note' => $note !== '' ? $note : null,
                    ];
                    $subtotal += (float) $d->subtotal;
                }

                $total = (float) $p->total_harga;
                $service = $subtotal > 0 ? 2000 : 0;
                $pb1 = max(0, $total - $subtotal - $service);
                $paid = ($p->status_pembayaran ?? '') === 'berhasil';
                $status = $p->status_pesanan === 'diproses' || $paid ? 'cooking' : 'waiting_payment';
                $waktu = \Carbon\Carbon::parse($p->tanggal_pesan);

                return [
                    'code' => 'A-'.$p->nomor_antrean,
                    'display_code' => '#A-'.$p->nomor_antrean,
                    'customer' => 'Pelanggan Meja '.($p->no_meja ?? '-'),
                    'phone' => '-',
                    'table' => 'Meja '.($p->no_meja ?? '-'),
                    'area' => AdminData::tableArea((string) ($p->no_meja ?? '')),
                    'type' => 'dinein',
                    'time' => $waktu->format('H:i'),
                    'ago' => $waktu->locale('id')->diffForHumans(),
                    'elapsed' => gmdate('H:i:s', max(0, time() - $waktu->timestamp)),
                    'status' => $status,
                    'status_label' => $status === 'cooking' ? 'Sedang Diracik' : 'Menunggu Pembayaran',
                    'items_count' => array_sum(array_column($items, 'qty')),
                    'items' => $items,
                    'subtotal' => $subtotal,
                    'pb1' => $pb1,
                    'service' => $service,
                    'total' => $total,
                    'note' => null,
                    'paid' => $paid,
                    'paid_via' => $p->nama_metode ?? null,
                    'id_pesanan' => $p->id_pesanan,
                ];
            })->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /** Daftar tiket contoh (fallback bila database kosong). */
    public static function dummyTickets(): array
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

    /** Filter tab: hitung dari tiket aktif bila dari database. */
    public static function filters(): array
    {
        $db = self::dbTickets();
        if (empty($db)) {
            return [
                ['key' => 'all', 'label' => 'Semua', 'count' => 18],
                ['key' => 'waiting_payment', 'label' => 'Menunggu Bayar', 'count' => 5],
                ['key' => 'cooking', 'label' => 'Sedang Diracik', 'count' => 6],
                ['key' => 'ready', 'label' => 'Siap Diantar', 'count' => 4],
                ['key' => 'done', 'label' => 'Selesai Hari Ini', 'count' => null],
                ['key' => 'cancelled', 'label' => 'Dibatalkan', 'count' => null],
            ];
        }

        $byStatus = array_count_values(array_column($db, 'status'));

        return [
            ['key' => 'all', 'label' => 'Semua', 'count' => count($db)],
            ['key' => 'waiting_payment', 'label' => 'Menunggu Bayar', 'count' => $byStatus['waiting_payment'] ?? 0],
            ['key' => 'cooking', 'label' => 'Sedang Diracik', 'count' => $byStatus['cooking'] ?? 0],
            ['key' => 'ready', 'label' => 'Siap Diantar', 'count' => $byStatus['ready'] ?? 0],
            ['key' => 'done', 'label' => 'Selesai Hari Ini', 'count' => null],
            ['key' => 'cancelled', 'label' => 'Dibatalkan', 'count' => null],
        ];
    }

    /** Statistik dasbor: database dulu (hari ini), dummy bila kosong. */
    public static function stats(): array
    {
        try {
            if (Schema::hasTable('pesanan') && Schema::hasTable('transaksi')) {
                $today = date('Y-m-d');
                $waiting = (int) DB::table('pesanan')
                    ->where('status_pesanan', 'pending')->whereDate('tanggal_pesan', $today)->count();
                $cooking = (int) DB::table('pesanan')
                    ->where('status_pesanan', 'diproses')->whereDate('tanggal_pesan', $today)->count();
                $donePesanan = (int) DB::table('pesanan')
                    ->where('status_pesanan', 'selesai')->whereDate('tanggal_pesan', $today)->count();
                $trx = DB::table('transaksi')
                    ->join('pembayaran', 'pembayaran.id_pembayaran', '=', 'transaksi.id_pembayaran')
                    ->join('pesanan', 'pesanan.id_pesanan', '=', 'pembayaran.id_pesanan')
                    ->where('transaksi.status_transaksi', 'berhasil')
                    ->whereDate('transaksi.tanggal_transaksi', $today);
                $doneTrx = (int) (clone $trx)->count();
                $revenue = (float) (clone $trx)->sum('pesanan.total_harga');
                $total = $waiting + $cooking + $donePesanan + $doneTrx;

                if ($total > 0 || $revenue > 0) {
                    return [
                        'total' => $total,
                        'waiting' => $waiting,
                        'cooking' => $cooking,
                        'done' => $donePesanan + $doneTrx,
                        'revenue' => $revenue,
                        'revenue_label' => self::rupiah($revenue),
                        'orders_badge' => $waiting + $cooking,
                    ];
                }
            }
        } catch (\Throwable) {
        }

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

    /** Riwayat transaksi hari ini dari database; dummy bila kosong. */
    public static function history(): array
    {
        try {
            if (Schema::hasTable('transaksi') && Schema::hasTable('pesanan')) {
                $today = date('Y-m-d');
                $rows = DB::table('transaksi')
                    ->join('pembayaran', 'pembayaran.id_pembayaran', '=', 'transaksi.id_pembayaran')
                    ->join('pesanan', 'pesanan.id_pesanan', '=', 'pembayaran.id_pesanan')
                    ->leftJoin('meja', 'meja.id_meja', '=', 'pesanan.id_meja')
                    ->leftJoin('metode_pembayaran', 'metode_pembayaran.id_metode', '=', 'pembayaran.id_metode')
                    ->where('transaksi.status_transaksi', 'berhasil')
                    ->whereDate('transaksi.tanggal_transaksi', $today)
                    ->orderBy('transaksi.tanggal_transaksi', 'desc')
                    ->select(
                        'transaksi.id_transaksi', 'transaksi.tanggal_transaksi',
                        'pesanan.nomor_antrean', 'pesanan.total_harga',
                        'meja.no_meja', 'metode_pembayaran.nama_metode'
                    )
                    ->get()
                    ->map(fn ($r) => [
                        'code' => '#A-'.$r->nomor_antrean,
                        'customer' => 'Pelanggan Meja '.($r->no_meja ?? '-'),
                        'table' => $r->no_meja ? 'Meja '.$r->no_meja : '-',
                        'time' => \Carbon\Carbon::parse($r->tanggal_transaksi)->format('H:i'),
                        'method' => $r->nama_metode ?? '-',
                        'total' => (float) $r->total_harga,
                        'status' => 'Selesai',
                    ])->all();

                $batal = DB::table('pesanan')
                    ->leftJoin('meja', 'meja.id_meja', '=', 'pesanan.id_meja')
                    ->where('pesanan.status_pesanan', 'batal')
                    ->whereDate('pesanan.tanggal_pesan', $today)
                    ->orderBy('pesanan.tanggal_pesan', 'desc')
                    ->select('pesanan.nomor_antrean', 'pesanan.total_harga', 'pesanan.tanggal_pesan', 'meja.no_meja')
                    ->get()
                    ->map(fn ($r) => [
                        'code' => '#A-'.$r->nomor_antrean,
                        'customer' => 'Pelanggan Meja '.($r->no_meja ?? '-'),
                        'table' => $r->no_meja ? 'Meja '.$r->no_meja : '-',
                        'time' => \Carbon\Carbon::parse($r->tanggal_pesan)->format('H:i'),
                        'method' => '-',
                        'total' => (float) $r->total_harga,
                        'status' => 'Dibatalkan',
                    ])->all();

                $merged = array_merge($rows, $batal);
                if (! empty($merged)) {
                    return $merged;
                }
            }
        } catch (\Throwable) {
        }

        return [
            ['code' => '#A-21', 'customer' => 'Andi Pratama', 'table' => 'Meja 01', 'time' => '13:58', 'method' => 'QRIS', 'total' => 86000, 'status' => 'Selesai'],
            ['code' => '#A-20', 'customer' => 'Maya Anggraini', 'table' => 'Meja 06', 'time' => '13:45', 'method' => 'Cash', 'total' => 68000, 'status' => 'Selesai'],
            ['code' => '#A-19', 'customer' => 'Rizky Ramadhan', 'table' => 'Takeaway 01', 'time' => '13:30', 'method' => 'Debit', 'total' => 54000, 'status' => 'Selesai'],
            ['code' => '#A-18', 'customer' => 'Putri Ayu', 'table' => 'Meja 10', 'time' => '13:12', 'method' => 'Cash', 'total' => 92000, 'status' => 'Selesai'],
            ['code' => '#A-17', 'customer' => 'Fajar Nugroho', 'table' => 'Meja 04', 'time' => '12:55', 'method' => 'QRIS', 'total' => 47000, 'status' => 'Dibatalkan'],
            ['code' => '#A-16', 'customer' => 'Nadia Salsabila', 'table' => 'Meja 09', 'time' => '12:40', 'method' => 'Cash', 'total' => 75000, 'status' => 'Selesai'],
        ];
    }

    /** Rekap shift hari ini per metode dari database; dummy bila kosong. */
    public static function shiftRecap(): array
    {
        try {
            if (Schema::hasTable('transaksi') && Schema::hasTable('pesanan')) {
                $byMethod = DB::table('transaksi')
                    ->join('pembayaran', 'pembayaran.id_pembayaran', '=', 'transaksi.id_pembayaran')
                    ->join('pesanan', 'pesanan.id_pesanan', '=', 'pembayaran.id_pesanan')
                    ->leftJoin('metode_pembayaran', 'metode_pembayaran.id_metode', '=', 'pembayaran.id_metode')
                    ->where('transaksi.status_transaksi', 'berhasil')
                    ->whereDate('transaksi.tanggal_transaksi', date('Y-m-d'))
                    ->select('metode_pembayaran.nama_metode', 'pesanan.total_harga')
                    ->get();

                if ($byMethod->isNotEmpty()) {
                    $sum = fn (array $names) => (float) $byMethod
                        ->filter(fn ($r) => in_array($r->nama_metode, $names, true))
                        ->sum('total_harga');

                    return [
                        'cash' => $sum(['Cash']),
                        'qris' => $sum(['QRIS', 'E-Wallet']),
                        'debit' => $sum(['Debit']),
                    ];
                }
            }
        } catch (\Throwable) {
        }

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

<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Support\CustomerData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function checkout(): View
    {
        $cart = CustomerData::cart();
        if (empty($cart)) {
            return view('order.cart', [
                'table' => session('order.table', CustomerData::resolveTable()),
                'cart' => [],
                'totals' => CustomerData::totals([]),
                'cartCount' => 0,
                'activeNav' => 'cart',
                'emptyCheckout' => true,
            ]);
        }

        return view('order.checkout', [
            'table' => session('order.table', CustomerData::resolveTable()),
            'cart' => $cart,
            'totals' => CustomerData::totals($cart),
            'methods' => CustomerData::paymentMethods(),
            'cartCount' => CustomerData::cartCount(),
            'activeNav' => 'cart',
        ]);
    }

    /**
     * Buat pesanan: snapshot di session + persist ke database
     * (pesanan, detail_pesanan, pembayaran, transaksi, pemasukan)
     * agar terbaca kasir & laporan manajer. Gagal tulis DB tidak
     * menggagalkan checkout (fallback session).
     */
    public function store(Request $request): RedirectResponse
    {
        $cart = CustomerData::cart();
        abort_if(empty($cart), 422, 'Keranjang masih kosong.');

        $data = $request->validate([
            'method' => ['required', 'in:qris,ewallet,debit,paylater'],
        ]);

        $totals = CustomerData::totals($cart);
        $table = session('order.table', CustomerData::resolveTable());
        $now = now();

        $order = [
            'code' => 'ORD-'.strtoupper($now->format('md').'-'.substr(uniqid(), -4)),
            'ref' => 'PERKOCI-'.$now->format('YmdHis'),
            'table' => $table,
            'items' => array_values($cart),
            'totals' => $totals,
            'method' => $data['method'],
            'method_label' => collect(CustomerData::paymentMethods())->firstWhere('key', $data['method'])['label'],
            'status' => 'Pesanan Diterima',
            'step' => 1,
            'paid' => $data['method'] !== 'paylater',
            'time' => $now->translatedFormat('d M Y • H:i').' WIB',
            'cashier' => 'Sistem Self-Order',
            'pesanan_id' => self::persist($cart, $totals, $table, $data['method']),
        ];

        session(['order.placed' => $order, 'order.cart' => []]);

        return redirect()->route('order.status')->with('success', "Pesanan {$order['code']} diterima!");
    }

    /**
     * Simpan order ke database. Return id_pesanan atau null bila
     * dilewati (mis. katalog dummy tanpa id produk DB / DB tak siap).
     */
    private static function persist(array $cart, array $totals, array $table, string $method): ?int
    {
        try {
            if (! Schema::hasTable('pesanan') || ! Schema::hasTable('detail_pesanan')
                || ! Schema::hasTable('pembayaran') || ! Schema::hasTable('meja')
                || ! Schema::hasTable('metode_pembayaran')) {
                return null;
            }

            // Hanya baris yang merujuk produk DB nyata yang bisa disimpan (FK).
            $lines = array_values(array_filter($cart, fn ($l) => isset($l['product_id'])
                && str_starts_with((string) $l['product_id'], 'db-')
                && DB::table('produk')->where('id_produk', (int) substr((string) $l['product_id'], 3))->exists()));
            if (empty($lines)) {
                return null;
            }

            $meja = DB::table('meja')->where('no_meja', $table['no'] ?? null)->first()
                ?? DB::table('meja')->first();
            if (! $meja) {
                return null;
            }

            // Pencatat sistem: kasir demo diutamakan, fallback user pertama.
            $sysUser = DB::table('users')->where('username', 'kasir.dimas')->first()
                ?? DB::table('users')->first();
            if (! $sysUser) {
                return null;
            }

            $methodMap = ['qris' => 'QRIS', 'ewallet' => 'E-Wallet', 'debit' => 'Debit', 'paylater' => 'Cash'];
            $methodName = $methodMap[$method] ?? 'Cash';
            $idMetode = DB::table('metode_pembayaran')->where('nama_metode', $methodName)->value('id_metode');
            if (! $idMetode) {
                $idMetode = DB::table('metode_pembayaran')->insertGetId([
                    'nama_metode' => $methodName, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            return DB::transaction(function () use ($lines, $totals, $table, $method, $meja, $sysUser, $idMetode) {
                $antrean = (int) (DB::table('pesanan')->max('nomor_antrean') ?? 0) + 1;

                $idPesanan = DB::table('pesanan')->insertGetId([
                    'id_meja' => $meja->id_meja,
                    'nomor_antrean' => $antrean,
                    'tanggal_pesan' => now(),
                    'total_harga' => $totals['total'],
                    'status_pesanan' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($lines as $line) {
                    $unit = (int) ($line['base_price'] ?? 0);
                    $topping = [];
                    $gula = null;
                    $es = null;
                    foreach ($line['options'] ?? [] as $opt) {
                        $unit += (int) ($opt['delta'] ?? 0);
                        $g = strtolower($opt['group'] ?? '');
                        if (str_contains($g, 'topping') || str_contains($g, 'racikan') || str_contains($g, 'ekstra')) {
                            $topping[] = $opt['value'];
                        } elseif (str_contains($g, 'manis') || str_contains($g, 'sugar') || str_contains($g, 'gula') || str_contains($g, 'aren')) {
                            $gula = $opt['value'];
                        } elseif (str_contains($g, 'es') || str_contains($g, 'ice')) {
                            $es = $opt['value'];
                        }
                    }

                    DB::table('detail_pesanan')->insert([
                        'id_pesanan' => $idPesanan,
                        'id_produk' => (int) substr((string) $line['product_id'], 3),
                        'jumlah' => (int) $line['qty'],
                        'harga' => $unit,
                        'subtotal' => $unit * (int) $line['qty'],
                        'topping' => $topping ? implode(', ', $topping) : null,
                        'gula' => $gula,
                        'es' => $es,
                        'catatan' => $line['note'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $paid = $method !== 'paylater';
                $idBayar = DB::table('pembayaran')->insertGetId([
                    'id_pesanan' => $idPesanan,
                    'id_metode' => $idMetode,
                    'jumlah_bayar' => $paid ? $totals['total'] : 0,
                    'kembalian' => 0,
                    'status_pembayaran' => $paid ? 'berhasil' : 'pending',
                    'tanggal_pembayaran' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Transaksi + pemasukan hanya untuk pembayaran lunas.
                if ($paid && Schema::hasTable('transaksi')) {
                    $idTrx = DB::table('transaksi')->insertGetId([
                        'id_pembayaran' => $idBayar,
                        'id_user' => $sysUser->id,
                        'tanggal_transaksi' => now(),
                        'status_transaksi' => 'berhasil',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if (Schema::hasTable('pemasukan')) {
                        DB::table('pemasukan')->insert([
                            'id_transaksi' => $idTrx,
                            'id_user' => $sysUser->id,
                            'jumlah' => $totals['total'],
                            'keterangan' => 'Self-order meja '.($table['label'] ?? ''),
                            'tanggal' => date('Y-m-d'),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                return $idPesanan;
            });
        } catch (\Throwable $e) {
            Log::warning('Checkout persist gagal, lanjut session-only: '.$e->getMessage());

            return null;
        }
    }

    public function status(): View
    {
        return view('order.status', [
            'table' => session('order.table', CustomerData::resolveTable()),
            'order' => session('order.placed'),
            'cartCount' => CustomerData::cartCount(),
            'activeNav' => 'status',
        ]);
    }

    public function receipt(): View
    {
        $order = session('order.placed');
        abort_if(! $order, 404, 'Belum ada transaksi.');

        return view('order.receipt', [
            'table' => session('order.table', CustomerData::resolveTable()),
            'order' => $order,
            'cartCount' => CustomerData::cartCount(),
            'activeNav' => 'status',
        ]);
    }
}

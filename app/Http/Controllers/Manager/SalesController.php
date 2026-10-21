<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Support\ManagerData;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesController extends Controller
{
    public function index(Request $request): View
    {
        [$dari, $sampai] = ManagerData::period($request->query('dari'), $request->query('sampai'));
        $metode = $request->query('metode') ?: null;
        $rows = ManagerData::salesRows($dari, $sampai, $metode);

        return view('manager.sales.index', [
            'active' => 'sales',
            'manager' => ManagerData::managerMeta($request->user()),
            'rows' => $rows,
            'summary' => ManagerData::summary($dari, $sampai),
            'methods' => ManagerData::methods(),
            'filters' => ['dari' => $dari, 'sampai' => $sampai, 'metode' => $metode],
        ]);
    }

    /** Pusat Export & Cetak Dokumen — UI pilihan laporan + periode. */
    public function center(Request $request): View
    {
        [$dari, $sampai] = ManagerData::period($request->query('dari'), $request->query('sampai'));
        $summary = ManagerData::summary($dari, $sampai);
        $masuk = ManagerData::pemasukan($dari, $sampai);
        $keluar = ManagerData::pengeluaran($dari, $sampai);
        $pb1 = ManagerData::pb1($dari, $sampai);

        return view('manager.export.index', [
            'active' => 'export',
            'manager' => ManagerData::managerMeta($request->user()),
            'filters' => ['dari' => $dari, 'sampai' => $sampai],
            'counts' => [
                'transaksi' => $summary['count'],
                'masuk' => count($masuk['rows']),
                'keluar' => count($keluar['rows']),
                'pajak' => $pb1['pajak'],
            ],
        ]);
    }

    /** Laporan Promo: banner aktif + kinerja item paket/bundling. */
    public function promo(Request $request): View
    {
        [$dari, $sampai] = ManagerData::period($request->query('dari'), $request->query('sampai'));
        $rows = ManagerData::promoRows($dari, $sampai);

        return view('manager.promo.index', [
            'active' => 'promo',
            'manager' => ManagerData::managerMeta($request->user()),
            'rows' => $rows,
            'promo' => \App\Support\CustomerData::promo(),
            'summary' => [
                'qty' => array_sum(array_column($rows, 'qty')),
                'omzet' => array_sum(array_column($rows, 'total')),
            ],
            'filters' => ['dari' => $dari, 'sampai' => $sampai],
        ]);
    }

    public function exportPromo(Request $request): StreamedResponse
    {
        [$dari, $sampai] = ManagerData::period($request->query('dari'), $request->query('sampai'));
        $rows = ManagerData::promoRows($dari, $sampai);

        return response()->streamDownload(function () use ($rows, $dari, $sampai) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ["Laporan Promo {$dari} s.d. {$sampai}"]);
            fputcsv($f, ['Item', 'Kategori', 'Harga', 'Qty Terjual', 'Omzet']);
            foreach ($rows as $r) {
                fputcsv($f, [$r['nama'], $r['kategori'], $r['harga'], $r['qty'], $r['total']]);
            }
            fputcsv($f, ['TOTAL', '', '', array_sum(array_column($rows, 'qty')), array_sum(array_column($rows, 'total'))]);
            fclose($f);
        }, "laporan-promo-{$dari}_{$sampai}.csv", ['Content-Type' => 'text/csv']);
    }

    public function export(Request $request): StreamedResponse
    {
        [$dari, $sampai] = ManagerData::period($request->query('dari'), $request->query('sampai'));
        $metode = $request->query('metode') ?: null;
        $rows = ManagerData::salesRows($dari, $sampai, $metode);

        return response()->streamDownload(function () use ($rows) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['Waktu', 'Kode', 'Antrean', 'Meja', 'Metode', 'Kasir', 'Total']);
            foreach ($rows as $r) {
                fputcsv($f, [$r['waktu'], $r['kode'], $r['antrean'], $r['meja'], $r['metode'], $r['kasir'], $r['total']]);
            }
            fclose($f);
        }, "laporan-penjualan-{$dari}_{$sampai}.csv", ['Content-Type' => 'text/csv']);
    }
}

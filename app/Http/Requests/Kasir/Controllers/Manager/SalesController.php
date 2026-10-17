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

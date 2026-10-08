<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Support\AuditTrail;
use App\Support\ManagerData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        [$dari, $sampai] = ManagerData::period($request->query('dari'), $request->query('sampai'));
        $masuk = ManagerData::pemasukan($dari, $sampai);
        $keluar = ManagerData::pengeluaran($dari, $sampai);

        return view('manager.finance.index', [
            'active' => 'finance',
            'manager' => ManagerData::managerMeta($request->user()),
            'masuk' => $masuk,
            'keluar' => $keluar,
            'laba' => $masuk['total'] - $keluar['total'],
            'filters' => ['dari' => $dari, 'sampai' => $sampai],
        ]);
    }

    public function storeKeluar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'jumlah' => ['required', 'numeric', 'min:1000'],
            'keterangan' => ['required', 'string', 'max:255'],
        ]);

        DB::table('pengeluaran')->insert([
            'id_user' => $request->user()->id,
            'jumlah' => $data['jumlah'],
            'keterangan' => $data['keterangan'],
            'tanggal' => $data['tanggal'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        AuditTrail::log('KEUANGAN', 'Catat pengeluaran', $data['keterangan'].' • '.ManagerData::rupiah($data['jumlah']));

        return back()->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function destroyKeluar(Request $request, int $id): RedirectResponse
    {
        $row = DB::table('pengeluaran')->where('id_pengeluaran', $id)->first();
        abort_if(! $row, 404);
        DB::table('pengeluaran')->where('id_pengeluaran', $id)->delete();

        AuditTrail::log('KEUANGAN', 'Hapus pengeluaran', ($row->keterangan ?? '').' • '.ManagerData::rupiah($row->jumlah));

        return back()->with('success', 'Data pengeluaran dihapus.');
    }

    public function export(Request $request): StreamedResponse
    {
        [$dari, $sampai] = ManagerData::period($request->query('dari'), $request->query('sampai'));
        $masuk = ManagerData::pemasukan($dari, $sampai);
        $keluar = ManagerData::pengeluaran($dari, $sampai);

        return response()->streamDownload(function () use ($masuk, $keluar, $dari, $sampai) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ["Laporan Keuangan {$dari} s.d. {$sampai}"]);
            fputcsv($f, ['Jenis', 'Tanggal', 'Keterangan', 'Pencatat', 'Jumlah']);
            foreach ($masuk['rows'] as $r) {
                fputcsv($f, ['MASUK', $r['tanggal'], $r['keterangan'], $r['user'], $r['jumlah']]);
            }
            foreach ($keluar['rows'] as $r) {
                fputcsv($f, ['KELUAR', $r['tanggal'], $r['keterangan'], $r['user'], $r['jumlah']]);
            }
            fputcsv($f, ['TOTAL MASUK', '', '', '', $masuk['total']]);
            fputcsv($f, ['TOTAL KELUAR', '', '', '', $keluar['total']]);
            fputcsv($f, ['LABA BERSIH', '', '', '', $masuk['total'] - $keluar['total']]);
            fclose($f);
        }, "laporan-keuangan-{$dari}_{$sampai}.csv", ['Content-Type' => 'text/csv']);
    }
}

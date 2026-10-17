<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminData;
use App\Support\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'semua');
        $rows = AdminData::stockRows();
        if (in_array($status, ['AMAN', 'MENIPIS', 'HABIS'], true)) {
            $rows = array_values(array_filter($rows, fn ($r) => $r['status'] === $status));
        }

        return view('admin.stocks.index', [
            'active' => 'stocks',
            'admin' => AdminData::adminMeta($request->user()),
            'rows' => $rows,
            'counts' => AdminData::stockCounts(),
            'filter' => $status,
        ]);
    }

    /** Tambah jumlah stok (restock). */
    public function restock(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_stok' => ['required', 'integer', 'exists:stok,id_stok'],
            'qty' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        $row = DB::table('stok')->where('id_stok', $data['id_stok'])->first();
        DB::table('stok')->where('id_stok', $data['id_stok'])->update([
            'jumlah_stok' => $row->jumlah_stok + $data['qty'],
            'updated_at' => now(),
        ]);

        $produk = DB::table('produk')->where('id_produk', $row->id_produk)->first();
        AuditTrail::log('INVENTORI_STOK', 'Restock '.($produk->nama_produk ?? "stok #{$row->id_stok}"), "+{$data['qty']} → ".($row->jumlah_stok + $data['qty']));

        return back()->with('success', "Stok berhasil ditambah {$data['qty']}.");
    }
}

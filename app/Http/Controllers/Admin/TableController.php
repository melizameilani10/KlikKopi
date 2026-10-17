<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meja;
use App\Support\AdminData;
use App\Support\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TableController extends Controller
{
    public function index(Request $request): View
    {
        $tables = Meja::orderBy('no_meja')->get()->map(fn ($m) => [
            'id' => $m->id_meja,
            'no' => $m->no_meja,
            'label' => 'Meja '.$m->no_meja,
            'area' => AdminData::tableArea($m->no_meja),
            'status' => $m->status,
            'qr' => $m->qr_code,
            'scan_url' => route('order.welcome', ['qr' => $m->qr_code]),
        ])->all();

        return view('admin.tables.index', [
            'active' => 'tables',
            'admin' => AdminData::adminMeta($request->user()),
            'tables' => $tables,
            'occupancy' => AdminData::occupancy(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'no_meja' => ['required', 'string', 'max:50', 'unique:meja,no_meja'],
            'status' => ['required', 'in:tersedia,terisi'],
        ]);

        $meja = Meja::create([
            'no_meja' => $data['no_meja'],
            'qr_code' => 'MEJA-'.strtoupper($data['no_meja']),
            'status' => $data['status'],
        ]);

        AuditTrail::log('QR_MEJA', "Tambah Meja {$meja->no_meja}", 'QR: '.$meja->qr_code);

        return back()->with('success', "Meja {$meja->no_meja} berhasil ditambahkan.");
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $meja = Meja::findOrFail($id);
        $data = $request->validate(['status' => ['required', 'in:tersedia,terisi']]);
        $meja->update($data);

        AuditTrail::log('QR_MEJA', "Ubah status Meja {$meja->no_meja} → ".strtoupper($data['status']));

        return back()->with('success', "Status Meja {$meja->no_meja} diperbarui.");
    }

    public function regenerate(Request $request, int $id): RedirectResponse
    {
        $meja = Meja::findOrFail($id);
        $meja->update(['qr_code' => 'MEJA-'.strtoupper($meja->no_meja).'-'.strtoupper(Str::random(4))]);

        AuditTrail::log('QR_MEJA', "Regenerate QR Meja {$meja->no_meja}", 'QR baru: '.$meja->qr_code);

        return back()->with('success', "QR Meja {$meja->no_meja} diperbarui. QR lama tidak berlaku lagi.");
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $meja = Meja::findOrFail($id);
        $no = $meja->no_meja;
        $meja->delete();

        AuditTrail::log('QR_MEJA', "Hapus Meja {$no}");

        return back()->with('success', "Meja {$no} dihapus.");
    }
}

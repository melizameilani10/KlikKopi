<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminData;
use App\Support\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'tanggal' => (string) $request->query('tanggal', ''),
            'user' => trim((string) $request->query('user', '')),
            'modul' => (string) $request->query('modul', ''),
            'status' => (string) $request->query('status', ''),
        ];

        return view('admin.audit.index', [
            'active' => 'audit',
            'admin' => AdminData::adminMeta($request->user()),
            'rows' => AuditTrail::read(array_filter($filters)),
            'filters' => $filters,
            'modules' => AuditTrail::modules(),
            'statuses' => AuditTrail::statuses(),
        ]);
    }
}

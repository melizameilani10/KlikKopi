<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $stock = AdminData::stockCounts();

        return view('admin.dashboard', [
            'active' => 'dashboard',
            'admin' => AdminData::adminMeta($request->user()),
            'metrics' => [
                'menuAktif' => AdminData::menuAktifCount(),
                'menuTotal' => AdminData::menuCount(),
                'menipis' => $stock['menipis'],
                'habis' => $stock['habis'],
                'pesanan' => AdminData::pesananHariIni(),
            ],
            'alerts' => AdminData::lowStockAlerts(),
            'occupancy' => AdminData::occupancy(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Support\ManagerData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        [$dari, $sampai] = ManagerData::period(null, date('Y-m-d'));
        $dari = substr($sampai, 0, 7).'-01';
        $summary = ManagerData::summary($dari, $sampai);
        $keuangan = ManagerData::pengeluaran($dari, $sampai);

        return view('manager.dashboard', [
            'active' => 'dashboard',
            'manager' => ManagerData::managerMeta($request->user()),
            'periode' => "{$dari} s.d. {$sampai}",
            'summary' => $summary,
            'laba' => $summary['omzet'] - $keuangan['total'],
            'keluar' => $keuangan['total'],
            'series' => ManagerData::dailySeries(7),
            'top' => ManagerData::topMenus($dari, $sampai),
        ]);
    }
}

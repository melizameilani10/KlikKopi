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
        $mode = (string) $request->query('periode', 'bulanan');
        if (! in_array($mode, ['harian', 'mingguan', 'bulanan'], true)) {
            $mode = 'bulanan';
        }

        $sampai = date('Y-m-d');
        $dari = match ($mode) {
            'harian' => $sampai,
            'mingguan' => date('Y-m-d', strtotime('-6 days')),
            default => substr($sampai, 0, 7).'-01',
        };

        $summary = ManagerData::summary($dari, $sampai);
        $keuangan = ManagerData::pengeluaran($dari, $sampai);

        return view('manager.dashboard', [
            'active' => 'dashboard',
            'manager' => ManagerData::managerMeta($request->user()),
            'periode' => "{$dari} s.d. {$sampai}",
            'mode' => $mode,
            'summary' => $summary,
            'laba' => $summary['omzet'] - $keuangan['total'],
            'keluar' => $keuangan['total'],
            'series' => ManagerData::dailySeries(7),
            'top' => ManagerData::topMenus($dari, $sampai),
        ]);
    }
}

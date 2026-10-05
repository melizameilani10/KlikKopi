<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Support\PosData;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $now = now()->locale('id');
        $recap = PosData::shiftRecap();
        $omzet = array_sum($recap);

        return view('kasir.history.index', [
            'active' => 'history',
            'sidebarBadge' => PosData::stats()['orders_badge'],
            'kasir' => [
                'name' => $user->name,
                'first_name' => Str::of($user->name)->before(' ')->toString() ?: $user->name,
                'role' => 'Kasir Utama',
                'code' => 'Kasir 01',
                'shift' => session('pos.shift_label', 'Pagi'),
                'session' => '07:00 - 15:00 WIB',
                'drawer' => '500.000',
                'now_label' => $now->translatedFormat('l, j M Y').' • '.$now->format('H:i').' WIB',
            ],
            'printer' => ['name' => 'Epson TM-T82', 'status' => 'Online'],
            'rows' => PosData::history(),
            'recap' => $recap,
            'omzet' => $omzet,
            'stats' => PosData::stats(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Support\PosData;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $now = now()->locale('id');
        $stats = PosData::stats();
        $tickets = array_slice(PosData::tickets(), 0, 4);

        return view('kasir.dashboard', [
            'active' => 'dashboard',
            'sidebarBadge' => $stats['orders_badge'],
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
            'stats' => $stats,
            'tickets' => $tickets,
            'tables' => PosData::tables(),
            'occupancy' => [
                'total' => 20, 'occupied' => 14, 'free' => 4, 'reserved' => 2,
                'outdoor' => '6/8', 'indoor' => '8/12',
            ],
            'quickActions' => [
                ['title' => 'Pesanan Manual / Takeaway', 'description' => 'Walk-in customer atau kasir counter', 'icon' => 'cart-plus', 'shortcut' => 'F1', 'active' => true, 'action' => 'manual-order'],
                ['title' => 'Buka Laci Uang (Open Drawer)', 'description' => 'Kick laci kas fisik untuk kembalian', 'icon' => 'register', 'shortcut' => 'F2', 'active' => false, 'action' => 'open-drawer'],
                ['title' => 'Split Bill & Cetak Ulang Struk', 'description' => 'Manajemen struk dan invoice meja', 'icon' => 'list', 'shortcut' => 'F3', 'active' => false, 'action' => 'split-bill'],
            ],
            'featured' => ['name' => 'V60 Gayo Natural Anaerob', 'stock' => 'Sisa stok biji kopi: 18 porsi'],
        ]);
    }
}

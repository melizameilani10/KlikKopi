<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Support\PosData;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tickets = PosData::tickets();
        $activeCode = strtoupper(ltrim((string) $request->query('ticket', 'A-24'), '#'));
        $active = PosData::findTicket($activeCode) ?? $tickets[0];

        return view('kasir.orders.index', [
            'active' => 'orders',
            'sidebarBadge' => PosData::stats()['orders_badge'],
            'kasir' => $this->kasirMeta($user),
            'printer' => ['name' => 'Epson TM-T82', 'status' => 'Online'],
            'filters' => PosData::filters(),
            'activeFilter' => (string) $request->query('filter', 'all'),
            'search' => (string) $request->query('q', ''),
            'tickets' => $tickets,
            'activeTicket' => $active,
        ]);
    }

    private function kasirMeta($user): array
    {
        $now = now()->locale('id');

        return [
            'name' => $user->name,
            'first_name' => Str::of($user->name)->before(' ')->toString() ?: $user->name,
            'role' => 'Kasir Utama',
            'code' => 'Kasir 01',
            'shift' => session('pos.shift_label', 'Pagi'),
            'session' => '07:00 - 15:00 WIB',
            'drawer' => '500.000',
            'now_label' => $now->translatedFormat('l, j M Y').' • '.$now->format('H:i').' WIB',
        ];
    }
}

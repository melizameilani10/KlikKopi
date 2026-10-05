<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Support\PosData;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function show(Request $request, string $code): View
    {
        $ticket = PosData::findTicket($code) ?? PosData::tickets()[0];
        $user = $request->user();
        $now = now()->locale('id');

        return view('kasir.payment.show', [
            'active' => 'orders',
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
            'ticket' => $ticket,
            'methods' => [
                ['key' => 'cash', 'label' => 'Cash', 'hint' => 'Tunai loket', 'icon' => 'banknote'],
                ['key' => 'qris', 'label' => 'QRIS', 'hint' => 'Scan dinamis', 'icon' => 'qr'],
                ['key' => 'debit', 'label' => 'Debit / EDC', 'hint' => 'Gesek / tap', 'icon' => 'card'],
                ['key' => 'split', 'label' => 'Split Bill', 'hint' => 'Bagi tagihan', 'icon' => 'split'],
            ],
        ]);
    }
}
